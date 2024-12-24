<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Models\Tag;
use App\Services\Code;
use Inertia\Inertia;
use Spatie\Tags\HasTags;

class TagManagerController
{
    public function __invoke()
    {
        return Inertia::render('Tags/Index', [
            'tags' => \App\Models\Tag::withCount([
                'conditions',
                'articles',
                'feeds',
                'servers',
                'transactions',
                'projects',
                'budgets',
                'accounts',
                'domains',
                'people',
                'messages',
            ])->withSum('transactions', 'amount')
                ->with(['conditions'])
                ->orderBy('type')
                ->paginate(
                    request('limit', 30),
                    ['*'],
                    'page',
                    request('page')
                ),
        ]);
    }

    public function show(Tag $tag)
    {
        //        dd(collect(Code::instancesOf(HasTags::class)->getClasses())->map(fn ($class) => (new $class)->getTable()));
        return Inertia::render('Tags/Show', [
            'tag' => $tag->loadSum('transactions', 'amount')
                ->load([
                    'conditions',
                    'articles' => function ($q) {
                        $q->latest('last_modified');
                    },
                    'feeds' => function ($q) {
                        $q->latest('last_modified');
                    },
                    'servers',
                    'transactions' => function ($q) {
                        $q->latest('date');
                    },
                    'projects',
                    'budgets',
                    'accounts',
                    'domains',
                    'people',
                    'messages',
                ]),
            'type' => Tag::class,
        ]);
    }
}
