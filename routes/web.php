<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Models\Customer;
use App\Models\Activity;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('');

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

require __DIR__.'/auth.php';
