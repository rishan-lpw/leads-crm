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
    protected string $callScriptBaseUrl;
    protected string $callScriptToken;
    
    public function __construct()
    {
        $this->baseUrl = env('LPW_API_BASE_URL', 'https://www.lankapropertyweb.com/api/v3');
        $this->apiToken = env('LPW_API_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiYXBpX2tleSJ9.l6YJhp_Jm2tryHhDdodj0E1kui6vfLordQUDXWF3y3U');
        $this->callScriptBaseUrl = env('LPW_CALLSCRIPT_BASE_URL', 'https://www.lankapropertyweb.com/api/v3');
        $this->callScriptToken = env('LPW_CALLSCRIPT_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOiIxNTM2NzUifQ.qIjXBn2QhO00-SRd0gycGMFKXU8plWTvtjenSsdPnrE');

        // Guard against blank env values overriding defaults
        $this->baseUrl = trim((string) $this->baseUrl) !== '' ? $this->baseUrl : 'https://www.lankapropertyweb.com/api/v3';
        $this->apiToken = trim((string) $this->apiToken) !== '' ? $this->apiToken : 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiYXBpX2tleSJ9.l6YJhp_Jm2tryHhDdodj0E1kui6vfLordQUDXWF3y3U';
        $this->callScriptBaseUrl = trim((string) $this->callScriptBaseUrl) !== '' ? $this->callScriptBaseUrl : 'https://www.lankapropertyweb.com/api/v3';
        $this->callScriptToken = trim((string) $this->callScriptToken) !== '' ? $this->callScriptToken : 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1aWQiOiIxNTM2NzUifQ.qIjXBn2QhO00-SRd0gycGMFKXU8plWTvtjenSsdPnrE';
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
                // Fallback: some servers may send application/octet-stream or text/plain
                if ($json === null) {
                    $rawBody = $response->body();
                    if (is_string($rawBody) && $rawBody !== '') {
                        $decoded = json_decode($rawBody, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $json = $decoded;
                        }
                    }
                }

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
     * Get all hunters data from the API
     */
    public function getAllHunters($cacheDuration = 0)
    {
        $cacheKey = "lpw_all_hunters";
        
        // Clear cache if cacheDuration is 0 (force refresh)
        if ($cacheDuration === 0) {
            Cache::forget($cacheKey);
        }
      
        try {
            return Cache::remember($cacheKey, $cacheDuration, function () {
                $url = "{$this->baseUrl}/HuntersAll/all";
                $requestParams = [
                    'token' => $this->apiToken,
                    'cache' => 'N',
                ];
                
                Log::info("Making request to LPW HuntersAll API", [
                    'url' => $url,
                    'params' => ['cache' => 'N', 'token' => substr($this->apiToken, 0, 10) . '...']
                ]);
                
                // Increase timeout for large dataset
                $response = Http::timeout(120)->get($url, $requestParams);
                
                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    Log::info("LPW HuntersAll API response received", [
                        'status' => $response->status(),
                        'count' => is_array($responseData) ? count($responseData) : 'not an array',
                    ]);
                   
                    return $responseData ?? [];
                } else {
                    Log::error("LPW HuntersAll API request failed", [
                        'url' => $url,
                        'status' => $response->status(),
                        'reason' => $response->reason(),
                        'body' => substr($response->body(), 0, 500) // Limit body log
                    ]);
                    
                    return [];
                }
            });
        } catch (Exception $e) {
            Log::error('Exception in LPW HuntersAll API service', [
                'message' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 1000)
            ]);
            return [];
        }
    }

    /**
     * Get all pending payments from the API
     */
    public function getPendingPayments($dateFrom = null, $cacheDuration = 60)
    {
        $dateFrom = $dateFrom ?? '2024-11-01';
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

    /**
     * Get other pending payments (e.g. Other Website sources) from the API.
     */
    public function getOtherPendingPayments(
        $dateFrom = null,
        $source = null,
        $sourceType = null,
        $cacheDuration = 0
    ) {
        $dateFrom = $dateFrom ?? '2025-11-10';
        $cacheKey = "lpw_other_pending_payments_{$dateFrom}_" . ($source ?? 'all') . "_" . ($sourceType ?? 'all');

        if ($cacheDuration === 0) {
            Cache::forget($cacheKey);
        }

        $fetchData = function () use ($dateFrom, $source, $sourceType) {
            $url = "{$this->baseUrl}/OtherPendingPayments/all";
            $requestParams = [
                'token' => $this->apiToken,
                'cache' => 'N',
                'date_from' => $dateFrom,
            ];

            if ($source) {
                $requestParams['source'] = $source;
            }
            if ($sourceType) {
                $requestParams['source_type'] = $sourceType;
            }

            Log::info("Making request to LPW OtherPendingPayments API", [
                'url' => $url,
                'params' => array_merge(
                    ['cache' => 'N', 'date_from' => $dateFrom],
                    $source ? ['source' => $source] : [],
                    $sourceType ? ['source_type' => $sourceType] : [],
                    ['token' => substr($this->apiToken, 0, 10) . '...']
                ),
            ]);

            $response = Http::timeout(120)->get($url, $requestParams);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info("LPW OtherPendingPayments API response received", [
                    'status' => $response->status(),
                    'count' => is_array($responseData) ? count($responseData) : 'not an array',
                ]);

                return $responseData ?? [];
            }

            Log::error("LPW OtherPendingPayments API request failed", [
                'url' => $url,
                'status' => $response->status(),
                'reason' => $response->reason(),
                'body' => substr($response->body(), 0, 500),
            ]);

            return [];
        };

        try {
            if ($cacheDuration === 0) {
                return $fetchData();
            }

            return Cache::remember(
                $cacheKey,
                now()->addMinutes($cacheDuration),
                $fetchData
            );
        } catch (Exception $e) {
            Log::error('Exception in LPW OtherPendingPayments API service', [
                'message' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 1000),
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

    /**
     * Fetch call script for a given user id from LPW API (dev2 endpoint).
     * Returns the raw associative array from the API, or empty array on failure.
     */
    public function getCallScript(int|string $userId, int $cacheMinutes = 10): array
    {
        if (empty($userId)) {
            Log::warning('LPW getCallScript: Empty user ID provided');
            return [];
        }

        $cacheKey = "lpw_call_script_{$userId}";

        try {
            return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($userId) {
                $url = rtrim($this->callScriptBaseUrl, '/') . '/CallScript';
                $params = [
                    'token' => $this->callScriptToken,
                    'user_id' => (string) $userId,
                ];

                Log::info('LPW getCallScript request', [
                    'url' => $url,
                    'user_id' => $userId,
                    'token_length' => strlen($this->callScriptToken),
                ]);

                $response = Http::acceptJson()->timeout(20)->get($url, $params);
                
                Log::info('LPW getCallScript response', [
                    'status' => $response->status(),
                    'user_id' => $userId,
                    'body_length' => strlen($response->body()),
                ]);

                if (! $response->successful()) {
                    Log::warning('LPW getCallScript failed', [
                        'status' => $response->status(),
                        'reason' => $response->reason(),
                        'body' => $response->body(),
                        'user_id' => $userId,
                    ]);
                    return [];
                }

                $json = $response->json();
                
                if (! is_array($json)) {
                    Log::warning('LPW getCallScript: Response is not an array', [
                        'user_id' => $userId,
                        'type' => gettype($json),
                    ]);
                    return [];
                }

                if (empty($json)) {
                    Log::info('LPW getCallScript: Empty response array', [
                        'user_id' => $userId,
                    ]);
                    return [];
                }

                Log::info('LPW getCallScript success', [
                    'user_id' => $userId,
                    'sections_count' => count($json),
                    'sections' => array_keys($json),
                ]);

                return $json;
            });
        } catch (Exception $e) {
            Log::error('LPW getCallScript exception', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Check if user has any call activities from the activity API.
     * Returns true if call activities exist, false otherwise.
     */
    public function hasCallActivities(int|string $userId, int $cacheMinutes = 10): bool
    {
        try {
            $activities = $this->getOldActivities($userId, $cacheMinutes, 2);
            
            if (empty($activities) || !is_array($activities)) {
                return false;
            }
            
            // Check if any activity has action = 'call' or similar
            foreach ($activities as $activity) {
                $action = null;
                
                // Try different possible field names for action
                if (isset($activity['action'])) {
                    $action = strtolower(trim((string) $activity['action']));
                } elseif (isset($activity['activity_type'])) {
                    $action = strtolower(trim((string) $activity['activity_type']));
                } elseif (isset($activity['type'])) {
                    $action = strtolower(trim((string) $activity['type']));
                }
                
                // Check if action is 'call'
                if ($action === 'call') {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('LPW hasCallActivities exception', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user has calls with talktime >= 50 seconds.
     * Returns true if such calls exist, false otherwise.
     */
    public function hasSignificantCallActivity(int|string $userId, int $cacheMinutes = 5): bool
    {
        try {
            $callLogs = $this->getCallLogs($userId, 50, $cacheMinutes);
            
            if (empty($callLogs) || !is_array($callLogs)) {
                return false;
            }
            
            // Check if any call has talktime >= 50
            foreach ($callLogs as $call) {
                $talktime = null;
                
                // Try different possible field names for talktime
                if (isset($call['talktime'])) {
                    $talktime = $call['talktime'];
                } elseif (isset($call['talk_time'])) {
                    $talktime = $call['talk_time'];
                } elseif (isset($call['duration'])) {
                    $talktime = $call['duration'];
                }
                
                // Convert to integer and check if >= 50
                if ($talktime !== null && (int) $talktime >= 50) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('LPW hasSignificantCallActivity exception', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if user has comments in their activity from LPW API.
     * Returns true if comments exist, false otherwise.
     */
    public function hasUserActivityComments(int|string $userId, int $cacheMinutes = 10, int|string $cacheFlag = 2): bool
    {
        $cacheKey = "lpw_user_activity_comments_{$userId}_{$cacheFlag}";

        try {
            return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($userId, $cacheFlag) {
                $url = "{$this->baseUrl}/UserDetails/activity";
                $params = [
                    'token' => $this->apiToken,
                    'cache' => (string) $cacheFlag,
                    'user_id' => (string) $userId,
                ];

                Log::info('LPW hasUserActivityComments request', [
                    'url' => $url,
                    'user_id' => $userId,
                    'cache' => $cacheFlag,
                ]);

                $response = Http::timeout(6)->get($url, $params);

                if (! $response->successful()) {
                    Log::warning('LPW hasUserActivityComments failed', [
                        'status' => $response->status(),
                        'reason' => $response->reason(),
                    ]);
                    return false;
                }

                $json = $response->json();
                
                // Fallback: some servers may send application/octet-stream or text/plain
                if ($json === null) {
                    $rawBody = $response->body();
                    if (is_string($rawBody) && $rawBody !== '') {
                        $decoded = json_decode($rawBody, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $json = $decoded;
                        }
                    }
                }

                // Check if there are any comments in the activity data
                if (is_array($json)) {
                    // Check if 'comment' or 'comments' field exists and is not empty
                    if (isset($json['comment']) && !empty(trim((string) $json['comment']))) {
                        return true;
                    }
                    if (isset($json['comments']) && !empty(trim((string) $json['comments']))) {
                        return true;
                    }
                    
                    // Check in results array if it exists
                    if (isset($json['results']) && is_array($json['results'])) {
                        foreach ($json['results'] as $result) {
                            if (is_array($result)) {
                                if (isset($result['comment']) && !empty(trim((string) $result['comment']))) {
                                    return true;
                                }
                                if (isset($result['comments']) && !empty(trim((string) $result['comments']))) {
                                    return true;
                                }
                            }
                        }
                    }
                    
                    // Check in data array if it exists
                    if (isset($json['data']) && is_array($json['data'])) {
                        foreach ($json['data'] as $item) {
                            if (is_array($item)) {
                                if (isset($item['comment']) && !empty(trim((string) $item['comment']))) {
                                    return true;
                                }
                                if (isset($item['comments']) && !empty(trim((string) $item['comments']))) {
                                    return true;
                                }
                            }
                        }
                    }
                }

                return false;
            });
        } catch (Exception $e) {
            Log::error('LPW hasUserActivityComments exception', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}