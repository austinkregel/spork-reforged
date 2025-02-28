<?php

namespace App\Jobs;

use App\Models\Finance\Transaction;
use App\Models\Tag;
use App\Services\ConditionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncTagsWithTransactionsInDatabase implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $page = 1;
        $tags = Tag::with('conditions')->where('type', 'automatic')->get();
        do {
            $paginator = Transaction::with('account')
                ->paginate(100, ['*'], 'page', $page++);

            foreach ($paginator as $transaction) {
                $conditionService = new ConditionService();

                $tagsToApply = $tags->filter(fn (Tag $tag) => $conditionService->process($tag, [
                    'transaction' => $transaction,
                    'account' => $transaction->account,
                ]));

                foreach ($tagsToApply as $tag) {
                    if (! $transaction->tags()->where('id', $tag->id)->exists()) {
                        $transaction->tags()->attach($tag);
                    }
                }
            }
        } while ($paginator->hasMorePages());
    }
}
