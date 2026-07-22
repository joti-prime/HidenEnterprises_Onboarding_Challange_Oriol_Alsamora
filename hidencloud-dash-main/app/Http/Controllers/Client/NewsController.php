<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\ArticleReaction;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $articles = Article::latest()->paginate(5);

        return Theme::view('news.index', ['articles' => $articles]);
    }

    public function article($article)
    {
        $article = Article::where('path', $article)->firstOrFail();

        if ($article->status == 'draft') {
            return redirect()->back();
        }

        if (!$article->allow_guests and auth()->guest()) {
            return redirect()->route('login');
        }

        $article->increment('views');

        return Theme::view('news.article', ['article' => $article]);
    }

    public function helpful(Article $article, $rating)
    {
        if (request()->hasCookie($article->id . 'feedback')) {
            return redirect()->back();
        }

        if ($rating == 'like') {
            $article->increment('likes');
        }

        if ($rating == 'dislike') {
            $article->increment('dislikes');
        }

        return redirect()->back()->withCookie($article->id . 'feedback', true);
    }

    public function comment(Request $request, Article $article)
    {
        $request->validate([
            'comment' => 'required|min:3|max:750',
        ]);

        $comment = new ArticleComment;
        $comment->user_id = auth()->user()->id;
        $comment->article_id = $article->id;
        $comment->body = $request->input('comment');
        $comment->save();

        return redirect()->back()->withSuccess('Your comment has been posted.');
    }

    public function react(Article $article, $emoji)
    {
        if (!in_array($emoji, ['fire', 'medal', 'moneybag', 'party'])) {
            return redirect()->back();
        }

        $reaction = $article->reactions()->where('user_id', auth()->user()->id)->where('emoji', $emoji)->first();
        if ($reaction) {
            $reaction->delete();

            return redirect()->back();
        }

        $reaction = new ArticleReaction;
        $reaction->user_id = auth()->user()->id;
        $reaction->article_id = $article->id;
        $reaction->emoji = $emoji;
        $reaction->save();

        return redirect()->back();
    }

    public function upvoteComment(ArticleComment $comment)
    {
        $comment->increment('upvotes');

        return redirect()->back();
    }

    public function downvoteComment(ArticleComment $comment)
    {
        $comment->decrement('upvotes');

        return redirect()->back();
    }

    public function removeComment(ArticleComment $comment)
    {
        if ($comment->user->id !== auth()->user()->id and !auth()->user()->is_admin()) {
            return redirect()->back()->withError('You don\'t have access to this resource.');
        }

        $comment->delete();

        return redirect()->back()->withSuccess('The comment has been deleted.');
    }

    public function reportComment(ArticleComment $comment)
    {
        return redirect()->back()->withSuccess('The comment has been reported.');
    }
}
