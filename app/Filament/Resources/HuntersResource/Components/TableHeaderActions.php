<?php

namespace App\Filament\Resources\HuntersResource\Components;

use Filament\Actions\Action;
use App\Services\LpwApiService;
use App\Models\Customer;
use App\Models\Lead;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class TableHeaderActions
{
    public static function getHeaderActions(): array
    {
        $sync = Action::make('sync_api_data')
            ->label('Sync API Data')
            // Only visible for the user with the id 5.
            // ->visible(fn() => auth()->user()->id === 5)
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Sync API Data')
            ->modalDescription('This will sync data from Pending Payments, HuntersAll, and Other Pending Payments APIs. Large datasets will be processed in chunks. This may take several minutes.')
            ->modalSubmitActionLabel('Start Sync')
            ->action(function () {
                $apiService = new LpwApiService();
                
                // Sync from multiple API endpoints
                $allResults = [];
                
                // Sync pending payments
                $pendingPayments = $apiService->getPendingPayments();
                if (isset($pendingPayments['results'])) {
                    $allResults = array_merge($allResults, $pendingPayments['results']);
                }
                
                // Sync HuntersAll data
                try {
                    $huntersAll = $apiService->getAllHunters();
                    if (is_array($huntersAll)) {
                        // HuntersAll might have different structure, handle both cases
                        if (isset($huntersAll['results'])) {
                            $allResults = array_merge($allResults, $huntersAll['results']);
                        } elseif (!empty($huntersAll)) {
                            // If it's a direct array of items
                            $allResults = array_merge($allResults, $huntersAll);
                        }
                    }
                } catch (Exception $e) {
                    Log::error('HuntersAll API sync failed', [
                        'message' => $e->getMessage(),
                    ]);
                }

                // Sync Other Pending Payments (e.g. Other Website sources)
                try {
                    $otherPendingPayments = $apiService->getOtherPendingPayments('2025-11-10', 'Other Website', 'Ikman');

                    if (is_array($otherPendingPayments)) {
                        $metaSource = $otherPendingPayments['source'] ?? null;
                        $metaSourceType = $otherPendingPayments['source_type'] ?? null;

                        $otherResults = $otherPendingPayments['results'] ?? null;

                        if (!is_array($otherResults) && is_array($otherPendingPayments) && isset($otherPendingPayments[0])) {
                            $otherResults = $otherPendingPayments;
                        }

                        if (is_array($otherResults) && !empty($otherResults)) {
                            $otherResults = array_map(function ($entry) use ($metaSource, $metaSourceType) {
                                if (!is_array($entry)) {
                                    return $entry;
                                }

                                if ($metaSource !== null) {
                                    $entry['source'] = $metaSource;
                                }

                                if ($metaSourceType !== null) {
                                    $entry['source_type'] = $metaSourceType;
                                }

                                if (isset($entry['ad']) && is_array($entry['ad'])) {
                                    if ($metaSource !== null && empty($entry['ad']['source'])) {
                                        $entry['ad']['source'] = $metaSource;
                                    }

                                    if ($metaSourceType !== null && empty($entry['ad']['source_type'])) {
                                        $entry['ad']['source_type'] = $metaSourceType;
                                    }
                                }

                                return $entry;
                            }, $otherResults);

                            $allResults = array_merge($allResults, $otherResults);
                        }
                    }
                } catch (Exception $e) {
                    Log::error('OtherPendingPayments API sync failed', [
                        'message' => $e->getMessage(),
                    ]);
                }
                
                $results = $allResults;

                $created = 0;
                $updated = 0;
                $customerCreated = 0;
                $customerUpdated = 0;
                $skipped = 0;
                $errors = 0;

                // Process in chunks for better memory management
                $chunkSize = 100;
                $totalRecords = count($results);
                $chunks = array_chunk($results, $chunkSize);
                $processedCount = 0;

                Log::info("Starting API sync", [
                    'total_records' => $totalRecords,
                    'chunks' => count($chunks),
                    'chunk_size' => $chunkSize,
                ]);

                foreach ($chunks as $chunkIndex => $chunk) {
                    Log::info("Processing chunk", [
                        'chunk' => $chunkIndex + 1,
                        'of' => count($chunks),
                        'records_in_chunk' => count($chunk),
                    ]);

                foreach ($chunk as $item) {
                    $processedCount++;
                    if (!isset($item['ad']['ad_id'])) {
                        $skipped++;
                        continue;
                    }
                    
                    try {

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
                                    'phones'           => $user['phones'] ?? null,
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
                                'phones'           => $user['phones'] ?? null,
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

                    // Check if customer has comments, call activities, or significant call activity in API to determine status
                    $leadStatus = 'new'; // Default status
                    if ($customerId) {
                        // Check for comments
                        $hasComments = $apiService->hasUserActivityComments($customerId, 10, 2);
                        
                        // Check for call activities in API (action = 'call')
                        $hasApiCallActivities = $apiService->hasCallActivities($customerId, 10);
                        
                        // Check for significant call activity (talktime >= 50)
                        $hasSignificantCalls = $apiService->hasSignificantCallActivity($customerId, 5);
                        
                        // Check for call activities in local database
                        $hasLocalCallActivities = false;
                        if ($existingLead) {
                            $hasLocalCallActivities = $existingLead->activities()
                                ->where('activity_type', 'call')
                                ->exists();
                        }
                        
                        // Set status to 'follow_up' if any condition is met
                        if ($hasComments || $hasApiCallActivities || $hasSignificantCalls || $hasLocalCallActivities) {
                            $leadStatus = 'follow_up';
                        }
                    }

                    $payload = [
                        'ad_id'          => $ad['ad_id'],
                        'cust_id'        => $customerId,
                        'type'           => $ad['type'] ?? null,
                        'propty_type'    => $ad['propty_type'] ?? ($ad['property_type'] ?? null),
                        'service_type'   => $ad['service_type'] ?? null,
                        'street'         => $ad['street'] ?? null,
                        'city'           => $ad['city'] ?? null,
                        'heading'        => $ad['heading'] ?? null,
                        'desc'           => $ad['desc'] ?? null,
                        'submit_date'    => $ad['submit_date'] ?? null,
                        'posted_date'    => $ad['posted_date'] ?? null,
                        'price'          => $ad['price'] ?? null,
                        'ad_url'        => $ad['ad_link'] ?? null,
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
                        'source'         => $item['source'] ?? ($ad['source'] ?? 'API'),
                        'source_type'    => $item['source_type'] ?? ($ad['source_type'] ?? null),
                        'score'          => isset($ad['score']) ? (float) $ad['score'] : null,
                        'status'         => $leadStatus, // Set status based on comments
                        // 'phones'         => $ad['phones'] ?? null,
                        // 'ad_link'       => $ad['ad_link'] ?? null,
                        
                        'house_post_url' => $ad['house_post_url'] ?? null,
                        'api_sync_date'  => now(),
                        'last_update_date' => now(),
                        'last_update_by' => 'API Sync',
                    ];

                    if ($existingLead) {
                        // Check if there are any changes before updating
                        $hasChanges = false;
                        foreach ($payload as $key => $value) {
                            if ($existingLead->$key !== $value) {
                                $hasChanges = true;
                                break;
                            }
                        }
                        
                        if ($hasChanges) {
                            $existingLead->update($payload);
                            $updated++;
                            
                            // Log score updates for debugging
                            if (isset($payload['score']) && $payload['score'] !== null) {
                                Log::info('Score updated for lead', [
                                    'ad_id' => $ad['ad_id'],
                                    'old_score' => $existingLead->getOriginal('score'),
                                    'new_score' => $payload['score'],
                                    'score_type' => gettype($payload['score'])
                                ]);
                            }
                        } else {
                            $skipped++;
                        }
                    } else {
                        Lead::create($payload);
                        $created++;
                    }
                    
                    } catch (Exception $e) {
                        $errors++;
                        // Log the error for debugging
                        Log::error('API Sync Error for ad_id: ' . ($ad['ad_id'] ?? 'unknown'), [
                            'error' => $e->getMessage(),
                            'data' => $item
                        ]);
                    }
                } // End of chunk foreach

                    // Optional: Clear memory after each chunk
                    if (($chunkIndex + 1) % 10 === 0) {
                        gc_collect_cycles();
                    }
                } // End of chunks foreach

                Log::info("API sync completed", [
                    'total_processed' => $processedCount,
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ]);

                Notification::make()
                    ->title('API Sync Completed')
                    ->body("Total Records: {$totalRecords}\nLeads: Created {$created}, Updated {$updated}, Skipped {$skipped}\nCustomers: Created {$customerCreated}, Updated {$customerUpdated}\nErrors: {$errors}")
                    ->success()
                    ->duration(10000)
                    ->send();
            });

        return [$sync];
    }
}