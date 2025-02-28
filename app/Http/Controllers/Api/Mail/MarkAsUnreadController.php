<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mail;

use App\Http\Controllers\Controller;
use App\Http\Requests\Messages\MailOwnerRequest;
use App\Models\Email;
use App\Models\Message;
use App\Services\Messaging\ImapFactoryService;

class MarkAsUnreadController extends Controller
{
    public function __invoke(MailOwnerRequest $request, ImapFactoryService $factoryService)
    {
        request()->validate([
            'id' => 'integer',
        ]);

        $message = Email::query()
            ->with('credential')
            ->findOrFail($request->get('id'));

        $service = $factoryService->make($message->credential);
        $service->markAsUnread($message->email_id);
        $message->update([
            'seen' => false,
        ]);
    }
}
