<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Flexible\BlockRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;

class PageController extends Controller
{
    public function home(BlockRegistry $registry): View
    {
        if (! Schema::hasTable('pages')) {
            return view('pages.empty');
        }

        $page = Page::query()
            ->where('slug', 'home')
            ->where('status', 'published')
            ->with('blocks')
            ->first();

        if (! $page) {
            $page = Page::query()
                ->where('status', 'published')
                ->with('blocks')
                ->orderBy('id')
                ->first();
        }

        if (! $page) {
            return view('pages.empty');
        }

        return view('pages.show', compact('page', 'registry'));
    }

    public function show(string $slug, BlockRegistry $registry): View
    {
        if (! Schema::hasTable('pages')) {
            throw new ModelNotFoundException();
        }

        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with('blocks')
            ->first();

        if (! $page) {
            throw new ModelNotFoundException();
        }

        return view('pages.show', compact('page', 'registry'));
    }
}
