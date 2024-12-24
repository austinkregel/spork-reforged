<?php

namespace Tests\Unit\Repositories;

use App\Repositories\MatrixClientSyncRepository;
use Tests\TestCase;

class MatrixClientSyncRepositoryTest extends TestCase
{
    protected MatrixClientSyncRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MatrixClientSyncRepository();
    }

    public function testProcessEventMatrixClientEvent()
    {
        $event = new \stdClass();
        $event->type = 'io.element.matrix_client_information.DEVICE_ID';
        $event->content = (object)['test' => 'test'];

        $this->repository->processEvent($event);

        $this->assertSame([
            'DEVICE_ID' => [
                'test' => 'test'
            ]
        ], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));

    }

    public function testProcessEventLocalNotificationSettings()
    {
        $event = new \stdClass();
        $event->type = 'org.matrix.msc3890.local_notification_settings.DEVICE_ID';
        $event->content = (object)['test' => 'test'];
        $this->repository->processEvent($event);
        $this->assertSame([
            'DEVICE_ID' => [
                'test' => 'test'
            ]
        ], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));

    }

    public function testProcessEventSecretStorageKey()
    {
        $event = new \stdClass();
        $event->type = 'm.secret_storage.key.DEVICE_KEY';
        $event->content = (object)['test' => 'test', 'object' => [ 'nested' => 'nested' ]];
        $this->repository->processEvent($event);
        $this->assertSame([
            'DEVICE_KEY' => [
                'test' => 'test',
                'object' => [
                    'nested' => 'nested'
                ]
            ]
        ], $this->getProperty($this->repository, 'keys'));
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));

    }

    public function testProcessEventRecentEmoji()
    {
        $event = new \stdClass();
        $event->type = 'io.element.recent_emoji';
        $event->content = (object)['recent_emoji' => [['emoji1'], ['emoji2']]];
        $this->repository->processEvent($event);
        $this->assertSame([
            'recent_emoji' => [
                'recent_emoji' => [
                    'emoji1',
                    'emoji2',
                ]
            ]
        ], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));

    }

    public function testProcessEventSecretStorageDefaultKey()
    {
        $event = new \stdClass();
        $event->type = 'm.secret_storage.default_key';
        $event->content = (object) [
            'key' => 'A7I',
        ];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame('A7I', $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventCrossSigningMaster()
    {
        $event = new \stdClass();
        $event->type = 'm.cross_signing.master';

        $event->content = (object)[
            'encrypted' => (object) [
                'A7I' => (object) [
                    'iv' => base64_encode('asdf'),
                    'ciphertext' => base64_encode('asdf'),
                    'mac' => base64_encode('asdf'),
                ]
            ]
        ];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame([
            'A7I' => [
                'iv' => base64_encode('asdf'),
                'ciphertext' => base64_encode('asdf'),
                'mac' => base64_encode('asdf'),
            ]
        ], json_decode(json_encode($this->getProperty($this->repository, 'master_key')), true));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventCrossSigningSelfSigning()
    {
        $event = new \stdClass();
        $event->type = 'm.cross_signing.self_signing';

        $event->content = (object)[
            'encrypted' => (object) [
                'A7I' => (object) [
                    'iv' => base64_encode('asdf'),
                    'ciphertext' => base64_encode('asdf'),
                    'mac' => base64_encode('asdf'),
                ]
            ]
        ];        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame([
            'A7I' => [
                'iv' => base64_encode('asdf'),
                'ciphertext' => base64_encode('asdf'),
                'mac' => base64_encode('asdf'),
            ]
        ], $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventCrossSigningUserSigning()
    {
        $event = new \stdClass();
        $event->type = 'm.cross_signing.user_signing';

        $event->content = (object)[
            'encrypted' => (object) [
                'A7I' => (object) [
                    'iv' => base64_encode('asdf'),
                    'ciphertext' => base64_encode('asdf'),
                    'mac' => base64_encode('asdf'),
                ]
            ]
        ];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame([
            'A7I' => [
                'iv' => base64_encode('asdf'),
                'ciphertext' => base64_encode('asdf'),
                'mac' => base64_encode('asdf'),
            ]
        ], $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventMegolmBackup()
    {
        $event = new \stdClass();
        $event->type = 'm.megolm_backup.v1';

        $event->content = (object)[
            'encrypted' => (object) [
                'A7I' => (object) [
                    'iv' => base64_encode('asdf'),
                    'ciphertext' => base64_encode('asdf'),
                    'mac' => base64_encode('asdf'),
                ]
            ]
        ];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame([
            'A7I' => [
                'iv' => base64_encode('asdf'),
                'ciphertext' => base64_encode('asdf'),
                'mac' => base64_encode('asdf'),
            ]
        ], $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventAcceptedTerms()
    {
        $event = new \stdClass();
        $event->type = 'm.accepted_terms';

        $event->content = (object)['test' => 'test'];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventWebSettings()
    {
        $event = new \stdClass();
        $event->type = 'im.vector.web.settings';

        $event->content = (object)['test' => 'test'];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(['test' => 'test'], (array) $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventBreadcrumbs()
    {
        $this->testProcessEventWebSettings();
        $event = new \stdClass();
        $event->type = 'im.vector.setting.breadcrumbs';

        $event->content = (object)['test' => 'test'];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame([
            'test' => 'test',
            'breadcrumbs' => [
                'test' => 'test'
            ],
        ], json_decode(json_encode($this->getProperty($this->repository, 'client')), true));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventDirect()
    {
        $this->testProcessEventWebSettings();
        $event = new \stdClass();
        $event->type = 'm.direct';

        $event->content = (object)[
            '@discordbot:fake.tools' => [
                '!channel:fake.tools',
            ],
        ];
        $this->repository->processEvent($event);
        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame([
            'test' => 'test',
            'dms' => [
                '@discordbot:fake.tools' => [
                    '!channel:fake.tools',
                ]
            ]
        ], json_decode(json_encode($this->getProperty($this->repository, 'client')), true));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }

    public function testProcessEventPushRules()
    {
        $event = new \stdClass();
        $event->type = 'm.push_rules';

        $event->content = (object)['test' => 'test'];
        $this->repository->processEvent($event);

        $this->assertSame([], $this->getProperty($this->repository, 'devices'));
        $this->assertSame([], $this->getProperty($this->repository, 'keys'));
        $this->assertSame(null, $this->getProperty($this->repository, 'default_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'master_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'self_sign_key'));
        $this->assertSame(null, $this->getProperty($this->repository, 'signing_user'));
        $this->assertSame(null, $this->getProperty($this->repository, 'megolm_backup'));
        $this->assertSame(null, $this->getProperty($this->repository, 'client'));
        $this->assertSame(null, $this->getProperty($this->repository, 'notification_settings'));
    }
}