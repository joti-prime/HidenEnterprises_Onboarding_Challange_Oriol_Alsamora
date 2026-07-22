<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Facades\Theme;
use App\Facades\Captcha;
use App\Models\Settings;
use App\Models\EmailHistory;

class ContactUsController extends Controller
{
    public function __construct()
    {
        Captcha::setConfig();
    }

    public function contact()
    {
        if(!settings('contact_us_enabled', true)) {
            return redirect('/')->withError('This page has been disabled');
        }

        return Theme::view('contact-us');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|min:3|max:100',
            'message' => 'required|string|min:3|max:1000',
            'cf-turnstile-response' => Captcha::CloudFlareRules('page_contact_us'),
        ]);

        if(session('contact_submission')) {
            return redirect()->back()->withError('You already submitted a contact form. Please wait before you submit again');
        }

        if(!settings('contact_us_enabled', true)) {
            return redirect('/')->withError('This page has been disabled');
        }

        if(!settings('contact_email', false)) {
            return redirect()->back()->withError('The administrator has not defined a contact email in admin configuration');
        }

        // email the contact submission to administrator
        EmailHistory::query()->create([
            'user_id' => null,
            'sender' => config('mail.from.address'),
            'receiver' => settings('contact_email'),
            'subject' => "Contact-Us {$request->input('email')}: {$request->input('subject')}",
            'content' => $request->input('message'),
            'button' => null,
            'attachment' => NULL,
        ]);

        // email the contact submission to administrator
        EmailHistory::query()->create([
            'user_id' => null,
            'sender' => config('mail.from.address'),
            'receiver' => $request->input('email'),
            'subject' => __('client.contact_us_success_subject'),
            'content' => __('client.contact_us_success_content', ['subject' => $request->input('subject')]),
            'button' => null,
            'attachment' => NULL,
        ]);

        Session::put('contact_submission', $request->input('subject'), 120);

        return redirect()->back();
    }
}
