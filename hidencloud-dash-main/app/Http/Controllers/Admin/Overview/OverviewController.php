<?php

namespace App\Http\Controllers\Admin\Overview;

use App\Entities\ZipManager;
use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Forms\Entities\Submission;
use Modules\Forms\Entities\Form;

class OverviewController extends Controller
{
    public function index()
    {
        // get data for paid payments
        $incomeSubscriptions = $this->incomeSubscriptionsDays(request()->input('period', 30));
        $period = request()->input('period', 30);
        $paid_payments = $this->getPaidPayments($period);
        $paid_subscriptions = $this->getPaidPaymentsSubscriptions($period);
        $unpaid_payments = $this->getUnpaidPaidPayments($period);
        $registrations = $this->getNewRegistrations($period);

        [$paid_dates_sub, $paid_amounts_sub] = $this->prepareDataForView($paid_subscriptions);
        [$paid_dates, $paid_amounts] = $this->prepareDataForView($paid_payments);
        [$unpaid_dates, $unpaid_amounts] = $this->prepareDataForView($unpaid_payments);

        $registration_dates = [0, 0];
        $registration_counts = [0, 0];
        foreach ($registrations as $registration) {
            $registration_dates[] = $registration->date;
            $registration_counts[] = $registration->count;
        }

        // Get active form submissions for the dashboard card. rejected_pending
        // = user replied after a rejection and the appeal is waiting for the
        // next review round, so it needs visibility here. rejected_1 and
        // rejected_2 are intentionally excluded: those are "waiting for the
        // user to respond" and don't need an admin to act, so showing them on
        // the dashboard would just be noise.
        $activeStatuses = ['open', 'pending', 'in_progress', 'under_review', 'on_hold', 'rejected_pending'];
        $activeSubmissions = [];
        $groupedSubmissions = [];
        $forms = [];
        
        // Check if Forms module exists
        if(class_exists('Modules\Forms\Entities\Submission')) {
            $activeSubmissions = Submission::query()
                ->with('form', 'user')
                ->whereIn('status', $activeStatuses)
                ->orderBy('updated_at', 'desc')
                ->get();
            
            // Group by form
            $groupedSubmissions = $activeSubmissions->groupBy('form_id');
            
            // Get forms
            $forms = Form::whereIn('id', $activeSubmissions->pluck('form_id')->unique())->get()->keyBy('id');
        }

        // Get segmented order statistics
        $orderStatistics = $this->getSegmentedOrderStatistics();

        return Theme::view('overview.index',
            compact('paid_dates_sub', 'paid_amounts_sub', 'incomeSubscriptions', 'paid_dates', 'paid_amounts', 
                    'unpaid_amounts', 'unpaid_dates', 'registration_dates', 'registration_counts',
                    'activeSubmissions', 'groupedSubmissions', 'forms', 'orderStatistics'));
    }

    private function prepareDataForView($payments)
    {
        $dates = [0, 0];
        $amounts = [0, 0];

        foreach ($payments as $payment) {
            $dates[] = $payment->date;
            $amounts[] = $payment->sum;
        }

        return [$dates, $amounts];
    }

    private function incomeSubscriptionsDays($days): float
    {
        $totalIncomePerDays = 0;

        $payments = Payment::query()
            ->whereType('subscription')
            ->whereStatus('paid')
            ->whereHas('order', function ($query) {
                $query->whereStatus('active');
            })
            ->with('price')
            ->get();

        foreach ($payments as $payment) {
            $dailyIncome = $payment->price->renewal_price / $payment->price->period;
            $totalIncomePerDays += $dailyIncome * $days;
        }

        return round($totalIncomePerDays, 2);
    }

    private function getPaidPaymentsSubscriptions($days)
    {
        $startDate = now()->subDays($days)->format('Y-m-d');

        return Payment::query()
            ->whereDate('created_at', '>', $startDate)
            ->whereType('subscription')
            ->whereStatus('paid')
            ->selectRaw('sum(amount) as sum, DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getPaidPayments($days)
    {
        $startDate = now()->subDays($days)->format('Y-m-d');

        return Payment::query()
            ->whereDate('created_at', '>', $startDate)
            ->where('type', '!=', 'subscription')
            ->whereStatus('paid')
            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(gateway, "$.driver")) != ?', ['Balance'])
            ->selectRaw('sum(amount) as sum, DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getUnpaidPaidPayments($days)
    {
        $startDate = now()->subDays($days)->format('Y-m-d');

        return Payment::query()
            ->whereDate('created_at', '>', $startDate)
            ->where('type', '!=', 'subscription')
            ->whereStatus('unpaid')
            ->selectRaw('sum(amount) as sum, DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getNewRegistrations($days)
    {
        $startDate = now()->subDays($days)->format('Y-m-d');

        return User::query()
            ->whereDate('created_at', '>', $startDate)
            ->selectRaw('count(*) as count, DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * @throws \Exception
     */
    public function resourceInstall($resource_id, $version_id)
    {
        $zip = new ZipManager();
        $resp = $zip->resourceDownloadAndExtract($resource_id, $version_id);
        if ($resp['status']) {
            return redirect()->back()->with('success', __('admin.resource_installed_success'));
        }

        return redirect()->back()->with('error', $resp['error']);
    }

    public function modeToggle()
    {
        $currentMode = Cache::get('admin_theme_mode_'.auth()->user()->id, 'light');
        $newMode = $currentMode === 'light' ? 'dark' : 'light';
        Cache::put('admin_theme_mode_'.auth()->user()->id, $newMode);

        return redirect()->back();
    }

    public function changeOrder($id, $model, $direction = 'up')
    {
        if (!in_array($direction, ['up', 'down'])) {
            return redirect()->back()->withErrors([__('admin.order_changed_error')]);
        }
        $item = match ($model) {
            'categories' => Categories::find($id),
            'packages' => Package::find($id),
            default => Categories::find($id),
        };
        $item->changeOrder($direction);

        return redirect()->back()->withSuccess(__('admin.order_changed'));
    }

    private function getSegmentedOrderStatistics()
    {
        $statuses = ['active', 'suspended', 'cancelled', 'terminated'];
        $services = ['universal', 'hetzner', 'pterodactyl', 'freepterodactyl'];
        
        $statistics = [];
        
        // Get total counts by status
        $totalsByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Fill missing statuses with 0
        foreach ($statuses as $status) {
            if (!isset($totalsByStatus[$status])) {
                $totalsByStatus[$status] = 0;
            }
        }
        
        // Get counts by status and service
        $byStatusAndService = Order::select('status', 'service', DB::raw('count(*) as count'))
            ->whereIn('status', $statuses)
            ->whereNotNull('service')
            ->groupBy('status', 'service')
            ->get();
        
        // Organize data in a structured format
        foreach ($statuses as $status) {
            $statistics[$status] = [
                'total' => $totalsByStatus[$status],
                'services' => []
            ];
            
            // Initialize all services with 0
            foreach ($services as $service) {
                $statistics[$status]['services'][$service] = 0;
            }
            
            // Add other services category
            $statistics[$status]['services']['other'] = 0;
        }
        
        // Populate service counts
        foreach ($byStatusAndService as $item) {
            $service = strtolower($item->service);
            if (in_array($service, $services)) {
                $statistics[$item->status]['services'][$service] = $item->count;
            } else {
                // Count as 'other' if not in our main services list
                $statistics[$item->status]['services']['other'] += $item->count;
            }
        }
        
        // Calculate grand totals
        $statistics['totals'] = [
            'all' => array_sum($totalsByStatus),
            'services' => []
        ];
        
        foreach ($services as $service) {
            $statistics['totals']['services'][$service] = 0;
            foreach ($statuses as $status) {
                $statistics['totals']['services'][$service] += $statistics[$status]['services'][$service];
            }
        }
        
        // Add other services total
        $statistics['totals']['services']['other'] = 0;
        foreach ($statuses as $status) {
            $statistics['totals']['services']['other'] += $statistics[$status]['services']['other'];
        }
        
        return $statistics;
    }
}
