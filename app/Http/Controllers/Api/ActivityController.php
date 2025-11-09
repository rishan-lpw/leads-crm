<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Activity;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cust_id' => ['required', 'integer'],
            'mobile' => ['required', 'string'],
        ]);

        $normalizedMobile = $this->normalizeMobile($data['mobile']);

        if ($normalizedMobile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number supplied.',
            ], 422);
        }

        $customer = Customer::query()->find($data['cust_id']);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $customerMobiles = collect([
            $this->normalizeMobile($customer->mobile ?? null),
            $this->normalizeMobile($customer->mobile_alt ?? null),
        ])->filter()->unique()->values();

        if ($customerMobiles->isNotEmpty() && ! $customerMobiles->contains($normalizedMobile)) {
            return response()->json([
                'success' => false,
                'message' => 'Provided mobile number does not match the customer records.',
            ], 422);
        }

        $activities = Activity::query()
            ->with([
                'user:id,username,name',
                'paymentStatus:id,status,sub_status',
                'funnel:id,category,stage',
            ])
            ->where('customer_id', $customer->id)
            ->latest('created_at')
            ->get();

        $payload = [
            'success' => true,
            'message' => 'Activities fetched successfully.',
            'customer' => [
                'id' => $customer->id,
                'name' => trim($customer->firstname . ' ' . $customer->surname),
                'mobile' => $customer->mobile,
                'mobile_alt' => $customer->mobile_alt,
            ],
            'data' => $activities->map(function (Activity $activity) {
                return [
                    'id' => $activity->id,
                    'lead_id' => $activity->lead_id,
                    'activity_type' => $activity->activity_type,
                    'status' => $activity->status,
                    'stage' => $activity->stage,
                    'action' => $activity->action,
                    'comments' => $activity->comments,
                    'date_time' => optional($activity->date_time)->toIso8601String(),
                    'created_at' => optional($activity->created_at)->toIso8601String(),
                    'updated_at' => optional($activity->updated_at)->toIso8601String(),
                    'assigned_by' => $activity->assigned_by,
                    'assigned_user' => [
                        'id' => optional($activity->user)->id,
                        'username' => optional($activity->user)->username,
                        'name' => optional($activity->user)->name,
                    ],
                    'payment_status' => [
                        'id' => optional($activity->paymentStatus)->id,
                        'status' => optional($activity->paymentStatus)->status,
                        'sub_status' => optional($activity->paymentStatus)->sub_status,
                    ],
                    'funnel' => [
                        'id' => optional($activity->funnel)->id,
                        'category' => optional($activity->funnel)->category,
                        'stage' => optional($activity->funnel)->stage,
                    ],
                ];
            }),
        ];

        return response()->json($payload);
    }

    public function show(int $custId, string $mobile): JsonResponse
    {
        $normalizedMobile = $this->normalizeMobile($mobile);

        if ($normalizedMobile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number supplied.',
            ], 422);
        }

        $customer = Customer::query()->find($custId);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $customerMobiles = collect([
            $this->normalizeMobile($customer->mobile ?? null),
            $this->normalizeMobile($customer->mobile_alt ?? null),
        ])->filter()->unique()->values();

        if ($customerMobiles->isNotEmpty() && ! $customerMobiles->contains($normalizedMobile)) {
            return response()->json([
                'success' => false,
                'message' => 'Provided mobile number does not match the customer records.',
            ], 422);
        }

        $activities = Activity::query()
            ->with([
                'user:id,username,name',
                'paymentStatus:id,status,sub_status',
                'funnel:id,category,stage',
            ])
            ->where('customer_id', $customer->id)
            ->latest('created_at')
            ->get();

        $payload = [
            'success' => true,
            'message' => 'Activities fetched successfully.',
            'customer' => [
                'id' => $customer->id,
                'name' => trim($customer->firstname . ' ' . $customer->surname),
                'mobile' => $customer->mobile,
                'mobile_alt' => $customer->mobile_alt,
            ],
            'data' => $activities->map(function (Activity $activity) {
                return [
                    'id' => $activity->id,
                    'lead_id' => $activity->lead_id,
                    'activity_type' => $activity->activity_type,
                    'status' => $activity->status,
                    'stage' => $activity->stage,
                    'action' => $activity->action,
                    'comments' => $activity->comments,
                    'date_time' => optional($activity->date_time)->toIso8601String(),
                    'created_at' => optional($activity->created_at)->toIso8601String(),
                    'updated_at' => optional($activity->updated_at)->toIso8601String(),
                    'assigned_by' => $activity->assigned_by,
                    'assigned_user' => [
                        'id' => optional($activity->user)->id,
                        'username' => optional($activity->user)->username,
                        'name' => optional($activity->user)->name,
                    ],
                    'payment_status' => [
                        'id' => optional($activity->paymentStatus)->id,
                        'status' => optional($activity->paymentStatus)->status,
                        'sub_status' => optional($activity->paymentStatus)->sub_status,
                    ],
                    'funnel' => [
                        'id' => optional($activity->funnel)->id,
                        'category' => optional($activity->funnel)->category,
                        'stage' => optional($activity->funnel)->stage,
                    ],
                ];
            }),
        ];

        return response()->json($payload);
    }

    private function normalizeMobile(?string $raw): ?string
    {
        if (blank($raw)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '94')) {
            $digits = substr($digits, 2);
        }

        if (Str::startsWith($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return strlen($digits) === 9 ? $digits : null;
    }
}

