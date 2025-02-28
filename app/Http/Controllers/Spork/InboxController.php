<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Models\Credential;
use App\Models\Email;
use App\Services\Messaging\ImapCredentialService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class InboxController
{
    public function index()
    {
        $paginator = Email::query()
            ->whereHas('credential', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with('from', 'to')
            ->orderByDesc('sent_at')
            ->paginate();

        return Inertia::render('Postal/Inbox', [
            'messages' => $paginator,
        ]);
    }

    public function show(Email $email)
    {
        $email->load('credential');

        abort_unless($email->credential->user_id === auth()->id(), 4035);

        $email = (new ImapCredentialService($email->credential))->findMessage($email->email_id, true);
        $messageBody = base64_decode($email['body']);

        $bodyWithTheImagesDisabledForPrivacy = str_replace(' src=', ' data-src=', $messageBody);

        return view('emails.'.$email['view'], [
            'body' => $bodyWithTheImagesDisabledForPrivacy,
        ]);
    }
}
