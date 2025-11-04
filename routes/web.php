<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Models\Customer;
use App\Models\Activity;

Route::view('/', 'welcome');

Route::redirect('/dashboard', '/admin')
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Contact profile preview route
Route::get('/contact-profile/{customer}', function (Customer $customer) {
    $activities = Activity::query()
        ->where('customer_id', $customer->id)
        ->latest('created_at')
        ->limit(20)
        ->get()
        ->map(function (Activity $activity) {
            return (object) [
                'type' => $activity->activity_type ?? 'note',
                'description' => $activity->comments ?? null,
                'created_at' => $activity->created_at,
            ];
        });

    return view('livewire.profile.contact-profile', [
        'customer' => $customer,
        'activities' => $activities,
    ]);
})->name('contact.profile');

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Admin-scoped routes
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    // Helper closure for call script routes
    $callScriptHandler = function (\Illuminate\Http\Request $request, \App\Services\LpwApiService $service, $activeTab = 'sinhala') {
        $uid = $request->query('uid');
        
        // API returns array where [0] = script content, [1] = property data
        $script = [];
        $propertyData = [];
        
        // Only fetch script/transcript data if NOT on ad-stats tab (stats only, no transcript needed)
        if ($activeTab !== 'ad-stats') {
            $apiResponse = $uid ? $service->getCallScript($uid) : [];
            
            \Illuminate\Support\Facades\Log::info('Call Script Handler - API Response', [
                'uid' => $uid,
                'is_array' => is_array($apiResponse),
                'count' => is_array($apiResponse) ? count($apiResponse) : 0,
                'has_two_elements' => is_array($apiResponse) && count($apiResponse) >= 2,
            ]);
            
            if (is_array($apiResponse) && count($apiResponse) >= 2) {
                $script = $apiResponse[0] ?? [];
                $propertyData = $apiResponse[1][0] ?? []; // First item in data array
                
                \Illuminate\Support\Facades\Log::info('Call Script Handler - Extracted Data', [
                    'script_keys' => is_array($script) ? array_keys($script) : 'not array',
                    'script_count' => is_array($script) ? count($script) : 0,
                    'propertyData_keys' => is_array($propertyData) ? array_keys($propertyData) : 'not array',
                ]);
            } elseif (is_array($apiResponse)) {
                // Fallback: treat as script only
                $script = $apiResponse;
                \Illuminate\Support\Facades\Log::info('Call Script Handler - Using fallback (old format)');
            }
        }

        // Get all stats data if on ad-stats tab
        $statsData = [];
        if ($activeTab === 'ad-stats' && $uid) {
            try {
                // Get the lead record(s) by cust_id
                $leads = \App\Models\Lead::where('cust_id', $uid)->get();
                
                if ($leads->isNotEmpty()) {
                    // Use the first lead to calculate stats
                    $lead = $leads->first();
                    
                    // Total Activities
                    $statsData['total_activities'] = $lead->activities()->count();
                    
                    // Recent Calls (Last 30 Days)
                    $statsData['recent_calls'] = $lead->activities()
                        ->where('stage', 'call')
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count();
                    
                    // Average Score
                    $avg = $lead->activities()
                        ->whereNotNull('level_score')
                        ->avg('level_score');
                    $statsData['avg_score'] = $avg ? number_format($avg, 1) . '/10' : 'N/A';
                    
                    // Activity Summary
                    $statsData['today_activities'] = $lead->activities()
                        ->whereDate('created_at', today())
                        ->count();
                    $statsData['week_activities'] = $lead->activities()
                        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->count();
                    $statsData['month_activities'] = $lead->activities()
                        ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                        ->count();
                    $statsData['all_time_activities'] = $lead->activities()->count();
                    
                    // Payment Status
                    $latestActivity = $lead->activities()
                        ->with('paymentStatus')
                        ->whereNotNull('payment_status_id')
                        ->orderByDesc('created_at')
                        ->first();
                    $statsData['payment_status'] = $latestActivity?->paymentStatus?->payment_status ?? 'N/A';
                    
                    // Activity Breakdown by Stage
                    $statsData['activity_breakdown'] = $lead->activities()
                        ->selectRaw('stage, COUNT(*) as count')
                        ->groupBy('stage')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'type' => ucfirst($item->stage ?? 'Other'),
                                'count' => $item->count,
                            ];
                        })
                        ->toArray();
                    
                    // API Stats
                    $record = (object)['cust_id' => $uid, 'customer_id' => $uid];
                    $statsData['api_stats'] = \App\Filament\Resources\HuntersResource\Components\Support\LpwData::getUserStatsForAdsNormalized($record);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to load stats data', ['error' => $e->getMessage()]);
                $statsData = [];
            }
        }

        return view('livewire.pages.call-script', [
            'uid' => $uid,
            'script' => $script,
            'propertyData' => $propertyData,
            'apiStats' => $statsData['api_stats'] ?? [],
            'statsData' => $statsData,
            'activeTab' => $activeTab,
        ]);
    };
    
    // Call Script main route (defaults to Sinhala tab)
    Route::get('/call-script', function (\Illuminate\Http\Request $request, \App\Services\LpwApiService $service) use ($callScriptHandler) {
        return $callScriptHandler($request, $service, 'sinhala');
    })->name('call.script');
    
    // Call Script nested tab routes
    Route::get('/call-script/sinhala', function (\Illuminate\Http\Request $request, \App\Services\LpwApiService $service) use ($callScriptHandler) {
        return $callScriptHandler($request, $service, 'sinhala');
    })->name('call.script.sinhala');
    
    Route::get('/call-script/stats', function (\Illuminate\Http\Request $request, \App\Services\LpwApiService $service) use ($callScriptHandler) {
        return $callScriptHandler($request, $service, 'stats');
    })->name('call.script.stats');
    
    Route::get('/call-script/bundle-package', function (\Illuminate\Http\Request $request, \App\Services\LpwApiService $service) use ($callScriptHandler) {
        return $callScriptHandler($request, $service, 'bundle-package');
    })->name('call.script.bundle-package');
    
    Route::get('/call-script/ad-stats', function (\Illuminate\Http\Request $request, \App\Services\LpwApiService $service) use ($callScriptHandler) {
        return $callScriptHandler($request, $service, 'ad-stats');
    })->name('call.script.ad-stats');
});

require __DIR__.'/auth.php';
