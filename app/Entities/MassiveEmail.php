<?php

namespace App\Entities;

use App\Models\User;
use Illuminate\Http\Request;

class MassiveEmail
{
    public function massiveSend(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required',
            'body' => 'required',
            'users' => 'required',
            'button' => 'nullable|array',
        ]);

        $type = $request->input('users');
        if (str_contains($type, 'service_')) {
            $this->sendToService($request, str_replace('service_', '', $type));
        } elseif ($type == 'all_users') {
            $this->sendToAll($request);
        } elseif ($type == 'active_orders') {
            $this->sendToActiveOrdersUsers($request);
        } elseif ($type == 'no_orders') {
            $this->sendToUsersWithoutOrders($request);
        } elseif ($type == 'subscribed') {
            $this->sendToSubscribedUsers($request);
        }

        return redirect()->back()->withSuccess(__('admin.emails_sent_success'));
    }

    private function send(User $user, $subject, $content, $button = null): void
    {
        $email = [
            'subject' => $subject,
            'content' => $content,
        ];
        if ($button && isset($button['name'], $button['url']) && !empty($button['name']) && !empty($button['url'])) {
            $email['button'] = [
                'name' => $button['name'],
                'url' => $button['url'],
            ];
        }
        $user->email($email);
    }

    //    Options for sending emails to users
    private function sendToAll(Request $request): void
    {
        $users = User::all();
        foreach ($users as $user) {
            $this->send($user, $request->input('subject'), $request->input('body'), $request->input('button', null));
        }
    }

    private function sendToActiveOrdersUsers(Request $request): void
    {
        $users = User::whereHas('orders', function ($query) {
            $query->where('status', 'active');
        })->get();

        foreach ($users as $user) {
            $this->send($user, $request->input('subject'), $request->input('body'), $request->input('button', null));
        }
    }

    private function sendToUsersWithoutOrders(Request $request): void
    {
        $users = User::whereDoesntHave('orders', function ($query) {
            $query->where('status', 'active');
        })->get();

        foreach ($users as $user) {
            $this->send($user, $request->input('subject'), $request->input('body'), $request->input('button', null));
        }
    }

    private function sendToSubscribedUsers(Request $request): void
    {
        $users = User::where('is_subscribed', true)->get();
        foreach ($users as $user) {
            $this->send($user, $request->input('subject'), $request->input('body'), $request->input('button', null));
        }
    }

    private function sendToService(Request $request, $service): void
    {
        $users = User::whereHas('orders', function ($query) use ($service){
            $query->where('service', $service);
        })->get();
        foreach ($users as $user) {
            $this->send($user, $request->input('subject'), $request->input('body'), $request->input('button', null));
        }
    }
}
