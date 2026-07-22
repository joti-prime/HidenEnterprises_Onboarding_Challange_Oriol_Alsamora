<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UserController extends Controller
{
    /**
     * Define relationships that are allowed to be loaded.
     *
     * @var array
     */
    protected $relations = [
        'balance_transactions',
        'notifications',
        'punishments',
        'permissions',
        'affiliate',
        'suborders',
        'payments',
        'devices',
        'address',
        'groups',
        'orders',
        'emails',
        'oauth',
        'ips',
    ];

    /**
     * Define allowed filters.
     *
     * @var array
     */
    protected $filters = [
        'is_subscribed',
        'created_at',
        'first_name',
        'is_online',
        'last_name',
        'language',
        'username',
        'email',
        'email_verified_at',
    ];

    /**
     * Allowed sort columns and their directions.
     *
     * @var array
     */
    protected $sorts = [
        'first_name',
        'last_name',
        'username',
        'balance',
        'email',
        'created_at',
    ];

    /**
     * Sorting operators.
     *
     * @var array
     */
    protected $sort_operators = [
        'asc',
        'desc',
        'random',
    ];

    /**
     * Get all users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function users(Request $request)
    {
        $users = User::query();

        try {
            // add relations
            $this->applyRelations($users);

            // apply filters
            $this->applyFilters($users);

            // apply sorting
            $this->applySorts($users);

            // apply date filters
            $this->applyDateFilters($users);

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 422);
        }

        return response()->json(array_merge(
            ['status' => true],
            $users->paginate($request->get('paginate', 15))->toArray()
        ), 200);
    }

    /**
     * Create a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:2|max:255',
            'last_name' => 'required|min:2|max:255',
            'username' => 'required|string|unique:users,username|min:4|max:191|regex:/^[A-Za-z0-9_]+$/',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'sometimes|required|min:4|max:255',
            'language' => 'sometimes|required|max:2',
            'address.company_name' => 'sometimes|max:255',
            'address.street' => 'sometimes|required|max:255',
            'address.street2' => 'sometimes|required|max:255',
            'address.country' => 'sometimes|required|max:2',
            'address.city' => 'sometimes|required|max:255',
            'address.region' => 'sometimes|required|max:255',
            'address.zip_code' => 'sometimes|required|max:255',
            'welcome_email' => 'sometimes|boolean',
            'mark_email_verified' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        try {
            // define variables
            $password = $request->input('password') ?? str_random(12);
            $address = $request->input('address', []);

            // create user
            $user = new User();
            $user->username = $request->input('username');
            $user->password = Hash::make($password);
            $user->email = $request->input('email');
            $user->language = $request->input('language', settings('default_language', 'en'));
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->status = (settings('registration_activation', '1') == '3') ? 'pending' : 'active';
            $user->last_login_at = Carbon::now();
            $user->save();

            // generate the address
            $address = $user->address()->update([
                'company_name' => $request->input('company_name'),
                'address' => $request->input('street'),
                'address_2' => $request->input('street_2'),
                'country' => $request->input('country'),
                'city' => $request->input('city'),
                'region' => $request->input('region'),
                'zip_code' => $request->input('zip_code'),
            ]);

            // mark email as verified
            if ($request->input('mark_email_verified', false)) {
                $user->markEmailAsVerified();
            }

            // send email
            if ($request->input('welcome_email', true)) {
                $user->email([
                    'subject' => 'Welcome to ' . settings('app_name'),
                    'content' => "An account was created for you on our platform. Your username is {$user->username}. If you did not already specify a custom password, you will receive a randomly generated password in the next email.",
                    'button' => [
                        'name' => __('client.login'),
                        'url' => route('login'),
                    ],
                ]);
            }

            // send password email
            if (!$request->input('password')) {
                $user->email([
                    'subject' => 'Your login details for ' . settings('app_name'),
                    'content' => "We have randomly generated a password for your account. You can find the username and password below. <br><br> Username: {$user->username} <br> Password: {$password} <br><br> Please login and change your password in your account settings.",
                    'button' => [
                        'name' => __('client.login'),
                        'url' => route('login'),
                    ],
                ]);
            }

            $user->address;
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['success' => true, 'data' => $user], 200);
    }

    /**
     * Update a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|min:2|max:255',
            'last_name' => 'sometimes|required|min:2|max:255',
            'username' => 'sometimes|required|unique:users,username,' . $user->id . '|min:4|max:191|regex:/^[A-Za-z0-9_]+$/',
            'email' => 'sometimes|required|email|unique:users,email|max:255',
            'password' => 'sometimes|required|min:4|max:255',
            'status' => 'sometimes|required|in:active,suspended,pending',
            'language' => 'sometimes|required|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        try {
            $user->first_name = $request->input('first_name', $user->first_name);
            $user->last_name = $request->input('last_name', $user->last_name);
            $user->username = $request->input('username', $user->username);

            if ($request->input('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            $user->email = $request->input('email', $user->email);
            $user->language = $request->input('language', $user->language);
            $user->status = $request->input('status', $user->status);
            $user->save();

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['success' => true, 'data' => $user], 200);
    }

    /**
     * Delete a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        // check if the user is administrator
        if ($user->isAdmin()) {
            return response()->json(['success' => false, 'errors' => ['You cannot delete an administrator']], 422);
        }

        if (!$request->get('force', false)) {
            // check if user has any non-terminated orders
            if ($user->orders()->where('status', '!=', 'terminated')->exists()) {
                return response()->json(['success' => false, 'errors' => ['All orders belonging to the user must first be terminated.']], 422);
            }

            // check if user has any recorded punishments
            if ($user->punishments()->exists()) {
                return response()->json(['success' => false, 'errors' => ['User has punishments in their record, pushments must first be removed.']], 422);

            }
        }

        try {
            $user->terminate();
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['success' => true, 'message' => 'User deleted successfully'], 200);
    }

    /**
     * Get a single user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = User::where('id', $id);

        if (!$user->exists()) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        try {
            // add relations
            $this->applyRelations($user);

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 422);
        }

        $user = $user->first();

        return response()->json([
            'status' => true,
            'data' => $user->toArray(),
        ], 200);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authUser()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User is not authenticated']], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'language' => $user->language,
                'avatar' => $user->avatar(),
                'status' => $user->status,
                'is_subscribed' => (bool) $user->is_subscribed,
                'is_email_verified' => $user->hasVerifiedEmail(),
                'last_seen_at' => $user->last_seen_at,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
            ],
        ], 200);
    }

    /**
     * Check if a username is available.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function UsernameAvailable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username|min:4|max:191|regex:/^[A-Za-z0-9_]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Username is available'], 200);
    }

    /**
     * Get a single user's orders.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function orders(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        return response()->json(array_merge(
            ['status' => true],
            $user->orders()->paginate($request->get('paginate', 15))->toArray()
        ), 200);
    }

    /**
     * Get a single user's payments.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function payments(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        return response()->json(array_merge(
            ['status' => true],
            $user->payments()->paginate($request->get('paginate', 15))->toArray()
        ), 200);
    }

    /**
     * Update a user's balance.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBalance(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:add,remove,set',
            'amount' => 'required|numeric',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $type = '=';
        if ($request->type == 'add') {
            $type = '+';
        } elseif ($request->type == 'remove') {
            $type = '-';
        }

        $user->balance(
            type: $type,
            amount: $request->amount,
            description: $request->description
        );

        return response()->json(['success' => true, 'message' => 'Balance updated successfully'], 200);
    }

    /**
     * Send an email to a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $user->email([
            'subject' => $request->subject,
            'content' => $request->content,
        ]);

        return response()->json(['success' => true, 'message' => 'Email sent successfully'], 200);
    }

    /**
     * Send a notification to a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['User does not exists']], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:success,warning,danger',
            'icon' => 'required|string',
            'message' => 'required|string|max:255',
            'url' => 'sometimes|required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        if (!Str::startsWith($request->icon, ['<i', '<svg'])) {
            return response()->json(['success' => false, 'errors' => "Icon must be from https://boxicons.com and of format <i class='bx bxs-bell-ring' ></i>"], 422);
        }

        $user->notify([
            'type' => $request->type,
            'icon' => $request->icon,
            'message' => $request->message,
            'button_url' => $request->url,
        ]);

        return response()->json(['success' => true, 'message' => 'Notification sent successfully'], 200);
    }

    /**
     * Apply relationships to the query.
     *
     * @param  array  $sorts
     *
     * @throws InvalidArgumentException
     */
    protected function applyRelations(Builder $query): void
    {
        $includes = request()->get('include', '');
        if (!$includes) {
            return;
        }

        // turn includes into an array & remove any spaces
        $includes = explode(',', $includes);
        $includes = array_map('trim', $includes);

        foreach ($includes as $relation) {
            if (!in_array($relation, $this->relations)) {
                throw new InvalidArgumentException("Relation $relation is not allowed");
            }

            $query->with($relation);
        }
    }

    /**
     * Apply filters to the query.
     *
     * @param  array  $filters
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function applyFilters(Builder $query)
    {
        $filters = request()->get('filter', []);

        // check if value is an array
        if (!is_array($filters)) {
            throw new InvalidArgumentException('Filter must be in format ?filter[key]=value');
        }

        // check if filters are empty
        if (empty($filters)) {
            return;
        }

        foreach ($filters as $key => $value) {
            if (!in_array($key, $this->filters)) {
                throw new InvalidArgumentException("Filter $key is not allowed");
            }

            // Custom filter for online status
            if ($key === 'is_online') {
                $query->where('last_seen_at', $value ? '>=' : '<=', now()->subMinutes(5));

                continue;
            }

            $query->where($key, $value);
        }
    }

    /**
     * Apply sorting to the query.
     *
     * @param  array  $sorts
     *
     * @throws InvalidArgumentException
     */
    protected function applySorts(Builder $query): void
    {
        $sorts = request()->get('sort', []);

        // check if value is an array
        if (!is_array($sorts)) {
            throw new InvalidArgumentException('Sorting filters must be of format ?sort[key]=value');
        }

        // check if sorts are empty
        if (empty($sorts)) {
            return;
        }

        foreach ($sorts as $key => $operator) {
            if (!in_array($key, $this->sorts)) {
                throw new InvalidArgumentException("Sorting by $key is not allowed");
            }

            if ($operator === 'random') {
                $query->inRandomOrder();
            } else {
                $query->orderBy($key, $operator);
            }
        }
    }

    /**
     * Apply date filters to the query.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function applyDateFilters(Builder $query)
    {
        $dateFilter = request()->get('date');

        if (!$dateFilter) {
            return;
        }

        switch ($dateFilter) {
            case 'today':
                $query->whereDate('created_at', '=', Carbon::today()->toDateString());
                break;
            case 'yesterday':
                $query->whereDate('created_at', '=', Carbon::yesterday()->toDateString());
                break;
            case '3days':
                $query->whereDate('created_at', '>=', Carbon::now()->subDays(3)->toDateString());
                break;
            case '7days':
                $query->whereDate('created_at', '>=', Carbon::now()->subDays(7)->toDateString());
                break;
            case '14days':
                $query->whereDate('created_at', '>=', Carbon::now()->subDays(14)->toDateString());
                break;
            case '30days':
                $query->whereDate('created_at', '>=', Carbon::now()->subDays(30)->toDateString());
                break;
            case '90days':
                $query->whereDate('created_at', '>=', Carbon::now()->subDays(90)->toDateString());
                break;
            default:
                $dates = explode(',', $dateFilter);
                if (count($dates) === 2) {
                    try {
                        $startDate = Carbon::parse($dates[0]);
                        $endDate = Carbon::parse($dates[1]);

                        // Ensure that the start date is not after the end date
                        if ($startDate->gt($endDate)) {
                            throw new InvalidArgumentException('Start date must be before end date.');
                        }

                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    } catch (\Exception $e) {
                        throw new InvalidArgumentException("Invalid date format. Dates should be in 'YYYY-MM-DD' format.");
                    }
                } else {
                    throw new InvalidArgumentException("Custom date range must be in format 'YYYY-MM-DD,YYYY-MM-DD'");
                }
                break;
        }
    }
}
