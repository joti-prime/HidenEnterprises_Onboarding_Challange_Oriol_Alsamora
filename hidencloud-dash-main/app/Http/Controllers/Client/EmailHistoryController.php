<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\EmailHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmailHistoryController extends Controller
{
    public function index()
    {
        $emails = EmailHistory::query()->where('user_id', Auth::user()->id)->latest()->paginate(10);

        return Theme::view('email-history', ['emails' => $emails]);
    }

    public function download(EmailHistory $email)
    {
        if ($email->user_id !== auth()->user()->id) {
            return redirect()->back()->withError(__('client.no_access_email_download'));
        }

        $file = $email['attachment'][request()->get('attachment_id')];

        if (!Storage::exists($file['path'])) {
            abort(404); // File not found
        }

        $headers = [
            'Content-Disposition' => 'attachment; filename=' . basename($file['name']),
        ];

        return Storage::download($file['path'], $file['name'], $headers);
    }
}
