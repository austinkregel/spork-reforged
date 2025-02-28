<?php

declare(strict_types=1);

namespace App\Listeners\Email;

use App\Events\Models\Email\EmailCreated;
use App\Events\Models\Transaction\TransactionCreated;
use App\Models\Email;
use App\Models\Finance\Account;
use App\Models\Finance\Transaction;
use App\Models\Tag;
use App\Services\ConditionService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApplyUserAutomatedTagsToEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(EmailCreated $event): void
    {
        $event->model->load('credential.user');
        $event->model->refresh();

        /** @var Email $email */
        $email = $event->model;
        $credential = $email->credential;

        $user = $credential->user;

        if (empty($user)) {
            return;
        }

        $tags = $user->tags()->with('conditions')->whereIn('type', ['automatic', 'info'])->get();

        $conditionService = new ConditionService();

        $tagsToApply = $tags->filter(fn (Tag $tag) => $conditionService->process($tag, [
            'email' => $email,
        ]));

        foreach ($tagsToApply as $tag) {
            if (! $email->tags()->where('id', $tag->id)->exists()) {
                $email->tags()->attach($tag);
            }
        }
    }
}
