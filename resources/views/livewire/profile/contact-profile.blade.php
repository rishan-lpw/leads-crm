@extends('layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row bg-gray-50 min-h-screen">
    {{-- Left Sidebar --}}
    <div class="w-full lg:w-1/4 bg-white shadow p-6 rounded-xl">
        {{-- Profile Header --}}
        <div class="flex flex-col items-center">
            <img class="w-24 h-24 rounded-full object-cover border-4 border-blue-500"
                 src="{{ $customer->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($customer->firstname) }}"
                 alt="{{ $customer->firstname }}">
            <h2 class="mt-4 text-xl font-semibold text-gray-800">{{ $customer->firstname ?? 'Contact Details' }}</h2>
            <button class="mt-2 bg-blue-600 text-white px-4 py-1 rounded-lg text-sm hover:bg-blue-700">
                Send Offer
            </button>
            <p class="mt-2 text-xs text-gray-500">Last activity: {{ $customer->last_activity ?? 'N/A' }}</p>
        </div>

        {{-- Contact Info --}}
        <div class="mt-6 space-y-3 border-t pt-4">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">Contact Info</h3>
            <div class="flex items-center text-gray-700">
                <x-heroicon-s-envelope class="w-4 h-4 mr-2 text-gray-500" />
                <span>{{ $customer->email ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center text-gray-700">
                <x-heroicon-s-phone class="w-4 h-4 mr-2 text-gray-500" />
                <span>{{ $customer->mobile ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center text-gray-700">
                <x-heroicon-s-map-pin class="w-4 h-4 mr-2 text-gray-500" />
                <span>{{ $customer->address ?? 'N/A' }}</span>
            </div>
        </div>

        {{-- Membership Info --}}
        <div class="mt-6 space-y-3 border-t pt-4">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">Membership</h3>
            <div class="flex items-center text-gray-700">
                <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-gray-500" />
                <span>Exp: {{ optional($customer->membership_exp_date)->format('Y-m-d') ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center text-gray-700">
                <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-gray-500" />
                <span>Payment Exp: {{ optional($customer->payment_exp_date)->format('Y-m-d') ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center">
                <span class="px-2 py-0.5 text-xs rounded-full font-medium
                    @if($customer->membership_status === 'active')
                        bg-green-100 text-green-700
                    @elseif($customer->membership_status === 'expired')
                        bg-red-100 text-red-700
                    @else
                        bg-gray-100 text-gray-700
                    @endif">
                    {{ ucfirst($customer->membership_status ?? 'N/A') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Right Side (Activities & Details) --}}
    <div class="flex-1 p-6">
        <h3 class="text-lg font-semibold mb-4">Activities</h3>

        @forelse ($activities as $activity)
            <div class="bg-white shadow-sm rounded-lg p-4 mb-3 border border-gray-100">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-semibold text-gray-800">{{ ucfirst($activity->type) }}</span>
                    <span class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-600">{{ $activity->description ?? '-' }}</p>
            </div>
        @empty
            <p class="text-gray-400 text-sm">No activities found.</p>
        @endforelse
    </div>
</div>
@endsection
