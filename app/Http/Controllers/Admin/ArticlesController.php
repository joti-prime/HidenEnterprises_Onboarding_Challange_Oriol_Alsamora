<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function index()
    {
        $articles = Article::query()->latest()->paginate(15);

        return Theme::view('articles.index', compact('articles'));
    }

    public function create()
    {
        return Theme::view('articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required',
            'title' => 'required|string',
            'path' => 'required|unique:articles',
            'labels' => 'nullable|array',
            'allow_comments' => 'boolean',
            'allow_guests' => 'boolean',
            'show_author' => 'boolean',
            'short_desc' => 'nullable',
        ]);

        $article = new Article;
        $article->user_id = auth()->user()->id;
        $article->status = $request->input('status');
        $article->title = $request->input('title');
        $article->path = $request->input('path');
        $article->allow_guests = $request->input('allow_guests', false);
        $article->show_author = $request->input('show_author', false);
        $article->allow_comments = $request->input('allow_comments', false);
        $article->content = $request->input('content');
        $article->short_desc = $request->input('short_desc');
        $article->labels = $request->input('labels', []);
        $article->save();

        return redirect()->route('articles.index')->with(
            'success',
            'Page was created successfully'
        );
    }

    public function edit(Article $article)
    {
        return Theme::view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'status' => 'required',
            'title' => 'required|string',
            'path' => 'required|unique:articles,path,'.$article->id,
            'labels' => 'nullable|array',
            'allow_comments' => 'boolean',
            'allow_guests' => 'boolean',
            'show_author' => 'boolean',
            'short_desc' => 'nullable',
        ]);

        $article->status = $request->input('status');
        $article->title = $request->input('title');
        $article->path = $request->input('path');
        $article->allow_guests = $request->input('allow_guests', false);
        $article->show_author = $request->input('show_author', false);
        $article->allow_comments = $request->input('allow_comments', false);
        $article->content = $request->input('content');
        $article->short_desc = $request->input('short_desc');
        $article->labels = $request->input('labels', []);
        $article->save();

        return redirect()->back()->with(
            'success',
            'Page was created successfully'
        );
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->back()->with(
            'success',
            'Article was deleted successfully'
        );
    }

    public function translation($id)
    {
        $translations = ArticleTranslation::query()->where('article_id', $id)->get();

        return Theme::view('articles.translation', compact('translations', 'id'));
    }

    public function translationEdit($id, $locale = null)
    {
        $page = Article::query()->find($id);
        if ($page) {
            $translation = $page->translations()->where('locale', $locale)->first();
            if ($translation) {
                $page->title = $translation->title;
                $page->content = $translation->content;
            }

            return Theme::view('articles.translation_edit', compact('page', 'locale'));
        } else {
            abort(404, 'Page not found');
        }
    }

    public function translationStore(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'locale' => 'required|string',
        ]);
        $translation = ArticleTranslation::query()->where('article_id', $id)->where('locale', $request->input('locale', 'en'))->first();
        if (!$translation) {
            $translation = new ArticleTranslation;
        }
        $translation->article_id = $id;
        $translation->title = $request->input('title');
        $translation->content = $request->input('content');
        $translation->locale = $request->input('locale', 'en');
        $translation->save();

        return redirect()->route('articles.translation', $translation->article_id)->with('success',
            trans('responses.page_update_success',
                ['name' => $translation->title])
        );
    }

    public function translationDestroy(ArticleTranslation $translation)
    {
        $translation->delete();

        return redirect()->route('articles.translation', $translation->article_id)->with('success',
            trans('responses.page_delete_success',
                ['name' => $translation->title])
        );
    }
}
