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
}