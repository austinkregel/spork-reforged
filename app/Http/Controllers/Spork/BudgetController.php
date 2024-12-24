<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Models\Finance\Budget;
use App\Models\Finance\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BudgetController
{
    public function show(Budget $budget)
    {
        $budget->load('user', 'tags.transactions');
        return Inertia::render('Budget/Index', [
            'title' => 'Budget Management',
            'budget' => $budget,
        ]);
    }
}
