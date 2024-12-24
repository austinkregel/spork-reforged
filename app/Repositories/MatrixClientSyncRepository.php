<?php

namespace App\Repositories;

use App\Models\Credential;
use App\Models\Message;
use App\Models\Person;
use App\Models\Thread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class MatrixClientSyncRepository
{
    protected $devices = [];

    protected $keys = [];

    protected $default_key = null;
    protected $master_key = null;
    protected $self_sign_key = null;
    protected $signing_user = null;
    protected $megolm_backup = null;

    protected $client = null;
    protected $breadcrumbs = null;
    protected $dms = null;
    protected $notification_settings = null;

    public function __destruct()
    {
//        file_put_contents(storage_path('app/matrix-sync.json'), json_encode([
//            'devices' => $this->devices,
//            'keys' => $this->keys,
//            'default_key' => $this->default_key,
//            'master_key' => $this->master_key,
//            'self_sign_key' => $this->self_sign_key,
//            'signing_user' => $this->signing_user,
//            'megolm_backup' => $this->megolm_backup,
//            'client' => $this->client,
//            'notification_settings' => $this->notification_settings,
//        ], JSON_PRETTY_PRINT));
    }

    public function __construct()
    {
//        if (file_exists(storage_path('app/matrix-sync.json'))) {
//            $contents = json_decode(file_get_contents(storage_path('app/matrix-sync.json')), true);
//
//            $this->devices = $contents['devices'];
//            $this->keys = $contents['keys'];
//            $this->default_key = $contents['default_key'];
//            $this->master_key = $contents['master_key'];
//            $this->self_sign_key = $contents['self_sign_key'];
//            $this->signing_user = $contents['signing_user'];
//            $this->megolm_backup = $contents['megolm_backup'];
//            $this->client = $contents['client'];
//            $this->notification_settings = $contents['notification_settings'];
//        }
    }

    public function process(array $sync, Credential $credential, User $user): void
    {
        $events = $sync['account_data']['events'];
        foreach ($events as $event) {
            $this->processEvent($event);
        }

        $rooms = json_decode(json_encode($sync['rooms']['join']), true);

        foreach ($rooms as $id => $room) {
            $this->processRoom($id, $room, $credential, $user);
        }

    }
    public function processRoom($roomId, array $room, $credential, $user): void
    {
        $events = array_merge(
            $room['state']['events'],
            $room['timeline']['events']
        );
        foreach ($events as $event) {
            switch($event['type']) {
                case 'm.room.create':
                    $this->processCreateEvent($roomId, $event);
                    break;
                case 'm.room.member':
                    $this->processMemberEvent($roomId, $event);
                    break;
                case 'm.room.topic':
                    $thread = Thread::firstWhere('thread_id', $roomId);
                    if (empty($thread)) {
                        $this->ignored($event);
                        break;
                    }
                    $thread->update(['description' => $event['content']['topic']]);
                    break;
                case 'm.room.encryption':
                    $thread = Thread::firstWhere('thread_id', $roomId);
                    if (empty($thread)) {
                        $this->ignored($event);
                        break;
                    }

                    $thread->update([
                        'settings' => array_merge(
                            $thread->settings ?? [],
                            [
                                'encrypted' => true,
                                'algorithm' => $event['content']['algorithm'],
                                'rotation_period_ms' => $event['content']['rotation_period_ms'] ?? null,
                            ],
                        )
                    ]);
                    break;
                case 'm.room.encrypted':
                    $this->ignored($event);
                    break;
                case 'm.reaction': // @todo
                case 'm.sticker':
                    case 'm.room.avatar':
                    $this->ignored($event);
                    break;
                case 'm.room.redaction': // @todo
                    $message = Message::firstWhere('event_id', $event['redacts']);
                    if (isset($message)) {
                        $message->update([
                            'message' => 'Message redacted',
                            'html_message' => '<i>Message redacted</i>',
                            'thumbnail_url' => null,
                        ]);
                    }
                    break;
                case 'm.room.message':
                    /** @var Thread $thread */
                    $thread = Thread::firstWhere('thread_id', $roomId);

                    $message = $thread->messages()->firstWhere('event_id', $event['event_id']);

                    if (empty($message)) {
                        $sender = $this->findOrCreatePerson($event['sender']);

                        if (!isset($event['content']['body'])) {
                            if ($event['unsigned']['redacted_because']) {
                                $message = Message::firstWhere('event_id', $event['unsigned']['redacted_because']['redacts']);

                                if (empty($message)) {
                                    $this->ignored($event);
                                    break;
                                }


                                $message->update([
                                    'message' => 'Message redacted',
                                    'html_message' => '<i>Message redacted</i>',
                                    'thumbnail_url' => null,
                                ]);
                                break;
                            }
                        }

                        $thread->messages()->forceCreate(array_merge(
                            isset($event['content']['info']) && isset($event['content']['info']['thumbnail_url']) ? [
                                'thumbnail_url' => $this->downloadMedia($credential, $event['content']['info']['thumbnail_url'])
                            ] : [],
                            isset($event['content']['settings']) ? [
                                'settings' => $event['content']['settings']
                            ] : [],
                            [
                                'from_person' => $sender->id,
                                'to_person' => $user->id,
                                'thread_id' => $thread->id,
                                'type' => $event['type'],
                                'originated_at' => Carbon::createFromFormat('U', round($event['origin_server_ts']/ 1000)),
                                'message' => $event['content']['body'],
                                'event_id' => $event['event_id'],
                                'html_message' => isset($event['content']['format_body']) ? $event['content']['format_body'] : null,
                                'credential_id' => $credential->id,
                                'is_decrypted' => true,
                            ]
                        ));
                        if (Carbon::createFromFormat('U', round($event['origin_server_ts']/ 1000))->isAfter($thread->origin_server_ts)) {
                            $thread->update(['origin_server_ts' => Carbon::createFromFormat('U', round($event['origin_server_ts']/ 1000))]);
                        }
                    }

                    break;
                case 'm.room.power_levels':
                case 'io.element.functional_members':
                case 'm.room.join_rules':
                case 'm.room.history_visibility':
                case 'm.room.guest_access':
                case 'm.bridge':
                case 'uk.half-shot.bridge':
                case 'm.space.child':
                case 'm.space.parent':
                case 'm.room.canonical_alias':
                case 'com.beeper.chatwoot.conversation_id':
                case 'com.beeper.backfill_status':
                case 'com.beeper.rooms.note_to_self':
                case 'com.beeper.support_chat':
                case 'fi.mau.dummy.portal_created':
                case 'com.beeper.message_send_status';
                case 'com.beeper.feed':
                case 'org.matrix.msc2716.marker':
                case 'org.matrix.msc3401.call.member':
                case 'uk.half-shot.matrix-hookshot.feed':
                case 'm.room.plumbing':
                case 'm.room.related_groups':
                    $this->ignored($event);
                    break;
                case 'm.room.name':
                    $thread = Thread::firstWhere('thread_id', $roomId);
                    if (empty($thread)) {
                        Thread::create(
                            [
                                'thread_id' => $roomId,
                                'name' => $event['content']['name'],
                                'origin_server_ts' => Carbon::now()
                            ]
                        );
                        break;
                    }
                    $thread->update(['name' => $event['content']['name']]);
                    break;
                default:
                    dd($event, 'Unknown event type');
            }
        }
    }

    public function processEvent(array $event): void
    {
        if (!isset($event['type'])) {
            return;
        }
        if (str_starts_with($event['type'], 'io.element.matrix_client_information.')) {
            $this->processMatrixClientEvent($event);
            return;
        }

        if (str_starts_with($event['type'], 'org.matrix.msc3890.local_notification_settings.')) {
            $this->handleLocalNotificationSettings($event);
            return;
        }

        if (str_starts_with($event['type'], 'm.secret_storage.key.')) {
            $this->processSecretStorageKey($event);
            return;
        }


        // Direct match names
        switch ($event['type']) {
            case 'io.element.recent_emoji':
                $this->processRecentEmojiEvent($event);
                break;
            case 'm.secret_storage.default_key':
                $this->processSecretStorageDefaultKey($event);
                break;
            case 'm.cross_signing.master':
                $this->processCrossSigningMaster($event);
                break;

            case 'm.cross_signing.self_signing':
                $this->processCrossSigningSelfSigning($event);
                break;
            case 'm.cross_signing.user_signing':
                $this->processCrossSigningUserSigning($event);
                break;
            case 'm.megolm_backup.v1':
                $this->processMegolmBackup($event);
                break;
            case 'im.vector.analytics':
            case 'm.push_rules':
            case 'm.accepted_terms':
                $this->ignored($event);
                break;
            case 'im.vector.web.settings':
                $this->processWebSettings($event);
                break;
            case 'im.vector.setting.breadcrumbs':
                $this->processBreadcrumbs($event);
                break;
            case 'm.direct':
                $this->processDirect($event);
                break;
            default:
                dd($event, 'Unknown event type');
        }
    }

    protected function processMatrixClientEvent(array $event)
    {
        $deviceId = $this->extractDeviceIdFromEvent($event);

        $context = $event['content'];

        if (!isset($this->devices[$deviceId])) {
            $this->devices[$deviceId] = [];
        }

        $this->devices[$deviceId] = array_merge($this->devices[$deviceId], $context);
    }

    protected function handleLocalNotificationSettings(array $event): void
    {
        $deviceId = $this->extractDeviceIdFromEvent($event);

        $context = $event['content'];

        if (!isset($this->devices[$deviceId])) {
            $this->devices[$deviceId] = [];
        }

        $this->devices[$deviceId] = array_merge($this->devices[$deviceId], $context);
    }

    protected function processSecretStorageKey(array $event)
    {
        $key = $this->extractDeviceIdFromEvent($event);

        if (!isset($this->keys[$key])) {
            $this->keys[$key] = [];
        }

        $this->keys[$key] = array_merge($this->keys[$key], $event['content']);
    }

    protected function processRecentEmojiEvent(array $event)
    {
        $deviceId = $this->extractDeviceIdFromEvent($event);

        if (!isset($this->devices[$deviceId])) {
            $this->devices[$deviceId] = [];
        }
        $context = [
            'recent_emoji' => array_map(fn ($emoji) => $emoji['0']  ,$event['content']['recent_emoji']),
        ];

        $this->devices[$deviceId] = array_merge($this->devices[$deviceId], $context);
    }

    protected function processCrossSigningMaster(array $event)
    {
        if (isset($this->master_key)) {
            return;
        }
        $this->master_key = $event['content']['encrypted'];
    }

    protected function processSecretStorageDefaultKey(array $event)
    {
        if (isset($this->default_key)) {
            return;
        }

        $this->default_key = $event['content']['key'];
    }

    protected function processCrossSigningSelfSigning(array $event)
    {
        if (!isset($this->self_sign_key)) {
            $this->self_sign_key = [];
        }

        $this->self_sign_key = array_merge(
            ($this->self_sign_key ?? []),
            $event['content']['encrypted']
        );
    }

    protected function processCrossSigningUserSigning(array $event)
    {
        if (!isset($this->signing_user)) {
            $this->signing_user = [];
        }
        $this->signing_user = array_merge(
            ($this->signing_user ?? []),
            $event['content']['encrypted']
        );
    }

    protected function processMegolmBackup(array $event)
    {
        if (!isset($this->megolm_backup)) {
            $this->megolm_backup = [];
        }
        $this->megolm_backup = array_merge(
            ($this->megolm_backup ?? []),
            $event['content']['encrypted']
        );
    }

    protected function ignored($event)
    {
        // ignoring
    }

    protected function processWebSettings(array $event)
    {
        $this->client = $event['content'];
    }

    protected function processBreadcrumbs(array $event)
    {
        $this->breadcrumbs = $event['content'];
    }

    protected function processDirect(array $event)
    {
        $this->dms = $event['content'];
    }
    protected function extractDeviceIdFromEvent(array $event)
    {
        $parts = explode('.', $event['type']);

        return end($parts);
    }

    protected function processMessageEvent(array $messages, string $roomId)
    {
        foreach ($messages as $message) {
            if ($message['type'] !== 'm.room.message') {
                continue;
            }

            $thread = Thread::firstOrCreate(
                ['thread_id' => $roomId],
                ['name' => $roomId, 'origin_server_ts' => Carbon::now()]
            );

            $sender = $this->findOrCreatePerson($message['sender']);
            $dmTarget = $this->findOrCreatePerson($this->getMatrixUserName());

        }
    }

    protected function downloadMedia(Credential $credential, string $mxcUrl)
    {
        $mxcUrl = str_replace('mxc://', '', $mxcUrl);

        [$domain, $key] = explode('/', $mxcUrl);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $credential->access_token,
            ])->get($credential->settings['matrix_server'] . sprintf('/_matrix/client/v1/media/thumbnail/%s?timeout_ms=500&width=64&height=64', $mxcUrl))->body();

            file_put_contents($path = storage_path('app/public/' . $key), $response);

            return '/storage/'.$key;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function findOrCreatePerson($identifier)
    {
        $person = Person::whereJsonContains('identifiers', $identifier)->first();

        if (!$person) {
            $person = Person::create([
                'name' => $identifier,
                'identifiers' => json_encode([$identifier]),
                'user_id' => $personSystemUser->id ?? 1,
            ]);
        }

        return $person;
    }

    protected function getMatrixUserName()
    {
        return '@' . env('MATRIX_USERNAME') . ':' . parse_url(env('MATRIX_HOST'), PHP_URL_HOST);
    }

    protected function processCreateEvent(string $roomId, mixed $event): void
    {
        Thread::firstOrCreate(
            ['thread_id' => $roomId],
            [
                'name' => $roomId,
                'origin_server_ts' => Carbon::createFromFormat('U', round($event['origin_server_ts']/1000) )
            ]
        );
    }

    protected function processMemberEvent($id, mixed $event)
    {
        $people = Person::whereJsonContains('identifiers', $event['sender'])->get();

        if ($people->isEmpty()) {
             Person::create([
                'name' => $event['content']['displayname'] ?? $event['sender'],
                'identifiers' => [$event['sender']],
                'user_id' => 1, // @todo
            ]);
        }

        $people = Person::whereJsonContains('identifiers', $event['sender'])->get();
        $credential = Credential::firstWhere('type', 'matrix');
        foreach ($people as $person) {
            /** @var Thread $thread */
            $thread = Thread::firstOrCreate(
                ['thread_id' => $id],
                ['name' => $id, 'origin_server_ts' => Carbon::createFromFormat('U', round($event['origin_server_ts']/ 1000)) ]
            );

            if ($thread->participants()->firstWhere('person_id', $person->id)) {
                continue;
            }

            $thread->participants()->attach($person->id, ['joined_at' => Carbon::createFromFormat('U', round($event['origin_server_ts']/ 1000))]);

            if (Carbon::createFromFormat('U', round($event['origin_server_ts']/ 1000))->isAfter($thread->origin_server_ts)) {
                $thread->update(['origin_server_ts' => Carbon::createFromFormat('U', round($event['origin_server_ts']/ 1000))]);
            }

            if (empty($person->photo_url) && !empty($event['content']['avatar_url'])) {
                try {
                    $person->photo_url = $this->downloadMedia($credential, $event['content']['avatar_url']);
                    $person->save();
                } catch (\Throwable $exception) {
                    info('Unable to download media', [
                        'exception' => $exception->getMessage(),
                        'stack' => $exception->getTraceAsString(),
                    ]);
                }
            }
        }
    }
}
