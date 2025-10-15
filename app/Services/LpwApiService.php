<?php
// filepath: d:\react\lpw-crm-Copy\app\Services\LpwApiService.php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log; // Correct Log import
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class LpwApiService
{
    protected string $baseUrl;
    protected string $apiToken;
    
    public function __construct()
    {
        $this->baseUrl = env('LPW_API_BASE_URL', 'https://www.lankapropertyweb.com/api/v3');
        $this->apiToken = env('LPW_API_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiYXBpX2tleSJ9.l6YJhp_Jm2tryHhDdodj0E1kui6vfLordQUDXWF3y3U');
    }
    
    /**
     * Fetch user details by user_id (cust_id) from LPW API.
     * Returns an associative array of details or empty array on failure.
     */
    public function getUserDetails(int|string $userId, int $cacheMinutes = 10, int|string $cacheFlag = 2): array
    {
        $cacheKey = "lpw_user_details_{$userId}_{$cacheFlag}";

        try {
            return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($userId, $cacheFlag) {
                $url = "{$this->baseUrl}/UserDetails/detail";
                $params = [
                    'token' => $this->apiToken,
                    'cache' => (string) $cacheFlag,
                    'user_id' => (string) $userId,
                ];

                Log::info('LPW getUserDetails request', [
                    'url' => $url,
                    'user_id' => $userId,
                    'cache' => $cacheFlag,
                ]);

                $response = Http::timeout(6)->get($url, $params);

                if (! $response->successful()) {
                    Log::warning('LPW getUserDetails failed', [
                        'status' => $response->status(),
                        'reason' => $response->reason(),
                    ]);
                    return [];
                }

                $json = $response->json();

                // Some endpoints wrap payload under 'data'
                $payload = is_array($json) && array_key_exists('data', $json) ? ($json['data'] ?? []) : ($json ?? []);

                if (! is_array($payload)) {
                    return [];
                }

                return $payload;
            });
        } catch (Exception $e) {
            Log::error('LPW getUserDetails exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get all pending payments from the API
     */
    public function getPendingPayments($dateFrom = null, $cacheDuration = 60)
    {
        $dateFrom = $dateFrom ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $cacheKey = "lpw_pending_payments_{$dateFrom}";
        
        // Clear cache if cacheDuration is 0 (force refresh)
        if ($cacheDuration === 0) {
            Cache::forget($cacheKey);
        }
      
        try {
            return Cache::remember($cacheKey, $cacheDuration, function () use ($dateFrom) {
                $url = "{$this->baseUrl}/PendingPayments/all";
                $requestParams = [
                    'token' => $this->apiToken,
                    'cache' => 'N',
                    'date_from' => $dateFrom
                ];
                
                Log::info("Making request to LPW API", [
                    'url' => $url,
                    'params' => array_merge(
                        ['cache' => 'N', 'date_from' => $dateFrom],
                        ['token' => substr($this->apiToken, 0, 10) . '...'] // Don't log full token
                    )
                ]);
                
                $response = Http::get($url, $requestParams);
                
                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    Log::info("LPW API response received", [
                        'status' => $response->status(),
                        'count' => is_array($responseData) ? count($responseData) : 'not an array',
                    ]);
                   
                    return $responseData ?? [];
                } else {
                    Log::error("LPW API request failed", [
                        'url' => $url,
                        'status' => $response->status(),
                        'reason' => $response->reason(),
                        'body' => $response->body()
                    ]);
                    
                    return [];
                }
            });
        } catch (Exception $e) {
            Log::error('Exception in LPW API service', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getOldActivities(int|string $userId, int $cacheMinutes = 10, int|string $cacheFlag = 2): array
    {
        $cacheKey = "lpw_old_activities_{$userId}_{$cacheFlag}";
        
        try {
            return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($userId, $cacheFlag) {
                $url = "{$this->baseUrl}/UserDetails/activity";
                $params = [
                    'token' => $this->apiToken,
                    'cache' => (string) $cacheFlag,
                    'user_id' => (string) $userId,
                ];
                
                Log::info('LPW getOldActivities request', [
                    'url' => $url,
                    'user_id' => $userId,
                    'cache' => $cacheFlag,
                ]);
                
                $response = Http::timeout(6)->get($url, $params);
                
                if (! $response->successful()) {
                    Log::warning('LPW getOldActivities failed', [
                        'status' => $response->status(),
                        'reason' => $response->reason(),
                        'body' => $response->body(),
                    ]);
                    return [];
                }
                
                $json = $response->json();
                
                Log::info('LPW getOldActivities response', [
                    'user_id' => $userId,
                    'has_data' => isset($json['data']),
                    'has_results' => isset($json['results']),
                    'is_array' => is_array($json),
                    'keys' => is_array($json) ? array_keys($json) : 'not array',
                ]);
                
                $data = $json['data'] ?? $json['results'] ?? $json ?? [];
                
                if (!is_array($data)) {
                    Log::warning('LPW getOldActivities: data is not array', ['type' => gettype($data)]);
                    return [];
                }
                
                Log::info('LPW getOldActivities: returning data', [
                    'count' => count($data),
                    'sample_keys' => !empty($data) ? array_keys($data[0] ?? []) : 'empty'
                ]);
                
                return $data;
            });
        } catch (Exception $e) {
            Log::error('LPW getOldActivities exception', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Fetch call logs for a user by user_id (cust_id) from LPW API.
     * Returns an array of call logs or empty array on failure.
     */
    public function getCallLogs(int|string $userId, int $limit = 10, int $cacheMinutes = 5): array
    {
        $cacheKey = "lpw_call_logs_{$userId}";

        try {
            return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($userId, $limit) {
                $url = "{$this->baseUrl}/UserDetails/calllLog";
                $params = [
                    'token' => $this->apiToken,
                    'user_id' => (string) $userId,
                    'cache' => '1',
                ];

                Log::info('LPW getCallLogs request', [
                    'url' => $url,
                    'user_id' => $userId,
                ]);

                $response = Http::timeout(5)->get($url, $params);

                if (! $response->successful()) {
                    Log::warning('LPW getCallLogs failed', [
                        'status' => $response->status(),
                        'reason' => $response->reason(),
                    ]);
                    return [];
                }

                $json = $response->json();

                // Extract data from the response
                $data = $json['data'] ?? $json['results'] ?? $json ?? [];
                
                if (!is_array($data)) {
                    return [];
                }

                // Limit results and add index
                return collect($data)->take($limit)->map(function ($log, $index) {
                    return array_merge($log, ['index' => $index + 1]);
                })->toArray();
            });
        } catch (Exception $e) {
            Log::error('LPW getCallLogs exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Fetch ads data for a specific user from LPW API
     */
    public function getUserAds($userId, $cache = 2)
    {
        
        try {
            $url = "https://www.lankapropertyweb.com/api/v3/UserDetails/ads";
            $params = [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiYXBpX2tleSJ9.l6YJhp_Jm2tryHhDdodj0E1kui6vfLordQUDXWF3y3U',
                'cache' => $cache,
                'user_id' => $userId
            ];

            $response = Http::timeout(30)->get($url, $params);
            // dd($response->json());
            if ($response->successful()) {
                $data = $response->json();
                // Log successful response
                Log::info('LPW getUserAds successful', [
                    'user_id' => $userId,
                    'response_size' => is_array($data) ? count($data) : 'unknown'
                ]);

                return $data;
            } else {
                Log::error('LPW getUserAds failed', [
                    'user_id' => $userId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [];
            }
        } catch (Exception $e) {
            Log::error('LPW getUserAds exception', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }
}