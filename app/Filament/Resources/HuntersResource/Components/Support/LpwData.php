<?php

namespace App\Filament\Resources\HuntersResource\Components\Support;

use App\Services\LpwApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LpwData
{
	public static function getLpwUserDetailsForRecord($record): array
	{
		static $cache = [];
		$userId = $record->cust_id ?? null;
		if (!$userId) {
			return [];
		}
		if (array_key_exists($userId, $cache)) {
			return $cache[$userId];
		}
		try {
			$service = app(LpwApiService::class);
			$raw = $service->getUserDetails($userId, 10, 2);
			$data = [];
			if (isset($raw['results'][0]) && is_array($raw['results'][0])) {
				$data = $raw['results'][0];
			} elseif (is_array($raw) && array_is_list($raw)) {
				$data = $raw[0] ?? [];
			} else {
				$data = $raw;
			}
			$normalize = function ($keys, $default = null) use ($data) {
				foreach ((array) $keys as $key) {
					$value = data_get($data, $key);
					if (! is_null($value) && $value !== '') {
						return $value;
					}
				}
				return $default;
			};
			$normalized = [
				'email' => $normalize(['Uemail', 'email', 'mail', 'user_email']),
				'mobile' => $normalize(['mobile_no', 'mobile_nos', 'mobile', 'tel', 'telephone', 'phone', 'contact_number']),
				'address' => $normalize(['company_address', 'address', 'address1', 'addr']),
				'membership_exp_date' => $normalize(['expiry', 'membership_exp_date', 'membership_expiry', 'membership_exp']),
				'payment_exp_date' => $normalize(['payment_exp_date', 'expiry', 'payment_expiry', 'payment_exp']),
				'membership_status' => $normalize(['membership_status', 'status']),
				'firstname' => $normalize(['firstname', 'first_name', 'name']),
				'last_activity' => $normalize(['latest_action', 'last_activity', 'last_activity_at', 'latest_activity']),
				'id' => $normalize(['UID', 'uid', 'id', 'user_id']),
				'reg_date' => $normalize(['reg_date', 'registered_at', 'registration_date', 'member_since']),
				'source' => $normalize(['source', 'source_type']),
				'category' => $normalize(['category', 'type']),
				'customer_remarks' => $normalize(['customer_remarks', 'remarks']),
				'payment' => $normalize(['payment']),
				'latest_action' => $normalize(['latest_action']),
				'latest_comment' => $normalize(['latest_comment']),
				'payment_status' => $normalize(['status', 'payment_status']),
				'latest_commented_at' => $normalize(['latest_commented_at', 'last_commented_at']),
				'company_name' => $normalize(['company_name', 'company']),
			];
			return $cache[$userId] = array_filter($normalized, fn($v) => !is_null($v) && $v !== '');
		} catch (\Throwable $e) {
			return $cache[$userId] = [];
		}
	}

	public static function getCallLogsForRecord($record): array
	{
		static $cache = [];
		$userId = $record->cust_id ?? null;
		if (!$userId) {
			return [];
		}
		if (array_key_exists($userId, $cache)) {
			return $cache[$userId];
		}
		try {
			$service = app(LpwApiService::class);
			$cacheKey = "call_logs_{$userId}";
			$cached = cache()->get($cacheKey);
			if ($cached !== null) {
				return $cache[$userId] = $cached;
			}
			$result = $service->getCallLogs($userId, 10, 5);
			cache()->put($cacheKey, $result, 300);
			return $cache[$userId] = $result;
		} catch (\Throwable $e) {
			return $cache[$userId] = [];
		}
	}

	public static function getOldActivitiesForRecord($record): array
	{
		static $cache = [];
		$userId = $record->cust_id ?? $record->customer_id ?? null;
		if (!$userId) {
			Log::warning('getOldActivitiesForRecord: No cust_id found', [
				'record_id' => $record->id ?? 'unknown',
			]);
			return [];
		}
		if (array_key_exists($userId, $cache)) {
			return $cache[$userId];
		}
		try {
			$cacheKey = "old_activities_{$userId}";
			$cached = cache()->get($cacheKey);
			if ($cached !== null) {
				return $cache[$userId] = $cached;
			}
			$service = app(LpwApiService::class);
			$response = $service->getOldActivities($userId, 10, 2);
			$activities = [];
			if (is_array($response)) {
				if (isset($response['data']) && is_array($response['data'])) {
					$activities = $response['data'];
				} elseif (array_is_list($response)) {
					$activities = $response;
				}
			}
			cache()->put($cacheKey, $activities, 300);
			Log::info('getOldActivitiesForRecord: normalized', [
				'user_id' => $userId,
				'count' => count($activities),
			]);
			return $cache[$userId] = $activities;
		} catch (\Throwable $e) {
			Log::error('getOldActivitiesForRecord: Exception', [
				'user_id' => $userId,
				'message' => $e->getMessage(),
			]);
			return $cache[$userId] = [];
		}
	}

	public static function getUserAdsForRecord($record): array
	{
		static $cache = [];
		$userId = $record->cust_id ?? $record->customer_id ?? null;
		if (!$userId) {
			return [];
		}
		if (array_key_exists($userId, $cache)) {
			return $cache[$userId];
		}
		try {
			$service = app(LpwApiService::class);
			$raw = $service->getUserAds($userId, 2);
			if (is_array($raw)) {
				if (isset($raw['results']) && is_array($raw['results'])) {
					return $cache[$userId] = $raw['results'];
				}
				if (isset($raw['data']) && is_array($raw['data'])) {
					return $cache[$userId] = $raw['data'];
				}
				if (array_is_list($raw)) {
					return $cache[$userId] = $raw;
				}
				return $cache[$userId] = [$raw];
			}
			return $cache[$userId] = [];
		} catch (\Throwable $e) {
			return $cache[$userId] = [];
		}
	}

	public static function getFirstUserAdForRecord($record): array
	{
		$ads = self::getUserAdsForRecord($record);
		if (empty($ads)) {
			return [];
		}
		$ad = (array) ($ads[0] ?? []);
		$normalize = function ($keys, $default = null) use ($ad) {
			foreach ((array) $keys as $key) {
				$value = data_get($ad, $key);
				if (!is_null($value) && $value !== '') {
					return $value;
				}
			}
			return $default;
		};
		return [
			'heading' => $normalize(['heading', 'adtitle', 'title']),
			'type' => $normalize(['type', 'ad_type', 'listing_type']),
			'propty_type' => $normalize(['propty_type', 'property_type', 'ptype']),
			'service_type' => $normalize(['service_type', 'stype', 'service']),
			'price' => $normalize(['price', 'amount', 'price_lkr']),
			'price_type' => $normalize(['price_type', 'priceType']),
			'desc' => $normalize(['desc', 'description', 'details', 'body']),
			'street' => $normalize(['street', 'address1', 'address', 'location']),
			'city' => $normalize(['city', 'town', 'district']),
			'lat' => $normalize(['lat', 'latitude']),
			'lng' => $normalize(['lng', 'longitude']),
		];
	}

	public static function getNormalizedUserAdsForRecord($record): array
	{
		$ads = self::getUserAdsForRecord($record);
		if (empty($ads) || !is_array($ads)) {
			return [];
		}
		$normalizeOne = function ($ad) {
			$ad = (array) $ad;
			$normalize = function ($keys, $default = null) use ($ad) {
				foreach ((array) $keys as $key) {
					$value = data_get($ad, $key);
					if (!is_null($value) && $value !== '') {
						return $value;
					}
				}
				return $default;
			};
			return [
				'heading' => $normalize(['heading', 'adtitle', 'title']),
				'type' => $normalize(['type', 'ad_type', 'listing_type']),
				'propty_type' => $normalize(['propty_type', 'property_type', 'ptype']),
				'service_type' => $normalize(['service_type', 'stype', 'service']),
				'price' => $normalize(['price', 'amount', 'price_lkr']),
				'price_type' => $normalize(['price_type', 'priceType']),
				'desc' => $normalize(['desc', 'description', 'details', 'body']),
				'street' => $normalize(['street', 'address1', 'address', 'location']),
				'city' => $normalize(['city', 'town', 'district']),
				'lat' => $normalize(['lat', 'latitude']),
				'lng' => $normalize(['lng', 'longitude']),
			];
		};
		return collect($ads)
			->map(fn($ad) => $normalizeOne($ad))
			->filter(function ($ad) {
				return array_filter($ad, fn($v) => !is_null($v) && $v !== '');
			})
			->values()
			->toArray();
	}

	public static function getCallScriptForRecord($record): array
	{
		static $cache = [];
		$userId = $record->cust_id ?? $record->customer_id ?? null;
		if (!$userId) {
			return [];
		}
		// $userId = 4;
		if (array_key_exists($userId, $cache)) {
			return $cache[$userId];
		}
		try {
			$service = app(LpwApiService::class);
			Cache::forget("lpw_call_script_{$userId}");
			$raw = $service->getCallScript($userId, 10);
			// dd($raw);
			Log::info('getCallScriptForRecord: Raw API response', [
				'user_id' => $userId,
				'is_array' => is_array($raw),
				'count' => is_array($raw) ? count($raw) : 0,
				'keys' => is_array($raw) ? array_keys($raw) : null,
			]);
			if (empty($raw) || !is_array($raw)) {
				Log::warning('getCallScriptForRecord: Empty or invalid response', [
					'user_id' => $userId,
					'is_empty' => empty($raw),
					'is_array' => is_array($raw),
				]);
				return $cache[$userId] = [];
			}
			$formatted = [];
			foreach ($raw as $category => $scripts) {
				if (is_array($scripts)) {
					foreach ($scripts as $title => $content) {
						$formatted[] = [
							'category' => (string) $category,
							'title' => (string) $title,
							'content' => (string) $content,
						];
					}
				} else {
					$formatted[] = [
						'category' => (string) $category,
						'title' => (string) $category,
						'content' => (string) $scripts,
					];
				}
			}

			// Filter to only include requested headings
			$allowedOrder = [
				'pending payment',
				'rejection options',
				'common text',
				'bundle package',
			];
			$allowedSet = array_flip($allowedOrder);
			$formatted = array_values(array_filter($formatted, function ($item) use ($allowedSet) {
				$cat = strtolower(trim((string) ($item['category'] ?? '')));
				return array_key_exists($cat, $allowedSet);
			}));

			// Sort by the specified heading order
			usort($formatted, function ($a, $b) use ($allowedSet) {
				$ca = strtolower(trim((string) ($a['category'] ?? '')));
				$cb = strtolower(trim((string) ($b['category'] ?? '')));
				$ia = $allowedSet[$ca] ?? PHP_INT_MAX;
				$ib = $allowedSet[$cb] ?? PHP_INT_MAX;
				return $ia <=> $ib;
			});
			Log::info('getCallScriptForRecord: Formatted data', [
				'user_id' => $userId,
				'formatted_count' => count($formatted),
				'categories' => collect($formatted)->pluck('category')->unique()->values()->all(),
			]);
			return $cache[$userId] = $formatted;
		} catch (\Throwable $e) {
			Log::error('getCallScriptForRecord exception', [
				'user_id' => $userId,
				'message' => $e->getMessage(),
			]);
			return $cache[$userId] = [];
		}
	}
}
