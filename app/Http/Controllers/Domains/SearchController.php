<?php

namespace App\Http\Controllers\Domains;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        return Inertia::render('Domains/Search', [

        ]);
    }
}
