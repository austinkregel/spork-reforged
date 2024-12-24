<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Credential;
use App\Models\Person;
use App\Models\User;
use App\Services\Messaging\ImapFactoryService;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMailboxIfCredentialsAreSet implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Credential $credential,
        protected ?Carbon $since = null,
    ) {
        $this->since = now()->subMonth();
    }

    public function handle(ImapFactoryService $imapFactory): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }
        $imapService = $imapFactory->make($this->credential);

        info('Imap service seems to have credentials, trying to access inbox');
        $start = now();
        $messages = $imapService->findAllFromDate('INBOX', $this->since);
        $end = now();

        info('Found '.count($messages).' messages in '.$start->diffInSeconds($end).' seconds');

        foreach ($messages as $i => $message) {
            $trackedMessage = $this->credential->emails()->firstWhere([
                'email_id' => $message['id'],
            ]);

            if (empty($trackedMessage)) {
                $body = $imapService->findMessage((string) $message['id']);
                $trackedMessage = $this->credential->emails()->create([
                    'email_id' => $message['id'],
                    'from_email' => (empty($message['from']['email']) ? null : $message['from']['email']) ?? $message['addressed-from']['email'] ?? null,
                    'to_email' => (empty($message['to']['email']) ? null : $message['to']['email']) ?? $message['addressed-to']['email'] ?? null,
                    'sent_at' => $message['date'],
                    'subject' => $body['subject'],
                    'message' => $body['body'],
                    'seen' => $body['seen'],
                    'spam' => $body['spam'],
                    'answered' => $body['answered'],
                ]);
            } else {
                $body = $imapService->findMessage((string) $message['id']);
                collect([
                    'sent_at' => $message['date'],
                    'message' => $body['body'],
                    'from_email' => (empty($message['from']['email']) ? null : $message['from']['email']) ?? $message['addressed-from']['email'] ?? null,
                    'to_email' => (empty($message['to']['email']) ? null : $message['to']['email']) ?? $message['addressed-to']['email'] ?? null,
                    'subject' => $body['subject'],
                    'seen' => $body['seen'],
                    'spam' => $body['spam'],
                    'answered' => $body['answered'],
                ])->map(function ($value, $key) use ($trackedMessage) {
                    if ($value !== $trackedMessage->$key) {
                        $trackedMessage->$key = $value;
                    }
                });

                if ($trackedMessage->isDirty([
                    'sent_at',
                    'message',
                    'seen',
                    'spam',
                    'answered',
                    'subject',
                ])) {
                    $this->getPersonToEmail($message);
                    $this->getPersonFromEmail($message);
                    $trackedMessage->save();
                }
            }
        }
        info('Finished syncing mailbox');
    }

    protected function getPersonFromEmail(array $message)
    {
        $fromName = $message['from']['name'] ?? $message['addressed-from']['name'] ?? $message['from']['email'];
        $fromEmail = $message['from']['email'] ?? $message['addressed-from']['email'] ?? null;

        if (empty($fromEmail)) {
            dd('no email found, where should we get an email', $message);

            return;
        }

        $person = Person::whereJsonContains('emails', $fromEmail)
            // for now, this is fine, my email base does support this idea, but I know if someone/
            // wanted to be malicious they could take advantage of this.
            ->orWhere('name', $fromName)
            ->first();

        if (empty($person)) {
            $person = Person::create([
                'name' => $fromName,
                'emails' => [$fromEmail],
                'user_id' => User::firstWhere('email', env('SPORK_ADMIN_EMAILS'))->id,
            ]);
        }
        $emails = array_values(array_unique(array_filter(array_merge($person->emails, [$message['from']['email']]), fn ($val) => ! empty($val))));

        if (! empty(array_diff($person->emails, $emails)) || ! empty(array_diff($emails, $person->emails))) {
            $person->update(compact('emails'));
        }

        return $person->id;
    }

    protected function getPersonToEmail(array $message)
    {
        $fromName = $message['to']['name'] ?? $message['addressed-to']['name'] ?? $message['to']['email'];
        $fromEmail = (empty($message['to']['email']) ? null : $message['addressed-to']['email']) ?? $message['to']['original'] ?? null;

        if (empty($fromEmail)) {
            dd('no email found, where should we get an email', $message);

            return;
        }

        if (str_starts_with($fromEmail, '<')) {
            $fromEmail = trim($fromEmail, '<>');
        }

        $person = Person::whereJsonContains('emails', $fromEmail)
            // for now, this is fine, my email base does support this idea, but I know if someone/
            // wanted to be malicious they could take advantage of this.
            ->first();

        if (empty($person)) {
            $person = Person::first();

            $person->update([
                'emails' => array_values(array_unique(array_merge($person->emails, [strtolower($fromEmail)]))),
            ]);
            // Need some way to determine a "default" user to assign messages to if they don't already have a person record.
        }

        return $person->id;
    }
}
