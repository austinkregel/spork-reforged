<?php

declare(strict_types=1);

namespace App\Services\Messaging;

use App\Contracts\Services\ImapServiceContract;
use App\Models\Credential;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImapCredentialService implements ImapServiceContract
{
    public function __construct(
        protected Credential $credential
    ) {
    }

    public function findAllMailboxes(): Collection
    {
        return collect(imap_list($inbox = imap_open(
            $this->buildMailboxString(),
            $this->credential->settings['username'],
            $this->credential->settings['password']
        ), $this->buildMailboxString(), '*'))
            ->tap(fn () => imap_close($inbox))
            ->map(fn ($mailbox) => Str::of($mailbox)
                // Remove the mailbox string
                ->replace($this->buildMailboxString(), '')
                ->toString()
            )
            // Don't return mailboxes explicitly marked as labels.... GOOGLE...
            ->filter(fn ($mailbox) => ! str_starts_with($mailbox, 'Labels'))
            ->values();
    }

    public function findAllFromDate(string $mailbox, Carbon $date): Collection
    {
        return collect(imap_search($inbox = imap_open(
            sprintf('%s%s', $this->buildMailboxString(), $mailbox),
            $this->credential->settings['username'],
            $this->credential->settings['password'],
        ),
            sprintf('SINCE "%s"', $date->format('Y-m-d'))
        ))
            ->map(function ($messageNumber) use ($inbox) {
                $headers = Str::of($headerRaw = imap_fetchheader($inbox, $messageNumber))
                    ->explode("\r\n")
                    ->filter()
                    ->reduce(fn ($lines, $line) => array_merge(
                        $lines,
                        [explode(': ', $line, 2)[0] => explode(': ', $line, 2)[1] ?? null]
                    ), []);

                $rfcHeaders = imap_rfc822_parse_headers($headerRaw);
                $body = null;
                $overview = Arr::first(imap_fetch_overview($inbox, (string) $messageNumber));

                try {
                    Carbon::parse($headers['X-Pm-Date']);
                } catch (\Throwable $e) {
                    dd($headers);
                }

                if (empty($headers['To'])) {
                    // ew
                    $headers['To'] = $headers['Delivered-To'];
                }

                // Many of these headers are probably specific to me using proton mail... It may not work for everyone...
                return [
                    'id' => imap_uid($inbox, $messageNumber),
                    'to' => $this->extractEmailAndName($headers['To']),
                    'addressed-to' => $this->extractEmailAndName($headers['X-Simplelogin-Envelope-To'] ?? $headers['X-Original-To'] ?? null),
                    'addressed-from' => $this->extractEmailAndName($rfcHeaders->fromaddress ?? $headers['X-Pm-External-Id'] ?? null),
                    'date' => Carbon::parse($headers['X-Pm-Date']),
                    'human_date' => Carbon::parse($headers['X-Pm-Date'])->fromNow(),
                    'subject' => imap_utf8($headers['Subject']),
                    'from' => $this->extractEmailAndName($rfcHeaders->senderaddress ?? $rfcHeaders->fromaddress ?? $headers['From'], $headers),
                    'reply-to' => $this->extractEmailAndName($rfcHeaders->reply_toaddress ?? $headers['Reply-To']),
                    'spam' => intval($headers['X-Pm-Spamscore'] ?? 0),
                    'seen' => (bool) $overview->seen ?? false,
                    'deleted' => (bool) $overview->deleted ?? false,
                    'answered' => (bool) $overview->answered ?? false,
                    'recent' => (bool) $overview->recent ?? false,
                    'draft' => (bool) $overview->draft ?? false,
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->tap(fn () => imap_close($inbox));
    }
    public function findAllUnseen(string $mailbox): Collection
    {
        return collect(imap_search($inbox = imap_open(
            sprintf('%s%s', $this->buildMailboxString(), $mailbox),
            $this->credential->settings['username'],
            $this->credential->settings['password'],
        ),
            sprintf(' SEEN')
        ))
            ->map(function ($messageNumber) use ($inbox) {
                $headers = Str::of($headerRaw = imap_fetchheader($inbox, $messageNumber))
                    ->explode("\r\n")
                    ->filter()
                    ->reduce(fn ($lines, $line) => array_merge(
                        $lines,
                        [explode(': ', $line, 2)[0] => explode(': ', $line, 2)[1] ?? null]
                    ), []);

                $rfcHeaders = imap_rfc822_parse_headers($headerRaw);
                $body = null;
                $overview = Arr::first(imap_fetch_overview($inbox, (string) $messageNumber));

                try {
                    Carbon::parse($headers['X-Pm-Date']);
                } catch (\Throwable $e) {
                    dd($headers);
                }

                if (empty($headers['To'])) {
                    // ew
                    $headers['To'] = $headers['Delivered-To'];
                }

                // Many of these headers are probably specific to me using proton mail... It may not work for everyone...
                return [
                    'id' => imap_uid($inbox, $messageNumber),
                    'to' => $this->extractEmailAndName($headers['To']),
                    'addressed-to' => $this->extractEmailAndName($headers['X-Simplelogin-Envelope-To'] ?? $headers['X-Original-To'] ?? null),
                    'addressed-from' => $this->extractEmailAndName($rfcHeaders->fromaddress ?? $headers['X-Pm-External-Id'] ?? null),
                    'date' => Carbon::parse($headers['X-Pm-Date']),
                    'human_date' => Carbon::parse($headers['X-Pm-Date'])->fromNow(),
                    'subject' => imap_utf8($headers['Subject']),
                    'from' => $this->extractEmailAndName($rfcHeaders->senderaddress ?? $rfcHeaders->fromaddress ?? $headers['From'], $headers),
                    'reply-to' => $this->extractEmailAndName($rfcHeaders->reply_toaddress ?? $headers['Reply-To']),
                    'spam' => intval($headers['X-Pm-Spamscore'] ?? 0),
                    'seen' => (bool) $overview->seen ?? false,
                    'deleted' => (bool) $overview->deleted ?? false,
                    'answered' => (bool) $overview->answered ?? false,
                    'recent' => (bool) $overview->recent ?? false,
                    'draft' => (bool) $overview->draft ?? false,
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->tap(fn () => imap_close($inbox));
    }

    public function findMessage(string $messageNumber, bool $peak = true): array
    {
        $mailbox = new \PhpImap\Mailbox(
            sprintf($this->buildMailboxString().'INBOX'), // IMAP server and mailbox folder
            $this->credential->settings['username'],
            $this->credential->settings['password'],
            storage_path(), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            false // Attachment filename mode (optional; false = random filename; true = original filename)
        );

        $message = $mailbox->getMail((int) $messageNumber, false);

        $headers = Str::of($message->headersRaw)
            ->explode("\r\n")
            ->filter()
            ->reduce(fn ($lines, $line) => array_merge(
                $lines,
                [explode(': ', $line, 2)[0] => explode(': ', $line, 2)[1] ?? null]
            ), []);

        $rfcHeaders = imap_rfc822_parse_headers($message->headersRaw);

        $body = base64_encode(empty($message->textHtml) ? $message->textPlain : $message->textHtml);

        if (empty($headers['To'])) {
            // ew
            $headers['To'] = $headers['Delivered-To'];
        }

        return [
            'id' => $messageNumber,
            'to' => $this->extractEmailAndName($headers['To']),
            'addressed-to' => $this->extractEmailAndName($headers['X-Simplelogin-Envelope-To'] ?? $headers['X-Original-To'] ?? null),
            'addressed-from' => $this->extractEmailAndName($rfcHeaders->fromaddress ?? $headers['X-Pm-External-Id'] ?? null),
            'date' => Carbon::parse($headers['X-Pm-Date']),
            'human_date' => Carbon::parse($headers['X-Pm-Date'])->fromNow(),
            'subject' => imap_utf8($headers['Subject']),
            'from' => $this->extractEmailAndName($rfcHeaders->senderaddress ?? $rfcHeaders->fromaddress ?? $headers['From'], $headers),
            'reply-to' => $this->extractEmailAndName($rfcHeaders->reply_toaddress ?? $headers['Reply-To']),
            'spam' => $headers['X-Pm-Spamscore'],
            'seen' => $message->isSeen ?? false,
            'deleted' => $message->isDeleted ?? false,
            'answered' => $message->isAnswered ?? false,
            'recent' => $message->isRecent ?? false,
            'draft' => $message->isDraft ?? false,
            'body' => $body,
            'view' => empty($message->textHtml) ? 'render-plain-inbox' : 'render-rich-inbox',
        ];
    }

    protected function buildMailboxString()
    {
        return sprintf(
            '{%s:%s/imap/%s}',
            $this->credential->settings['host'],
            $this->credential->settings['port'],
            $this->credential->settings['encryption'],
        );
    }

    protected function extractEmailAndName(?string $value, $headers = [])
    {
        if (empty($value)) {
            return $value;
        }

        if (! str_contains($value, '<')) {
            return array_merge([
                'email' => $value,
            ]);
        }

        if (str_contains($value, '"')) {
            preg_match_all('/(\".*\")?(\s)?(\<.*\>)/', $value, $matches);
        } else {
            preg_match_all('/(.*)(\s)(\<.*\>)/', $value, $matches);
        }

        return match (count($matches)) {
            3 => [
                'email' => trim(Arr::first($matches[2])),
                'original' => $value,
            ],
            4 => empty(trim((string) Arr::first($matches[1]), "\"'")) ? [
                // Address
                'email' => trim((string) Arr::first($matches[3]), '<>'),
                'original' => $value,
            ] : [
                // Name
                'name' => trim(Arr::first($matches[1]), "\"'"),
                // Address
                'email' => trim(Arr::first($matches[3]), '<>'),
                'original' => $value,
            ],
        };
    }

    public function markAsRead(string $messageId): void
    {
        $mailbox = new \PhpImap\Mailbox(
            sprintf($this->buildMailboxString().'INBOX'), // IMAP server and mailbox folder
            $this->credential->settings['username'],
            $this->credential->settings['password'],
            storage_path(), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            false // Attachment filename mode (optional; false = random filename; true = original filename)
        );

        $mailbox->getMail((int) $messageId, true);
    }

    public function markAsUnread(string $messageId): void
    {
        $mailbox = new \PhpImap\Mailbox(
            sprintf($this->buildMailboxString().'INBOX'), // IMAP server and mailbox folder
            $this->credential->settings['username'],
            $this->credential->settings['password'],
            storage_path(), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            false // Attachment filename mode (optional; false = random filename; true = original filename)
        );

        $mailbox->markMailAsUnread((int) $messageId);
    }
}
