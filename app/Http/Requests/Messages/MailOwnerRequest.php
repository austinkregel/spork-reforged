<?php

declare(strict_types=1);

namespace App\Http\Requests\Messages;

use App\Models\Email;
use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class MailOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $messageId = $this->get('id');
        $message = Email::findOrFail($messageId);

        return $this->user()->credentials()->where('id', $message->credential_id)->exists();
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
