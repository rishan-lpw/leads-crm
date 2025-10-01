<?php

namespace App\Filament\Resources\HuntersResource\Components;

use Filament\Actions\Action;
use App\Services\LpwApiService;
use App\Models\Customer;
use App\Models\Lead;
use Exception;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class TableHeaderActions
{
    public static function getHeaderActions(): array
    {
        $sync = Action::make('sync_api_data')
            ->label('Sync API Data')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->action(function () {
                $apiService = new LpwApiService();
                $apiResponse = $apiService->getPendingPayments();
                $results = $apiResponse['results'] ?? [];

                $created = 0;
                $updated = 0;
                $customerCreated = 0;
                $customerUpdated = 0;

                foreach ($results as $item) {
                    if (!isset($item['ad']['ad_id'])) {
                        continue;
                    }

                    $ad = $item['ad'];
                    $user = $item['user'] ?? null;

                    $customerId = null;
                    if ($user && isset($user['uid'])) {
                        $existingCustomer = Customer::where('id', $user['uid'])->first();

                        if (!$existingCustomer) {
                            $mobileCheck = null;
                            if (!empty($user['mobile'])) {
                                $mobileCheck = Customer::where('mobile', $user['mobile'])->first();
                            }

                            if ($mobileCheck) {
                                $customerId = $mobileCheck->id;
                            } else {
                                $customerPayload = [
                                    'id'               => $user['uid'],
                                    'firstname'        => $user['firstname'] ?? null,
                                    'surname'          => $user['surname'] ?? null,
                                    'mobile'           => $user['mobile'] ?? null,
                                    'mobile_alt'       => $user['mobile_alt'] ?? null,
                                    'email'            => $user['email'] ?? null,
                                ];

                                try {
                                    $customer = Customer::create($customerPayload);
                                    $customerId = $customer->id;
                                    $customerCreated++;
                                } catch (Exception $e) {
                                    $customer = Customer::where('id', $user['uid'])->first();
                                    $customerId = $customer ? $customer->id : null;
                                }
                            }
                        } else {
                            $customerId = $existingCustomer->id;

                            $newData = [
                                'firstname'        => $user['firstname'] ?? null,
                                'surname'          => $user['surname'] ?? null,
                                'mobile'           => $user['mobile'] ?? null,
                                'mobile_alt'       => $user['mobile_alt'] ?? null,
                                'email'            => $user['email'] ?? null,
                            ];

                            $hasChanges = false;
                            foreach ($newData as $key => $value) {
                                if ($existingCustomer->$key !== $value) {
                                    $hasChanges = true;
                                    break;
                                }
                            }

                            if ($hasChanges) {
                                $existingCustomer->update($newData);
                                $customerUpdated++;
                            }
                        }
                    }

                    $existingLead = Lead::where('ad_id', $ad['ad_id'])->first();

                    $payload = [
                        'ad_id'          => $ad['ad_id'],
                        'cust_id'        => $customerId,
                        'type'           => $ad['type'] ?? null,
                        'propty_type'    => $ad['propty_type'] ?? null,
                        'service_type'   => $ad['service_type'] ?? null,
                        'street'         => $ad['street'] ?? null,
                        'city'           => $ad['city'] ?? null,
                        'heading'        => $ad['heading'] ?? null,
                        'desc'           => $ad['desc'] ?? null,
                        'submit_date'    => $ad['submit_date'] ?? null,
                        'posted_date'    => $ad['posted_date'] ?? null,
                        'price'          => $ad['price'] ?? null,
                        'alt_price'      => $ad['alt_price'] ?? null,
                        'alt_currency'   => $ad['alt_currency'] ?? null,
                        'price_type'     => $ad['price_type'] ?? null,
                        'price_monthly'  => $ad['price_monthly'] ?? null,
                        'price_land_pp'  => $ad['price_land_pp'] ?? null,
                        'price_land_pa'  => $ad['price_land_pa'] ?? null,
                        'price_land_total' => $ad['price_land_total'] ?? null,
                        'price_sqft'     => $ad['price_sqft'] ?? null,
                        'land_s_l'       => $ad['land_s_l'] ?? null,
                        'comm_type'      => $ad['comm_type'] ?? null,
                        'contact_type'   => $ad['contact_type'] ?? null,
                        'contact_name'   => $ad['contact_name'] ?? null,
                        'email'          => $ad['email'] ?? null,
                        'avail'          => $ad['avail'] ?? null,
                        'lat'            => $ad['lat'] ?? null,
                        'lng'            => $ad['lng'] ?? null,
                        'blocked'        => ($ad['blocked'] ?? 'N') === 'Y' ? 1 : 0,
                        'is_active'      => is_numeric($ad['is_active']) ? (int)$ad['is_active'] : 0,
                        'source'         => $ad['source'] ?? 'API',
                        'house_post_url' => $ad['house_post_url'] ?? null,
                        'api_sync_date'  => now(),
                        'last_update_date' => now(),
                        'last_update_by' => 'API Sync',
                    ];

                    if ($existingLead) {
                        $existingLead->update($payload);
                        $updated++;
                    } else {
                        Lead::create($payload);
                        $created++;
                    }
                }

                Notification::make()
                    ->title('API Sync Completed')
                    ->body("Leads: Created {$created}, Updated {$updated}\nCustomers: Created {$customerCreated}, Updated {$customerUpdated}")
                    ->success()
                    ->send();
            });

        return [$sync];
    }
}