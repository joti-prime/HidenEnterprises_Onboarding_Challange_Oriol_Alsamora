<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\OrderMember;
use App\Models\Page;
use App\Models\Punishment;

class DashboardController extends Controller
{
    public function index()
    {
        return Theme::view('dashboard.index');
    }

    public function balance()
    {
        return Theme::view('dashboard.balance');
    }

    public function invoices()
    {
        return Theme::view('dashboard.invoices');
    }

    public function page($page)
    {
        $page = Page::wherePath($page)->firstOrFail();
        $page->translate();

        if (!$page->allow_guests and auth()->guest()) {
            return redirect('/')->withError(__('client.please_login_to_view'));
        }

        if (isset($page->redirect_url)) {
            return redirect($page->redirect_url);
        }

        if (!$page->is_enabled) {
            return redirect('/')->withError(__('client.page_not_published'));
        }

        $page->increment('views');

        return Theme::view('page', compact('page'));
    }

    public function contact()
    {
        return Theme::view('contact-us');
    }

    public function filterOrders($status)
    {
        return redirect()->back()->withCookie('filter_orders', $status);
    }

    public function suspended()
    {
        $user = auth()->user();
        $hasActiveBans = Punishment::hasActiveBans();
        $isSuspended = $user && $user->status === 'suspended';
        
        if (!$hasActiveBans && !$isSuspended) {
            return redirect()->route('dashboard');
        }

        $ban = Punishment::getActiveBan();
        
        // If user is suspended by status but no ban record exists, create a fake ban object
        if (!$ban && $isSuspended) {
            $ban = (object) [
                'id' => 'SUSPENDED_BY_SYSTEM',
                'expires_at' => null,
                'reason' => 'Account suspended by administrator',
                'type' => 'suspended'
            ];
        }

        return Theme::view('suspended', compact('ban'));
    }

    public function invites()
    {
        $invites = OrderMember::where('email', auth()->user()->email)->orWhere('user_id', auth()->user()->id);

        return Theme::view('dashboard.invites', compact('invites'));
    }

    public function acceptInvite(OrderMember $invite)
    {
        if (auth()->user()->email !== $invite->email) {
            return abort(404);
        }

        $invite->user_id = auth()->user()->id;
        $invite->status = 'active';
        $invite->save();

        return redirect()->back()->withSuccess('You have successfully accepted the invite');
    }

    public function rejectInvite(OrderMember $invite)
    {
        if (auth()->user()->email !== $invite->email) {
            return abort(404);
        }

        $invite->inviter->notify([
            'type' => 'warning',
            'icon' => "<i class='bx bx-user-plus' ></i>",
            'message' => "{$invite->email} rejected your invite to join order {$invite->order->name}",
        ]);

        $invite->delete();

        return redirect()->back()->withError('You have rejected the invite');
    }


}
