<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PackagePrice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderController extends Controller
{
    /**
     * Define relationships that are allowed to be loaded.
     *
     * @var array
     */
    protected $relations = [
        'package',
        'members',
        'payments',
        'user',
    ];

    /**
     * Define allowed filters.
     *
     * @var array
     */
    protected $filters = [
        'id',
        'external_id',
        'user_id',
        'package_id',
        'status',
        'service',
        'domain',
        'data',
        'options',
        'due_date',
    ];

    /**
     * Allowed sort columns and their directions.
     *
     * @var array
     */
    protected $sorts = [
        'id',
        'name',
        'package_id',
        'status',
        'service',
        'domain',
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
     * Get all orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function orders(Request $request)
    {
        $orders = Order::query();

        try {
            // add relations
            $this->applyRelations($orders);

            // apply filters
            $this->applyFilters($orders);

            // apply sorting
            $this->applySorts($orders);

            // apply date filters
            $this->applyDateFilters($orders);

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 422);
        }

        return response()->json(array_merge(
            ['status' => true],
            $orders->paginate($request->get('paginate', 15))->toArray()
        ), 200);
    }

    /**
     * Get a single order.
     *
     * @param  Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id);

        if (!$order->exists()) {
            return response()->json(['success' => false, 'errors' => ['Order does not exists']], 404);
        }

        try {
            // add relations
            $this->applyRelations($order);

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 422);
        }

        $order = $order->first();

        return response()->json(['status' => true, 'data' => $order->toArray()], 200);
    }

    /**
     * Update a order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'errors' => ['Order does not exists']], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'price_id' => 'sometimes|required|integer|exists:package_prices,id',
            'data' => 'sometimes|required|json',
            'options' => 'sometimes|required|json',
            'due_date' => 'sometimes|required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        try {
            // turn data json into array
            if ($request->has('data')) {
                $request->merge(['data' => json_decode($request->get('data'), true)]);
            }

            // turn options json into array
            if ($request->has('options')) {
                $request->merge(['options' => json_decode($request->get('options'), true)]);
            }

            // check if due date is set
            if ($request->has('due_date')) {
                $request->merge(['due_date' => Carbon::parse($request->get('due_date'))->toDateTimeString()]);
                $order->last_renewed_at = Carbon::now()->toDateTimeString();
                $order->save();
            }

            // update order
            $order->update($request->all());

            // update price
            $price = PackagePrice::where('id', $request->get('price_id'))->first();
            if ($request->has('price_id')) {
                $order->price = $price->toArray();
                $order->save();
            }
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['success' => true, 'data' => $order], 200);
    }

    /**
     * Suspend an order.
     *
     * @param  Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function suspend(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'errors' => ['Order does not exists']], 404);
        }

        try {
            $order->suspend();
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['status' => true, 'data' => $order->toArray()], 200);
    }

    /**
     * Unuspend an order.
     *
     * @param  Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsuspend(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'errors' => ['Order does not exists']], 404);
        }

        try {
            $order->unsuspend();
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['status' => true, 'data' => $order->toArray()], 200);
    }

    /**
     * Terminate an order.
     *
     * @param  Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function terminate(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'errors' => ['Order does not exists']], 404);
        }

        try {
            $order->terminate();
        } catch (\Exception $error) {
            if ($request->get('force', false)) {
                $order->forceTerminate();
            } else {
                return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
            }
        }

        return response()->json(['status' => true, 'data' => $order->toArray()], 200);
    }

    /**
     * Cancel an order.
     *
     * @param  Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'errors' => ['Order does not exists']], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:immediately,end_of_term',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        try {
            $order->cancel($request->get('type'), $request->get('reason'));
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['status' => true, 'data' => $order->toArray()], 200);
    }

    /**
     * Delete an order.
     *
     * @param  Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'errors' => ['Order does not exists']], 404);
        }

        // check if force delete is enabled
        if (!$request->get('force', false)) {
            // check if order is in terminated state
            if ($order->status !== 'terminated') {
                return response()->json(['success' => false, 'errors' => ['Order must be in terminated state in order to delete it']], 422);
            }
        } else {
            try {
                // attempt to terminate order before deleting
                if ($order->status !== 'terminated') {
                    $order->terminate();
                }
            } catch (\Exception $error) {
                ErrorLog('api::terminate::delete', $error->getMessage());
            }
        }

        try {
            $order->delete();
        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['status' => true, 'message' => 'Order deleted successfully'], 200);
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
                if (!Str::contains($key, '->')) {
                    throw new InvalidArgumentException("Filter $key is not allowed");
                }
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
