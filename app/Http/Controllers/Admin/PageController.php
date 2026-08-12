<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(PageService $pages): View
    {
        $pages = $pages->paginate();

        return view('admin.pages.index', compact('pages'));
    }

    public function store(StorePageRequest $request, PageService $pages): RedirectResponse
    {
        $pages->create($request->validated());

        return back()->with('success', __('Page created.'));
    }

    public function update(UpdatePageRequest $request, Page $page, PageService $pages): RedirectResponse
    {
        $pages->update($page, $request->validated(), $request->boolean('is_published', true));

        return back()->with('success', __('Page updated.'));
    }
}
