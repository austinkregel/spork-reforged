<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Models\Finance\Transaction;
use App\Models\Tag;
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

        $dateRange = match ($selectedRange = strtolower(request()->get('range', 'MTD'))) {
            'mtd' => [now()->startOfMonth(), now()],
            'ytd' => [now()->startOfYear(), now()],
            'all' => [now()->subYears(10), now()],

            '7d' => [now()->subDays(7), now()],
            '30d' => [now()->subDays(30), now()],
            '60d' => [now()->subDays(60), now()],

            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        /**
         * @var \Carbon\Carbon $startDate
         * @var \Carbon\Carbon $endDate
         */
        [$startDate, $endDate] = $dateRange;

        $labels = iterator_to_array($startDate->range($endDate)->map(fn ($date) => $date->format('Y-m-d')));

        $graphData = request()->user()
            ->tags()
            ->with([
                'transactions' => fn ($query) => $query->whereIn('account_id', $accounts->pluck('account_id'))
                    ->where('date', '>=', $startDate)
                    ->where('date', '<=', $endDate)
                    ->where('name', 'not like', '%transfer%')
                    ->select(['amount', 'date']),
            ])
            ->get()
            ->reduce(function (array $carry, Tag $tag) {
                // We need to get all the dates
                $data = [
                    'label' => $tag->name,
                    'data' => $tag->transactions?->reduce(fn ($carry, $transaction) => array_merge($carry, [
                        $transaction->date->format('Y-m-d') => $transaction->amount + ($carry[$transaction->date->format('Y-m-d')] ?? 0)
                    ]), []),
                ];

                return array_merge($carry, [$data]);
            }, []);

        $lastMonth = now()->startOfMonth()->addHours(5);
        $beforeLastMonth = now()->startOfMonth()->subMonth()->addHours(5);


        return Inertia::render('Banking/Index', [
            'title' => 'Banking ',
            'accounts' => $accounts,
            'graphs' => [
                'labels' => $labels,
                'datasets' => $graphData
            ],
            'selected_range' => $selectedRange,
            'transactions' => $this->queryBuilder(),
            'stats' => [
                'total_income' => [
                    'current' => Tag::where('name->en', 'credit/income')
                        ->with([
                            'transactions' => fn ($query) => $query->where('date', '>=', $lastMonth)
                                ->where('date', '<=', $lastMonth->copy()->endOfMonth())
                                ->select(['amount'])
                        ])
                        ->get()
                        ->reduce(fn ($carry, Tag $tag) => $carry + abs($tag->transactions->sum('amount')), 0),
                    'previous' => Tag::where('name->en', 'credit/income')
                        ->with([
                            'transactions' => fn ($query) =>
                                $query->where('date', '>=', $beforeLastMonth)
                                ->where('date', '<=', $beforeLastMonth->copy()->endOfMonth())
                                ->select(['amount'])
                        ])
                        ->get()
                        ->reduce(fn ($carry, Tag $tag) => $carry + abs($tag->transactions->sum('amount')), 0),
                ],
                'total_expenses' => [
                    'current' => Tag::where('name->en', 'debit/expense')
                        ->with(['transactions' => fn ($query) => $query->where('date', '>=', $lastMonth)
                            ->where('date', '<=', $lastMonth->copy()->endOfMonth())
                            ->select(['amount'])])
                        ->get()
                        ->reduce(fn ($carry, Tag $tag) => $carry + $tag->transactions->sum('amount'), 0),
                    'previous' => Tag::where('name->en', 'debit/expense')
                        ->with(['transactions' => fn ($query) => $query->where('date', '>=', $beforeLastMonth)
                            ->where('date', '<=', $beforeLastMonth->copy()->endOfMonth())
                            ->select(['amount'])])
                        ->get()
                        ->reduce(fn ($carry, Tag $tag) => $carry + $tag->transactions->sum('amount'), 0),
                ],
                'other' => [
                    'current' => Tag::whereIn('name->en', [
                        'Fast Food',
                        'Food and Drink',
                        'Food and Beverage',
                        'Supermarkets and Groceries',
                        'Restaurants',
                    ])
                        ->with(['transactions' => fn ($query) => $query
                            ->where('date', '>=', $lastMonth)
                            ->where('date', '<=', $lastMonth->copy()->endOfMonth())
                            ->select(['amount'])])
                        ->get()
                        ->reduce(fn ($carry, Tag $tag) => $carry + $tag->transactions->sum('amount'), 0),
                    'previous' => Tag::whereIn('name->en', [
                        'Fast Food',
                        'Food and Drink',
                        'Food and Beverage',
                        'Supermarkets and Groceries',
                        'Restaurants',
                    ])
                        ->with(['transactions' => fn ($query) => $query
                            ->where('date', '>=', $beforeLastMonth)
                            ->where('date', '<=', $beforeLastMonth->copy()->endOfMonth())
                            ->select(['amount'])])
                        ->get()
                        ->reduce(fn ($carry, Tag $tag) => $carry + $tag->transactions->sum('amount'), 0),
                ],
            ]
        ]);
    }
    public function budgets()
    {
        return Inertia::render('Banking/Index', [
            'title' => 'Banking ',
            'accounts' => request()->user()
                ->accounts()
                ->with('credential')
                ->get(),
            'transactions' => $this->queryBuilder(),
        ]);
    }


    protected function queryBuilder()
    {
        return QueryBuilder::for(Transaction::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::callback('tag', function (Builder $builder, $name) {
                    $builder->whereHas('tags', fn ($query) => $query->where('name', 'like', '%'.$name.'%'));
                }),
                AllowedFilter::partial('date'),
                AllowedFilter::callback('range', fn () => null),
            ])
            ->allowedIncludes(['account', 'tags'])
            ->allowedSorts(['name', 'amount', 'date'])
            ->where('name', 'not like', '%transfer%')
            ->whereIn('account_id', request()->user()
                ->accounts()
                ->with('credential')
                ->get()->pluck('account_id'))
            ->with([
                'tags' => fn ($query) => $query->where('type', 'automatic'),
                'account'
            ])
            ->orderByDesc('date')
            ->paginate();
    }
}
