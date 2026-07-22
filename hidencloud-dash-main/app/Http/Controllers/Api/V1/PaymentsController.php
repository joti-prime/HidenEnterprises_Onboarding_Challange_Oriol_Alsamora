<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gateways\Gateway;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentsController extends Controller
{
    /**
     * Define relationships that are allowed to be loaded.
     *
     * @var array
     */
    protected $relations = [
        'package',
        'price',
        'user',
        'order',
    ];

    /**
     * Define allowed filters.
     *
     * @var array
     */
    protected $filters = [
        'id',
        'package_id',
        'price_id',
        'user_id',
        'order_id',
        'status',
        'type',
        'currency',
        'amount',
        'transaction_id',
        'gateway',
        'data',
        'options',
    ];

    /**
     * Allowed sort columns and their directions.
     *
     * @var array
     */
    protected $sorts = [
        'description',
        'status',
        'type',
        'currency',
        'amount',
        'options',
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
     * Get all payments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payments(Request $request)
    {
        $payments = Payment::query();

        try {
            // add relations
            $this->applyRelations($payments);

            // apply filters
            $this->applyFilters($payments);

            // apply sorting
            $this->applySorts($payments);

            // apply date filters
            $this->applyDateFilters($payments);

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 422);
        }

        return response()->json(array_merge(
            ['status' => true],
            $payments->paginate($request->get('paginate', 15))->toArray()
        ), 200);
    }

    /**
     * Generate a new payment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'data' => 'sometimes|array',
            'notes' => 'sometimes|string',
            'show_as_invoice' => 'sometimes|boolean',
        ]);

        try {
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
            }

            $payment = Payment::generate([
                'user_id' => $request->get('user_id'),
                'description' => $request->get('description'),
                'amount' => $request->get('amount'),
                'data' => $request->get('data', []),
                'notes' => $request->get('notes', null),
                'show_as_unpaid_invoice' => $request->get('show_as_invoice', false),
            ]);

            $gateways = Gateway::where('status', 1)->where('type', 'once')->get();
            $links = [];

            foreach ($gateways as $gateway) {
                $links[] = [$gateway->name => route('invoice.pay', ['payment' => $payment->id, 'gateway' => $gateway->id])];
            }

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'errors' => [$error->getMessage()]], 500);
        }

        return response()->json(['success' => true, 'payment' => $payment->id, 'links' => $links], 200);
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
