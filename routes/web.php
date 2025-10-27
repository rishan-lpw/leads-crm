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
    // Call Script page under /admin (fetches data via LpwApiService)
    Route::get('/call-script', function (\Illuminate\Http\Request $request, \App\Services\LpwApiService $service) {
        $uid = $request->query('uid');
        $script = $uid ? $service->getCallScript($uid) : [];

        return view('livewire.pages.call-script', [
            'uid' => $uid,
            'script' => $script,
        ]);
    })->name('call.script');
});

require __DIR__.'/auth.php';
