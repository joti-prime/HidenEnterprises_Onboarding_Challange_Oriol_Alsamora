<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Admin\Group;
use App\Models\Device;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\UserIp;
use App\Models\UserOauth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query();

        if (request()->get('sort') == 'random') {
            $users->inRandomOrder();
        }

        if (request()->get('sort') == 'latest' or request()->get('sort') == null) {
            $users->latest();
        }

        if (request()->get('sort') == 'oldest') {
            $users->oldest();
        }

        if (request()->get('sort') == 'online') {
            $users->where('last_seen_at', '>=', now()->subMinutes(5));
        }

        if (request()->get('sort') == 'subscribed') {
            $users->where('is_subscribed', true);
        }

        if (request()->get('sort') == 'balance') {
            $users->orderBy('balance', 'desc');
        }

        if (isset(request()->filter)) {
            foreach (request()->filter as $filter) {
                if (in_array($filter['operator'], ['LIKE', 'NOT LIKE'])) {
                    $filter['value'] = '%' . $filter['value'] . '%';
                }

                $users->where($filter['key'], $filter['operator'], $filter['value']);
            }
        }

        $users = $users->paginate(request()->get('per_page', 20));

        return Theme::view('users.index', compact('users'));
    }

    public function create()
    {
        return Theme::view('users.create', ['groups' => Group::query()->get()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'username' => 'required|unique:users,username|max:191|regex:/^[A-Za-z0-9_]+$/',
            'email' => 'required|email|unique:users,email',
            'groups' => 'nullable|array',
            'password' => 'required|min:4|max:255',
            'company_name' => 'max:255',
            'address' => 'max:255',
            'address_2' => 'max:255',
            'country' => 'max:255',
            'city' => 'max:255',
            'region' => 'max:255',
            'zip_code' => 'max:255',
        ]);
 
        $password = $request->input('password');
 
        $user = User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($password),
            'status' => 'active',
            'is_subscribed' => isset($request->is_subscribed) && $request->is_subscribed == 'on',
        ]);
 
        // generate the address
        $address = $user->address()->update([
            'company_name' => $request->input('company_name'),
            'address' => $request->input('address'),
            'address_2' => $request->input('address_2'),
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'region' => $request->input('region'),
            'zip_code' => $request->input('zip_code'),
        ]);
 
        // check if the user has permission to assign groups
        if(auth()->user()->hasPerm('groups.update')) {
            // assign the user to the groups
            $user->groups()->sync($request->input('groups'), false);
        }
 
        // verify email if isset
        if ($request->get('verify_email')) {
            $user->markEmailAsVerified();
        }
 
        // send email with password
        $user->email([
            'subject' => __('admin.account_created_email_subject', ['service' => settings('app_name')]),
            'content' => __('admin.account_created_email_content', ['service' => settings('app_name'), 'username' => $user->email, 'password' => $password]),
            'button' => [
                'name' => __('client.login'),
                'url' => route('login'),
            ],
        ]);
 
        return redirect()->route('users.edit', $user->id)->with('success', 'User has been created successfully');
 
    }

    public function edit(User $user)
    {
        $groups = Group::query()->get();

        return Theme::view('users.edit', compact('user', 'groups'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'username' => 'required|unique:users,username,' . $user->id . '|max:191|regex:/^[A-Za-z0-9_]+$/',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'groups' => 'nullable|array',
            'password' => 'nullable|min:4|max:255',
            'company_name' => 'max:255',
            'address' => 'max:255',
            'address_2' => 'max:255',
            'country' => 'max:255',
            'city' => 'max:255',
            'region' => 'max:255',
            'zip_code' => 'max:255',
        ]);
 
        // reset the password if it is set
        if ($request->input('password', false)) {
            $user->password = Hash::make($request->input('password'));
        }
 
        // check if the user has permission to assign groups
        if(auth()->user()->hasPerm('groups.update')) {
            // update the groups
            $user->groups()->sync($request->input('groups'), true);
        }
 
        // Store old email to check for changes
        $oldEmail = $user->email;
        
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->status = $request->input('status');
        $user->username = $request->input('username');
        $user->is_subscribed = isset($request->is_subscribed) && $request->is_subscribed == 'on';
        $user->save();
        
        // If email was changed, invalidate any existing password reset tokens
        if ($oldEmail !== $request->input('email')) {
            PasswordReset::whereEmail($oldEmail)->delete();
        }
 
        $address = $user->address;
        $address->company_name = $request->input('company_name');
        $address->address = $request->input('address');
        $address->address_2 = $request->input('address_2');
        $address->country = $request->input('country');
        $address->city = $request->input('city');
        $address->region = $request->input('region');
        $address->zip_code = $request->input('zip_code');
        $address->save();
 
        return redirect()->back()->with('success',
            trans('responses.user_update_success',
                ['default' => 'User updated successfully'])
        );
    }

    public function uploadProfilePicture(Request $request, User $user)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048', // Maximum file size is 2MB
        ]);

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

        return redirect()->back()->with('success',
            trans('responses.user_upload_picture_success',
                ['default' => 'Profile picture uploaded successfully'])
        );
    }

    public function resetProfilePicture(User $user)
    {
        // delete avatar if user already has one
        if ($user->avatar !== null) {
            Storage::delete('public/avatars/' . $user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return redirect()->back()->with('success',
            trans('responses.user_picture_reset',
                ['default' => 'Profile picture has been reset'])
        );
    }

    public function destroy(User $user)
    {
        if ($user->is_admin()) {
            return redirect()->back()->withError('This account has administrive privelages. The admin permissions must be removed before you can have your account deleted.');
        }

        if ($user->orders()->where('status', 'active')->exists()) {
            return redirect()->back()->withError(
                'This user has active orders. You must terminate the orders before deleting the user'
            );
        }

        if ($user->punishments()->exists()) {
            return redirect()->back()->withError(
                'This user has a recorded punishments. You must delete the punishments before deleting the user'
            );
        }

        $user->terminate();

        return redirect('/admin/users')->withSuccess('User has been deleted permanently.');
    }

    public function disable2fa(User $user)
    {
        $user->disable2FA();

        return redirect()->back()->with('success', '2FA has been disabled');
    }

    public function revokeDevice(User $user, Device $device)
    {
        $device->revoke();

        return redirect()->back()->with('success',
            trans('responses.user_device_updated',
                ['default' => 'Device has been updated'])
        );
    }

    public function destroyDevice(User $user, Device $device)
    {
        $device->delete();

        return redirect()->back()->with('success',
            trans('responses.user_device_deleted',
                ['default' => 'Device has been deleted'])
        );
    }

    public function unlinkOauth(User $user, UserOauth $oauth)
    {
        if ($oauth->user_id !== $user->id) {
            abort(404);
        }

        $driver = $oauth->driver;
        $oauth->delete();

        return redirect()->back()->with('success', ucfirst($driver) . ' has been unlinked from this user.');
    }

    public function orders(User $user)
    {
        return Theme::view('users.orders', compact('user'));
    }

    public function invoices(User $user)
    {
        return Theme::view('users.invoices', compact('user'));
    }

    public function emails(User $user)
    {
        return Theme::view('users.emails', compact('user'));
    }

    public function punishments(User $user)
    {
        $punishments = $user->punishments()->latest()->paginate(10);

        return Theme::view('users.punishments', compact('user', 'punishments'));
    }

    public function createPunishment(Request $request, User $user)
    {
        // ipban removed: Cloudflare fronts the dash, so request IPs are CF edge nodes,
        // not the real client; banning by IP would silently nuke many legitimate users.
        $request->validate([
            'type' => 'required|in:ban,warning',
            'reason' => 'nullable',
            'expires_at' => 'nullable',
        ]);

        if ($user->is_admin()) {
            return redirect()->back()->with('error', 'You cannot punish admin users');
        }

        $user->punish([
            'type' => $request->input('type'),
            'reason' => $request->input('reason'),
            'expiry_date' => $request->input('expires_at'),
        ]);

        if ($request->get('terminate_orders')) {
            $user->terminateAllOrders();
        } elseif ($request->get('suspend_orders')) {
            $user->suspendAllOrders();
        }

        return redirect()->back()->with('success', 'User has been punished');
    }

    public function activity(User $user)
    {
        $ips = UserIp::query()->where('user_id', $user->id)->orderBy('uses', 'desc');

        return Theme::view('users.activity', compact('user', 'ips'));
    }

    public function search()
    {
        $query = request()->query('query');

        return User::query()->where('username', 'LIKE', "%$query%")->orWhere('email', 'LIKE', "%$query%")->take(5)->get();
    }

    public function activate(User $user)
    {
        $user->status = 'active';
        $user->save();

        return redirect()->back()->with('success',
            trans('responses.user_activate_success',
                ['default' => 'User has been activated.'])
        );
    }

    public function verify(User $user)
    {
        $user->markEmailAsVerified();

        return redirect()->back()->with('success',
            trans('responses.user_verify_success',
                ['default' => 'Email has been verified'])
        );
    }

    public function updateBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|max:1',
        ]);

        $description = $request->input('description') ?? __('admin.balance_update_by', ['auth_user_username' => auth()->user()->username]);

        $user->balance($description, $request->input('type'), $request->input('amount'));

        return redirect()->back()->with('success',
            trans('responses.user_update_balance_success',
                ['default' => 'User balance was updated successfully'])
        );
    }

    public function emailPasswordReset(User $user)
    {
        $user->sendPasswordResetEmail();

        return redirect()->back()->with('success',
            trans('responses.user_email_password_reset',
                ['default' => 'Email with password reset link has been emailed'])
        );
    }

    public function impersonate(Request $request, User $user)
    {
        if ($user->is_admin()) {
            return redirect()->back()->withError('You cannot login as admin users.');
        }

        // Store the original admin user ID
        $request->session()->put('impersonate_admin', auth()->id());

        // Store the user to impersonate
        $request->session()->put('impersonate', $user->id);

        // Mark this as an impersonation session to prevent login events
        $request->session()->put('is_impersonating', true);

        // Login as the impersonated user without triggering login events
        auth()->loginUsingId($user->id, true);

        return redirect('/dashboard');
    }

    public function stopImpersonate(Request $request)
    {
        if (!$request->session()->has('impersonate')) {
            return abort('403');
        }

        $impersonatedUserId = session('impersonate');
        $originalAdminId = session('impersonate_admin');

        // Remove impersonation session data
        $request->session()->forget(['impersonate', 'impersonate_admin', 'is_impersonating']);

        // Login back as the original admin
        if ($originalAdminId) {
            auth()->loginUsingId($originalAdminId, true);
        }

        // Redirect to the user edit page
        return redirect()->route('users.edit', $impersonatedUserId);
    }
}
