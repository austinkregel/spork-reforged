<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Message;
use App\Models\Thread;
use App\Services\ConditionService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        if (auth()->check()) {
            auth()->user()->setRelation('person', auth()->user()->person());
            auth()->user()->load('credentials');
        }
        $navigation = (new ConditionService)->navigation();

        return array_merge(parent::share($request), [
            'navigation' => $navigation,
            'current_navigation' => $navigation->flatten(1)->where('current', true)->first(),
            'conversations' => auth()->check() ? Thread::query()
                ->with(['messages', 'messages.fromPerson', 'messages.toPerson'])
                ->whereHas('participants', function ($query) {
                    $query->where('person_id', auth()->user()?->person()?->id);
                })
                ->whereHas('messages')
                ->orderByDesc('updated_at')
                ->paginate(
                    request('conversation_limit'),
                    ['*'],
                    'conversation_page',
                    request('conversation_page')
                ) : null,
            'unread_email_count' => 0,
            'notifications' => $request->user()?->notifications ?? [],
        ]);
    }
}
