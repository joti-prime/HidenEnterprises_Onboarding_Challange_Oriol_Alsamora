<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function index()
    {
        $pages = Page::query()->latest()->paginate(15);

        return Theme::view('pages.index', compact('pages'));
    }

    public function create()
    {
        return Theme::view('pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'is_enabled' => 'boolean',
            'allow_guests' => 'boolean',
            'basic_page' => 'boolean',
            'new_tab' => 'boolean',
            'title' => 'required|string',
            'path' => 'required|unique:pages',
        ]);

        $page = new Page;
        $page->is_enabled = $request->input('is_enabled', false);
        $page->allow_guests = $request->input('allow_guests', false);
        $page->basic_page = $request->input('basic_page', false);
        $page->name = $request->input('name');
        $page->title = $request->input('title');
        $page->path = $request->input('path');
        $page->icon = $request->input('icon');
        $page->placement = $request->input('placement', []);
        $page->content = $request->input('content');
        $page->new_tab = $request->input('new_tab', false);
        $page->redirect_url = $request->input('redirect');
        $page->save();

        return redirect()->route('pages.index')->with('success',
            trans('responses.page_create_success',
                ['name' => $page->name, 'default' => 'Page :name created successfully.'])
        );
    }

    public function edit(Page $page)
    {
        return Theme::view('pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'is_enabled' => 'boolean',
            'allow_guests' => 'boolean',
            'basic_page' => 'boolean',
            'new_tab' => 'boolean',
            'title' => 'required|string',
            'path' => 'required|unique:pages,path,' . $page->id,
        ]);

        $page->is_enabled = $request->input('is_enabled', false);
        $page->allow_guests = $request->input('allow_guests', false);
        $page->basic_page = $request->input('basic_page', false);
        $page->name = $request->input('name');
        $page->title = $request->input('title');
        $page->path = $request->input('path');
        $page->icon = $request->input('icon');
        $page->placement = $request->input('placement', []);
        $page->content = $request->input('content');
        $page->new_tab = $request->input('new_tab', false);
        $page->redirect_url = $request->input('redirect');
        $page->save();

        return redirect()->back()->with('success',
            trans('responses.page_update_success',
                ['name' => $page->name, 'default' => 'Page :name updated successfully.'])
        );
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('pages.index')->with('success',
            trans('responses.page_delete_success',
                ['name' => $page->name, 'default' => 'Page :name deleted successfully.'])
        );
    }

    public function translation($id)
    {
        $translations = PageTranslation::query()->where('page_id', $id)->get();

        return Theme::view('pages.translation', compact('translations', 'id'));
    }

    public function translationEdit($id, $locale = null)
    {
        $page = Page::query()->find($id);
        if ($page) {
            $translation = $page->translations()->where('locale', $locale)->first();
            if ($translation) {
                $page->name = $translation->name;
                $page->title = $translation->title;
                $page->content = $translation->content;
            }

            return Theme::view('pages.translation_edit', compact('page', 'locale'));
        } else {
            abort(404, 'Page not found');
        }
    }

    public function translationStore(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'name' => 'required|string',
            'locale' => 'required|string',
        ]);
        $translation = PageTranslation::query()->where('page_id', $id)->where('locale', $request->input('locale', 'en'))->first();
        if (!$translation) {
            $translation = new PageTranslation;
        }
        $translation->page_id = $id;
        $translation->name = $request->input('name');
        $translation->title = $request->input('title');
        $translation->content = $request->input('content');
        $translation->locale = $request->input('locale', 'en');
        $translation->save();

        return redirect()->route('pages.translation', $translation->page_id)->with('success',
            trans('responses.page_update_success',
                ['name' => $translation->name])
        );
    }

    public function translationDestroy(PageTranslation $translation)
    {
        $translation->delete();

        return redirect()->route('pages.translation', $translation->page_id)->with('success',
            trans('responses.page_delete_success',
                ['name' => $translation->name])
        );
    }
}
