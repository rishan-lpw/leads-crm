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
        $apiResponse = $uid ? $service->getCallScript($uid) : [];
        
        // API returns array where [0] = script content, [1] = property data
        $script = [];
        $propertyData = [];
        
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

        return view('livewire.pages.call-script', [
            'uid' => $uid,
            'script' => $script,
            'propertyData' => $propertyData,
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
});

require __DIR__.'/auth.php';
