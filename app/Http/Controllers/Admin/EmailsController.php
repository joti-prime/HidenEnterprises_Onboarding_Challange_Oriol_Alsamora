<?php

namespace App\Http\Controllers\Admin;

use App\Entities\MassiveEmail;
use App\Entities\ResourceApiClient;
use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\EmailHistory;
use App\Models\EmailMessage;
use App\Models\MassMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Facades\EmailTemplate;

class EmailsController extends Controller
{
    public function configure()
    {
        return Theme::view('emails.configure');
    }

    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'subject' => 'required',
            'body' => 'required',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            $user->email([
                'subject' => $request->input('subject'),
                'content' => $request->input('body'),
            ]);

            return redirect()->back()->withSuccess('Email has been sent to '. $user->username);
        }

        // email the contact submission to administrator
        $email = EmailHistory::query()->create([
            'user_id' => null,
            'sender' => config('mail.from.address'),
            'receiver' => $request->input('email'),
            'subject' => $request->input('subject'),
            'content' => $request->input('body'),
            'button' => null,
            'attachment' => null,
        ]);

        return redirect()->back()->withSuccess('Email has been sent');
    }

    public function testMail()
    {
        Auth::user()->email([
            'subject' => 'Test email',
            'content' => 'This is a test email sent from admin side',
        ]);

        Artisan::call('cron:emails:send');

        return redirect()->back()->with('success',
            trans('responses.test_email_success', ['default' => 'Test email was sent successfully.'])
        );
    }

    public function messages()
    {
        $lang = request()->input('lang', 'en');
        $defaultMessages = EmailMessage::getAllMessages();
        $messages = EmailMessage::where('language', $lang)->get()->pluck('content', 'key');
        $messages = array_merge($defaultMessages, $messages->toArray());

        return Theme::view('emails.messages', compact('messages', 'lang'));
    }

    public function updateMessages(Request $request)
    {
        $messages = $request->input('messages');
        $lang = $request->input('lang', 'en');

        foreach ($messages as $key => $content) {
            $message = EmailMessage::where('key', $key)->where('language', $lang)->first();
            if ($message) {
                $message->update(['content' => $content]);
            } else {
                EmailMessage::create([
                    'key' => $key,
                    'language' => $lang,
                    'content' => $content,
                ]);
            }
        }

        return redirect()->back()->with('success', __('admin.success'));
    }

    public function history()
    {
        return Theme::view('emails.history');
    }

    public function resend(EmailHistory $email)
    {
        $email->resend();

        return redirect()->back()->with('success',
            trans('responses.resent_email_success', ['default' => 'Email was resent successfully.']));
    }

    public function destroy(EmailHistory $email)
    {
        $email->delete();

        return redirect()->back()->with('success',
            trans('admin.email_deleted_success', ['default' => 'Email was deleted successfully.']));
    }

    // return login page view
    public function templates()
    {
        $api = new ResourceApiClient;
        $marketplace = $api->getAllResources('Templates', 'email');
        if (array_key_exists('error', $marketplace)) {
            $marketplace = [];
        }

        return Theme::view('emails.templates', compact('marketplace'));
    }

    public function preview(Request $request)
    {
        $data = [
            'subject' => $request->input('subject', 'Lorem ipsum dolor reiciendis.'),
            'name' => $request->input('name', '<username>'),
            'intro' => $request->input('content', 'Lorem ipsum dolor sit amet. Aut dolor quam ut perspiciatis quia et velit voluptatem! Ea odio delectus et enim officiis non aperiam porro in pariatur quam non sint voluptas ut perspiciatis aliquid.'),
        ];

        if($request->input('button.name')) {
            $data = array_merge($data, [
                'button' => [
                    'name' => $request->input('button.name', null),
                    'url' => $request->input('button.url', null),
                ]
            ]);
        }

        return view(EmailTemplate::view(), $data);
    }

    public function massMailer()
    {
        $massMail = MassMail::query()->latest()->paginate(15);

        return Theme::view('emails.mass-mailer', compact('massMail'));
    }

    public function createMassMail()
    {
        return Theme::view('emails.mass-mailer-create');
    }

    public function storeMassMail(Request $request)
    {
        $validated = $request->validate([
            'audience' => 'required',
            'subject' => 'required',
            'content' => 'required',
            'button_name' => 'nullable|string',
            'button_url' => 'nullable|url',
            'repeat' => 'nullable|integer|min:1',
            'scheduled_at' => 'nullable|date',
            'custom_selection' => 'required_if:audience,custom_selection|array',
        ]);

        $massMail = MassMail::query()->create([
            'audience' => $request->input('audience'),
            'subject' => $request->input('subject'),
            'content' => $request->input('content'),
            'button_text' => $request->input('button_name'),
            'button_url' => $request->input('button_url'),
            'custom_selection' => $request->input('custom_selection', []),
            'attachment' => $request->input('attachment'),
            'email_theme' => $request->input('email_theme', 'default'),
            'status' => $request->input('scheduled_at') ? 'scheduled' : 'pending',
            'repeat' => $request->input('repeat'),
            'scheduled_at' => $request->input('scheduled_at'),
            'total_count' => User::query()->count(),
        ]);

        return redirect()->route('emails.mass-mailer')->withSuccess(__('admin.emails_sent_success'));
    }

    public function editMassMail(MassMail $massMail)
    {
        $email = $massMail;
        return Theme::view('emails.mass-mailer-edit', compact('email'));
    }

    public function updateMassMail(Request $request, MassMail $massMail)
    {
        $validated = $request->validate([
            'audience' => 'required',
            'subject' => 'required',
            'content' => 'required',
            'button_name' => 'nullable|string',
            'button_url' => 'nullable|url',
            'repeat' => 'nullable|integer|min:1',
            'scheduled_at' => 'nullable|date',
            'custom_selection' => 'required_if:audience,custom_selection|array',
        ]);

        $massMail->update([
            'audience' => $request->input('audience'),
            'subject' => $request->input('subject'),
            'content' => $request->input('content'),
            'button_text' => $request->input('button_name'),
            'button_url' => $request->input('button_url'),
            'custom_selection' => $request->input('custom_selection', []),
            'attachment' => $request->input('attachment'),
            'email_theme' => $request->input('email_theme', 'default'),
            'repeat' => $request->input('repeat'),
            'scheduled_at' => $request->input('scheduled_at'),
            'total_count' => User::query()->count(),
        ]);

        return redirect()->back()->withSuccess(__('admin.emails_sent_success'));
    }

    public function destroyMassMail(MassMail $massMail)
    {
        $massMail->delete();

        return redirect()->back()->withSuccess(__('admin.email_deleted_success'));
    }
}
