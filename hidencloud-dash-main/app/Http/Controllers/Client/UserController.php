<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\UserDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function settings()
    {
        return Theme::view('user.settings');
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->id . '|min:4|max:191|regex:/^[A-Za-z0-9_]+$/',
            'company_name' => 'max:255',
            'address' => 'max:255',
            'address_2' => 'max:255',
            'country' => 'max:255',
            'city' => 'max:255',
            'region' => 'max:255',
            'zip_code' => 'max:255',
            'is_subscribed' => 'boolean',
        ]);

        $user->username = $request->input('username');
        $user->is_subscribed = $request->input('is_subscribed', false);
        $user->save();

        $address = $user->address;
        
        // Check if user has UserVerifiedOne group - if so, don't update address fields
        if (!$user->groups()->where('name', 'UserVerifiedOne')->exists()) {
            $address->company_name = $request->input('company_name');
            $address->address = $request->input('address');
            $address->address_2 = $request->input('address_2');
            //$address->country = $request->input('country');
            $address->region = $request->input('region');
            $address->zip_code = $request->input('zip_code');
            $address->save();
        } else {
            // Only update company_name for verified users
            $address->company_name = $request->input('company_name');
            $address->save();
        }

        return redirect()->back()->with('success',
            trans('responses.user_info_update_success',
                ['default' => 'Account Information has been updated'])
        );
    }

    public function updateAddress(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'phone_number' => 'sometimes|numeric|digits_between:8,15',
            'company_name' => 'max:255',
            'address' => 'required|max:255',
            'address_2' => 'max:255',
            'country' => 'required|max:255',
            'city' => 'required|max:255',
            'region' => 'required|max:255',
            'zip_code' => 'required|max:255',
        ]);

        $address = $user->address;
        $address->phone_number = $request->input('phone_number');
        $address->company_name = $request->input('company_name');
        $address->address = $request->input('address');
        $address->address_2 = $request->input('address_2');
        $address->country = $request->input('country');
        $address->city = $request->input('city');
        $address->region = $request->input('region');
        $address->zip_code = $request->input('zip_code');
        $address->save();

        return redirect()->back()->with('success',
            trans('responses.user_address_update_success',
                ['default' => 'Address has been updated successfully'])
        );
    }

    public function updateEmail(Request $request)
    {
        $user = User::query()->findOrFail(Auth::user()->id);
        $request->validate([
            'current_password' => 'required|max:255',
            'new_email' => 'required|email|unique:users,email,' . $user->id . '|max:255',
        ]);

        if (!Hash::check($request->input('current_password'), auth()->user()->password)) {
            return redirect()->back()->with('error',
                trans('responses.user_password_match_error',
                    ['default' => 'Current password does not match records'])
            );
        }

        $oldEmail = $user->email;
        
        $user->emailUpdatedNotification($request->input('new_email'));

        $user->email = $request->input('new_email');
        $user->save();
        
        // Invalidate any password reset tokens for the old email
        PasswordReset::whereEmail($oldEmail)->delete();

        return redirect()->back()->with('success',
            trans('responses.user_email_update_success',
                ['default' => 'Email has been updated'])
        );
    }

    public function updatePassword(Request $request)
    {
        $user = User::query()->findOrFail(Auth::user()->id);
        $request->validate([
            'current_password' => 'required|min:4|max:255',
            'new_password' => 'required|min:8|max:255|confirmed',
        ]);

        if (!Hash::check($request->input('current_password'), auth()->user()->password)) {
            return redirect()->back()->with('error',
                trans('responses.user_password_match_error',
                    ['default' => 'Current password does not match records'])
            );
        }

        $user->passwordUpdatedNotification();

        // IMPORTANT: First logout other devices with the CURRENT password
        // This must be done BEFORE changing the password
        if (config('session.driver') === 'database') {
            Auth::logoutOtherDevices($request->input('current_password'));
        }

        // Now update the password and remember token
        $user->password = Hash::make($request->input('new_password'));
        $user->remember_token = Str::random(60);
        $user->save();

        return redirect()->back()->with('success',
            trans('responses.user_password_update_success',
                ['default' => 'Password has been updated'])
        );
    }

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        // delete avatar if user already has one
        if ($user->avatar !== null) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = Str::random(32) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/avatars', $filename);
            $user->avatar = $filename;
            $user->save();
        }

        // Check if it's an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('responses.user_picture_update_success',
                    ['default' => 'Profile picture uploaded successfully']),
                'avatar_url' => $user->avatar()
            ]);
        }

        return redirect()->back()->with('success',
            trans('responses.user_picture_update_success',
                ['default' => 'Profile picture uploaded successfully'])
        );
    }

    public function deleteProfilePicture(Request $request)
    {
        $user = Auth::user();

        // Check if user has an avatar
        if ($user->avatar === null) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('responses.no_profile_picture_to_delete', 
                        ['default' => 'You don\'t have a profile picture to delete'])
                ]);
            }
            return redirect()->back()->with('error', 
                trans('responses.no_profile_picture_to_delete', 
                    ['default' => 'You don\'t have a profile picture to delete']));
        }

        // Delete the avatar file
        Storage::delete('public/avatars/' . $user->avatar);
        
        // Remove avatar from user record
        $user->avatar = null;
        $user->save();

        // Return appropriate response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('responses.profile_picture_deleted_success',
                    ['default' => 'Profile picture deleted successfully'])
            ]);
        }

        return redirect()->back()->with('success', 
            trans('responses.profile_picture_deleted_success',
                ['default' => 'Profile picture deleted successfully']));
    }

    public function revoke(Device $device)
    {
        if (Auth::user()->id !== $device->user_id and !Auth::user()->is_admin()) {
            return redirect()->back()->with('error',
                trans('responses.user_device_perms_error',
                    ['default' => 'You don\'t have permission to this resource.'])
            );
        }

        $device->revoke();

        return redirect()->back()->with('success',
            trans('responses.user_device_update_success',
                ['default' => 'Device has been updated'])
        );
    }

    public function visibility($status)
    {
        auth()->user()->setVisibility($status);

        return redirect()->back()->with('success',
            trans('responses.user_visibility_update_success',
                ['default' => 'Visibility has been updated'])
        );
    }

    public function downloadUserData(Request $request)
    {
        if (!settings('download_user_data', true)) {
            return redirect()->back()->withError('Downloading user data has been disabled by administrator');
        }

        $user = Auth::user(); // Get the currently authenticated user
        $request->validate([
            'current_password' => 'required',
            'OPT' => $user->TwoFa()->exists() ? 'required|numeric|digits:6' : 'nullable',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->with('error',
                trans('responses.user_password_match_error',
                    ['default' => 'Current password does not match records'])
            );
        }

        if ($user->TwoFa()->exists() and !$user->twoFA->validate($request->input('OPT'))) {
            return redirect()->back()->withError(trans('responses.otp_code_error_two_factor'));
        }

        // Get basic user data
        $userData = $user->toArray();

        // Get related orders and comments
        $userData['address'] = $user->address->toArray();
        // $userData['orders'] = $user->orders->skip('data')->toArray();
        // $userData['payments'] = $user->payments->where('status', 'paid')->toArray();
        $userData['balance_transactions'] = $user->balance_transactions->toArray();
        $userData['notifications'] = $user->notifications->toArray();
        $userData['emails'] = $user->emails->toArray();
        $userData['oauth'] = $user->oauth->toArray();
        $userData['ip_addresses'] = $user->ips->toArray();

        // Create a JSON string
        $userDataJson = json_encode($userData, JSON_PRETTY_PRINT);

        // Create a response with download headers
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="user-data.json"',
        ];

        return Response::make($userDataJson, 200, $headers);
    }

    public function deleteUserAccount(Request $request)
    {
        if (!settings('delete_user_account', true)) {
            return redirect()->back()->withError('Deleting user data has been disabled by administrator');
        }

        $user = Auth::user(); // Get the currently authenticated user
        $request->validate([
            'current_password' => 'required',
            'disclosure' => 'required',
            'OPT' => $user->TwoFa()->exists() ? 'required|numeric|digits:6' : 'nullable',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->with('error',
                trans('responses.user_password_match_error',
                    ['default' => 'Current password does not match records'])
            );
        }

        if ($user->TwoFa()->exists() and !$user->twoFA->validate($request->input('OPT'))) {
            return redirect()->back()->withError(trans('responses.otp_code_error_two_factor'));
        }

        if ($user->is_admin()) {
            return redirect()->back()->withError('Your account has administrive privelages. Your admin permissions must be removed before you can have your account deleted.');
        }

        if ($user->punishments()->exists()) {
            return redirect()->back()->withError(
                trans('responses.account_has_punishments')
            );
        }

        $request = new UserDelete;
        $request->user_id = $user->id;
        $request->username = $user->username;
        $request->delete_at = now()->addDays(3);
        $request->save();

        $user->deletionRequestNotification();

        return redirect()->back()->withSuccess(trans('responses.account_deleted_in_24_hours'));
    }

    public function CanceldeleteUserAccount()
    {
        if (!settings('delete_user_account', true)) {
            return redirect()->back()->withError('Deleting user data has been disabled by administrator');
        }

        $user = Auth::user();
        $user->deletion_requests()->first()->delete();

        return redirect()->back()->withSuccess(trans('responses.deletion_request_removed'));
    }
}
