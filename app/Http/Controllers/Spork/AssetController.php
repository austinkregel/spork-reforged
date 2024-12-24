<?php

namespace App\Http\Controllers\Spork;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Services\Development\DescribeTableService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index()
    {
        return Inertia::render('Assets/Index', [
            'description' => (new DescribeTableService)->describe(new Asset),
            'assets' => Asset::query()
                ->with('owner')
                ->paginate(),
        ]);
    }

    public function labels()
    {

    }
}
