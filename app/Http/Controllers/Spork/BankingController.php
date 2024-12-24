<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Models\Finance\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BankingController
{
    public function __invoke()
    {
        $accounts = request()->user()
            ->accounts()
            ->with('credential')
            ->get();

        return Inertia::render('Banking/Index', [
            'title' => 'Banking ',
            'accounts' => $accounts,
            'transactions' => QueryBuilder::for(Transaction::class)
                ->allowedFilters([
                    AllowedFilter::partial('name'),
                    AllowedFilter::callback('tag', function (Builder $builder, $name) {
                        $builder->whereHas('tags', fn ($query) => $query->where('name', 'like', '%'.$name.'%'));
                    }),
                    AllowedFilter::partial('date'),
                ])
                ->allowedIncludes(['account', 'tags'])
                ->allowedSorts(['name', 'amount', 'date'])
                ->whereIn('account_id', $accounts->pluck('account_id'))
                ->with('tags')
                ->orderByDesc('date')
                ->paginate(),
        ]);
    }
}
