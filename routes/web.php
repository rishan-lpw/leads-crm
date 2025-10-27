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
        $script = $uid ? $service->getCallScript($uid) : [];

        return view('livewire.pages.call-script', [
            'uid' => $uid,
            'script' => $script,
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
