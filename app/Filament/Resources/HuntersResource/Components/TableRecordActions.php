<?php

namespace App\Filament\Resources\HuntersResource\Components;

use App\Filament\Resources\HuntersResource;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Gate;
use Filament\Infolists\Components\Actions as InfolistActions;
use Filament\Infolists\Components\TextEntry as TextEntryInfo;
use Filament\Infolists\Components\ViewEntry;
use App\Models\PaymentStatus;
use App\Models\Funnel;
use App\Models\User;
use App\Services\LpwApiService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Str;
use Filament\Actions\ButtonAction;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Illuminate\Support\Js;
use Filament\Support\View\Components\ButtonComponent;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
// Removed old chart widget imports
use Livewire\Component as LivewireComponent;
use App\Filament\Resources\HuntersResource\Components\Sections\ContactDetailsSections;
use App\Filament\Resources\HuntersResource\Components\Sections\OldActivitiesSection;
use App\Filament\Resources\HuntersResource\Components\Sections\CallLogSection;
use App\Filament\Resources\HuntersResource\Components\Sections\CallScriptSection;
use App\Filament\Resources\HuntersResource\Components\Tabs\ActivityTabs;
use App\Filament\Resources\HuntersResource\Components\Actions\RecordActions;
use App\Filament\Resources\HuntersResource\Components\Support\LpwData;
use App\Filament\Resources\HuntersResource\Widgets\FunnelChart;
use App\Filament\Resources\HuntersResource\Widgets\ActivitiesByTypeChart;
use App\Filament\Resources\HuntersResource\Widgets\ActivitiesOverTimeChart;
use App\Models\Customer;
use Filament\Schemas\Components\Wizard;

class TableRecordActions
{
    private const MANUAL_MOBILE_VALUE = '__manual__';

    /**
     * Fetch and normalize LPW user details for a given lead record.
     * Normalized keys: email, mobile, address, membership_exp_date, payment_exp_date, membership_status, firstname, last_activity
     */
    // private static function getLpwUserDetailsForRecord($record): array
    // {
    //     static $cache = [];
        
    //     $userId = $record->cust_id ?? null;
    //     if (! $userId) {
    //         return [];
    //     }

    //     if (array_key_exists($userId, $cache)) {
    //         return $cache[$userId];
    //     }

    //     try {
    //         $service = app(LpwApiService::class);
    //         $raw = $service->getUserDetails($userId, 10, 2);
            
    //         // Extract data from nested results structure
    //         $data = [];
    //         if (isset($raw['results'][0]) && is_array($raw['results'][0])) {
    //             $data = $raw['results'][0];
    //         } elseif (is_array($raw) && array_is_list($raw)) {
    //             $data = $raw[0] ?? [];
    //         } else {
    //             $data = $raw;
    //         }

    //         $normalize = function ($keys, $default = null) use ($data) {
    //             foreach ((array) $keys as $key) {
    //                 $value = data_get($data, $key);
    //                 if (! is_null($value) && $value !== '') {
    //                     return $value;
    //                 }
    //             }
    //             return $default;
    //         };

    //         $normalized = [
    //             'email' => $normalize(['Uemail', 'email', 'mail', 'user_email']),
    //             'mobile' => $normalize(['mobile_no', 'mobile_nos', 'mobile', 'tel', 'telephone', 'phone', 'contact_number']),
    //             'address' => $normalize(['company_address', 'address', 'address1', 'addr']),
    //             'membership_exp_date' => $normalize(['expiry', 'membership_exp_date', 'membership_expiry', 'membership_exp']),
    //             'payment_exp_date' => $normalize(['payment_exp_date', 'expiry', 'payment_expiry', 'payment_exp']),
    //             'membership_status' => $normalize(['membership_status', 'status']),
    //             'firstname' => $normalize(['firstname', 'first_name', 'name']),
    //             'last_activity' => $normalize(['latest_action', 'last_activity', 'last_activity_at', 'latest_activity']),
    //             'id' => $normalize(['UID', 'uid', 'id', 'user_id']),
    //             'reg_date' => $normalize(['reg_date', 'registered_at', 'registration_date', 'member_since']),
    //             'source' => $normalize(['source', 'source_type']),
    //             'category' => $normalize(['category', 'type']),
    //             'customer_remarks' => $normalize(['customer_remarks', 'remarks']),
    //             'payment' => $normalize(['payment']),
    //             'latest_action' => $normalize(['latest_action']),
    //             'latest_comment' => $normalize(['latest_comment']),
    //             'payment_status' => $normalize(['status', 'payment_status']),
    //             'latest_commented_at' => $normalize(['latest_commented_at', 'last_commented_at']),
    //             'company_name' => $normalize(['company_name', 'company']),
    //         ];

    //         return $cache[$userId] = array_filter($normalized, fn($v) => !is_null($v) && $v !== '');
    //     } catch (\Throwable $e) {
    //         return $cache[$userId] = [];
    //     }
    // }

    /**
     * Fetch call logs for a given lead record from LPW API.
     */
    // private static function getCallLogsForRecord($record): array
    // {
    //     static $cache = [];
        
    //     $userId = $record->cust_id ?? null;
    //     if (!$userId) {
    //         return [];
    //     }

    //     if (array_key_exists($userId, $cache)) {
    //         return $cache[$userId];
    //     }

    //     try {
    //         $service = app(LpwApiService::class);
    //         // Cache for 5 minutes to reduce API calls
    //         $cacheKey = "call_logs_{$userId}";
    //         $cached = cache()->get($cacheKey);
            
    //         if ($cached !== null) {
    //             return $cache[$userId] = $cached;
    //         }
            
    //         $result = $service->getCallLogs($userId, 10, 5);
    //         cache()->put($cacheKey, $result, 300); // 5 minutes cache
    //         return $cache[$userId] = $result;
    //     } catch (\Throwable $e) {
    //         return $cache[$userId] = [];
    //     }
    // }

    /**
     * Fetch old activities for a given lead record from LPW API.
     */
//     private static function getOldActivitiesForRecord($record): array
// {
//     static $cache = [];

//     $userId = $record->cust_id ?? $record->customer_id ?? null;

//     if (!$userId) {
//         Log::warning('getOldActivitiesForRecord: No cust_id found', [
//             'record_id' => $record->id ?? 'unknown',
//         ]);
//         return [];
//     }

//     if (array_key_exists($userId, $cache)) {
//         return $cache[$userId];
//     }

//     try {
//         // Check cache first
//         $cacheKey = "old_activities_{$userId}";
//         $cached = cache()->get($cacheKey);
        
//         if ($cached !== null) {
//             return $cache[$userId] = $cached;
//         }

//         $service = app(\App\Services\LpwApiService::class);
//         $response = $service->getOldActivities($userId, 10, 2);

//         // Normalize result
//         $activities = [];
//         if (is_array($response)) {
//             if (isset($response['data']) && is_array($response['data'])) {
//                 $activities = $response['data'];
//             } elseif (array_is_list($response)) {
//                 $activities = $response;
//             }
//         }

//         // Cache for 5 minutes to reduce API calls
//         cache()->put($cacheKey, $activities, 300);
        
//         // Log useful debug info
//         Log::info('getOldActivitiesForRecord: normalized', [
//             'user_id' => $userId,
//             'count' => count($activities),
//         ]);

//         // Cache and return a safe array
//         return $cache[$userId] = $activities;
//     } catch (\Throwable $e) {
//         Log::error('getOldActivitiesForRecord: Exception', [
//             'user_id' => $userId,
//             'message' => $e->getMessage(),
//         ]);
//         return $cache[$userId] = [];
//     }
// }


    private static function oldActivitiesSection(): array
    {
        return OldActivitiesSection::build();
    }

    private static function allActivitiesSection(): array
    {
        return [
            RepeatableEntry::make('all_activities')
                ->label('')
                ->lazy()
                ->contained(false)
                ->getStateUsing(function ($record) {
                    $activities = $record->activities()
                        ->with('user', 'paymentStatus', 'funnel')
                        ->latest()
                        ->limit(50)
                        ->get();

                    return self::formatActivities($activities);
                })
                ->schema([
                    Section::make('')
                        ->lazy()
                        ->schema([
                            ComponentsGrid::make(4)->lazy()->schema([
                                TextEntry::make('user.username')->label('Done By')->placeholder('N/A'),
                                TextEntry::make('created_at')->label('Date & Time')->dateTime('M d, Y H:i')->placeholder('N/A'),
                                TextEntry::make('activity_type')->label('Activity Type')->badge()->placeholder('N/A'),
                                TextEntry::make('paymentStatus.status')->label('Payment Status')->badge()->color('info')->placeholder('N/A'),
                            ]),
                            Section::make('More Details')
                                ->lazy()
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    ComponentsGrid::make(3)->schema([
                                        TextEntry::make('id')->label('Activity ID')->placeholder('N/A'),
                                        TextEntry::make('stage')->label('Stage')->placeholder('N/A'),
                                        TextEntry::make('paymentStatus.sub_status')->label('Sub Status')->badge()->placeholder('N/A'),
                                        TextEntry::make('funnel.category')->label('Funnel Category')->placeholder('N/A'),
                                        TextEntry::make('funnel.stage')->label('Funnel Stage')->placeholder('N/A'),
                                        TextEntry::make('follow_up_type')->label('Follow Up Type')->placeholder('N/A'),
                                        TextEntry::make('comments')->label('Comments')->columnSpanFull()->default('No comments available'),
                                    ]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function myActivitiesSection(): array
    {
        return [
            RepeatableEntry::make('my_activities')
                ->label('')
                ->lazy()
                ->contained(false)
                ->getStateUsing(function ($record) {
                    $activities = $record->activities()
                        ->with('user', 'paymentStatus', 'funnel')
                        ->where('assigned_by', Auth::id())
                        ->latest()
                        ->limit(50)
                        ->get();

                    return self::formatActivities($activities);
                })
                ->schema([
                    Section::make('')
                        ->lazy()
                        ->schema([
                            ComponentsGrid::make(4)->lazy()->schema([
                                TextEntry::make('user.username')->label('Done By')->placeholder('N/A'),
                                TextEntry::make('created_at')->label('Date & Time')->dateTime('M d, Y H:i')->placeholder('N/A'),
                                TextEntry::make('activity_type')->label('Activity Type')->badge()->placeholder('N/A'),
                                TextEntry::make('paymentStatus.status')->label('Payment Status')->badge()->color('info')->placeholder('N/A'),
                            ]),
                            Section::make('More Details')
                                ->lazy()
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    ComponentsGrid::make(3)->schema([
                                        TextEntry::make('id')->label('Activity ID')->placeholder('N/A'),
                                        TextEntry::make('stage')->label('Stage')->placeholder('N/A'),
                                        TextEntry::make('paymentStatus.sub_status')->label('Sub Status')->badge()->placeholder('N/A'),
                                        TextEntry::make('funnel.category')->label('Funnel Category')->placeholder('N/A'),
                                        TextEntry::make('funnel.stage')->label('Funnel Stage')->placeholder('N/A'),
                                        TextEntry::make('follow_up_type')->label('Follow Up Type')->placeholder('N/A'),
                                        TextEntry::make('comments')->label('Comments')->columnSpanFull()->default('No comments available'),
                                    ]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function callActivitiesSection(): array
    {
        return [
            RepeatableEntry::make('call_activities')
                ->label('')
                ->lazy()
                ->contained(false)
                ->getStateUsing(function ($record) {
                    $activities = $record->activities()
                        ->with('user', 'paymentStatus', 'funnel')
                        ->where('activity_type', 'call')
                        ->latest()
                        ->limit(50)
                        ->get();

                    return self::formatActivities($activities);
                })
                ->schema([
                    Section::make('')
                        ->lazy()
                        ->schema([
                            ComponentsGrid::make(4)->lazy()->schema([
                                TextEntry::make('user.username')->label('Done By')->placeholder('N/A'),
                                TextEntry::make('created_at')->label('Date & Time')->dateTime('M d, Y H:i')->placeholder('N/A'),
                                TextEntry::make('activity_type')->label('Activity Type')->badge()->placeholder('N/A'),
                                TextEntry::make('paymentStatus.status')->label('Payment Status')->badge()->color('info')->placeholder('N/A'),
                            ]),
                            Section::make('More Details')
                                ->lazy()
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    ComponentsGrid::make(3)->schema([
                                        TextEntry::make('id')->label('Activity ID')->placeholder('N/A'),
                                        // TextEntry::make('stage')->label('Stage')->placeholder('N/A'),
                                        TextEntry::make('paymentStatus.sub_status')->label('Sub Status')->badge()->placeholder('N/A'),
                                        TextEntry::make('funnel.category')->label('Funnel Category')->placeholder('N/A'),
                                        TextEntry::make('funnel.stage')->label('Funnel Stage')->placeholder('N/A'),
                                        TextEntry::make('follow_up_type')->label('Follow Up Type')->placeholder('N/A'),
                                        TextEntry::make('comments')->label('Comments')->columnSpanFull()->default('No comments available'),
                                    ]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ])
        ];
    }

    /**
     * Build the reusable Call Log section schema.
     */
    private static function callLogSection(): array
    {
        return CallLogSection::build();
    }
    
    private static function formatActivities($activities): array
    {
        return $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'user' => [
                    'username' => optional($activity->user)->username,
                ],
                'created_at' => $activity->created_at,
                'activity_type' => $activity->activity_type,
                'stage' => $activity->stage,
                'paymentStatus' => [
                    'status' => optional($activity->paymentStatus)->status,
                    'sub_status' => optional($activity->paymentStatus)->sub_status,
                ],
                'funnel' => [
                    'category' => optional($activity->funnel)->category,
                    'stage' => optional($activity->funnel)->stage,
                ],
                'follow_up_type' => $activity->follow_up_type,
                'comments' => $activity->comments,
            ];
        })->toArray();
    }

    /**
     * Fetch LPW user ads for the given record and normalize the payload.
     */
    private static function getUserAdsForRecord($record): array
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

            // Normalize possible response shapes
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
                // Single object payload
                return $cache[$userId] = [$raw];
            }

            return $cache[$userId] = [];
        } catch (\Throwable $e) {
            return $cache[$userId] = [];
        }
    }

    private static function contactDetailsSectionForOverview(): Section
    {
        return ContactDetailsSections::forOverview();
    }

    // Select the mobile number from the customer table column 'mobile'.
    // Take the mobile number as follows: 
    // 1. If the mobile number is in format 771234567, then take the number 771234567.
    // 2. If the mobile number is in format 771234567, then take the number 771234567.
    // 3. If the mobile number is in format +94771234567, (neglect +94) then take the number 771234567.
    // 4. Neglect the space characters in the mobile number.

    // Create a function to get the mobile number from the user details API.
                       
    
    /**
     * Build the reusable Contact Details section.
     */
    private static function contactDetailsSection(): Section
    {
        return ContactDetailsSections::forSidePanel();
    }

	/**
	 * Normalize a raw phone string to a 9-digit Sri Lankan mobile without leading 0 or country code.
	 * Rules:
	 * - Remove spaces and non-digits, ignore leading +94 or 94, drop a single leading 0
	 * - Result should be exactly 9 digits (e.g. 771234567). Otherwise return null.
	 */
	private static function normalizeMobile(?string $raw): ?string
	{
		if (empty($raw)) {
			return null;
		}

		$digits = preg_replace('/[^0-9+]/', '', $raw) ?? '';
		$digits = ltrim($digits, '+');

		if (str_starts_with($digits, '94')) {
			$digits = substr($digits, 2);
		}

		if (str_starts_with($digits, '0')) {
			$digits = substr($digits, 1);
		}

		return (strlen($digits) === 9) ? $digits : null;
	}

	/**
	 * Fetch the best-available mobile for the given record, preferring record fields,
	 * then LPW user details API. Returns 9-digit string like 771234567 or null.
	 */
	private static function getWhatsappTemplates(): array
	{
		return [
			'initial' => [
				'label' => 'Initial Message',
				'body' => <<<TEXT
Dear {customer_name},
This is {am_name} from Lanka Property Web — www.lankapropertyweb.com, Sri Lanka’s No.1 property advertising portal.

Looking to sell or rent your property? Post your ad on Lanka Property Web and reach thousands of potential buyers and tenants across Sri Lanka and overseas.

Why choose us:
🔹 Get free ads on House.lk when you post on Lanka Property Web
🔹 Reach 1M+ visits and 4.8M+ views per month
🔹 Generate over 100,000 leads monthly
🔹 18 years of trusted service, exclusively for properties
🔹 30%+ of visitors from overseas
🔹 Ranked No.1 on Google for property searches
🔹 119K+ Facebook followers and strong social media reach

List your property today for maximum exposure and faster results!

Need help? Our friendly team is always ready to assist.
TEXT,
			],
			'paid_ads' => [
				'label' => 'Paid Ads',
				'body' => <<<TEXT
Dear {customer_name},

Your advertisement is now live. You can view it on our websites: www.lankapropertyweb.com and www.house.lk.

Thank you for choosing our service.
TEXT,
			],
			'follow_up' => [
				'label' => 'Follow Up',
				'body' => <<<TEXT
Dear {customer_name},
This is {am_name} from Lanka Property Web.

I’m following up regarding the advertisement for your property that we previously discussed. Within a short time, we’ve successfully helped many property owners achieve great results, and I’d be delighted for you to benefit from the same.

If you’re interested, I can share more details about our advertising packages or guide you through the simple posting process. Your property will gain visibility among a large audience both locally and internationally.

I look forward to hearing from you soon.
TEXT,
			],
			'rna' => [
				'label' => 'RNA',
				'body' => <<<TEXT
Dear {customer_name},
This is {am_name} from Lanka Property Web.

I tried reaching you regarding your property advertisement. Please let me know a convenient time to call, or you can contact me back at your earliest convenience.
TEXT,
			],
			'not_interested' => [
				'label' => 'Not Interested',
				'body' => <<<TEXT
Dear {customer_name},
This is {am_name} from Lanka Property Web.

If you plan to sell or rent your property in the future, please feel free to contact us. We can help you connect with genuine buyers and tenants quickly and efficiently.

We’ll be glad to assist you whenever you’re ready to advertise again.
TEXT,
			],
		];
	}

	private static function buildWhatsappMessage(string $templateKey, $record): ?string
	{
		$templates = self::getWhatsappTemplates();
		if (! isset($templates[$templateKey])) {
			return null;
		}

		$templateBody = $templates[$templateKey]['body'] ?? '';
		if ($templateBody === '') {
			return null;
		}

		$search = ['{am_name}', '{customer_name}'];
		$replace = [
			self::resolveAccountManagerName(),
			self::resolveCustomerName($record),
		];

		return trim(str_replace($search, $replace, $templateBody));
	}

    private static function getMobileOptions($record): array
    {
        if (! $record) {
            return [];
        }

        if (method_exists($record, 'loadMissing')) {
            $record->loadMissing('customer');
        }

        $options = [];

        $append = function ($raw, string $label) use (&$options) {
            self::addMobileOption($options, $raw, $label);
        };

        $lpwDetails = LpwData::getLpwUserDetailsForRecord($record);
        if (! empty($lpwDetails['mobile'])) {
            $append($lpwDetails['mobile'], 'LPW API');
        }

        $customer = $record->customer ?? null;
        if ($customer) {
            // First priority: phones JSON array
            if (!empty($customer->phones) && is_array($customer->phones)) {
                $append($customer->phones, 'Customer Phone');
            }
            
            // Fallback to individual mobile fields if phones array is empty
            if (empty($options)) {
                $append($customer->mobile ?? null, 'Customer Mobile');
                $append($customer->mobile_alt ?? null, 'Customer Alt');
            }
        }

        if (empty($options)) {
            $append($record->mobile ?? null, 'Lead Mobile');
            $append($record->mobile_no ?? null, 'Lead Mobile No');
            $append($record->phone ?? null, 'Lead Phone');
        }

        return $options;
    }

    private static function addMobileOption(array &$options, $value, string $label = ''): void
    {
        foreach (self::extractMobileCandidates($value) as $normalized) {
            $formatted = '0' . $normalized;

            if (isset($options[$formatted])) {
                continue;
            }

            $options[$formatted] = $label ? "{$formatted} ({$label})" : $formatted;
        }
    }

    private static function extractMobileCandidates($value): array
    {
        $candidates = [];

        $values = match (true) {
            is_array($value) => $value,
            $value instanceof \Stringable => [(string) $value],
            is_string($value) => [$value],
            is_null($value) => [],
            default => [(string) $value],
        };

        foreach ($values as $item) {
            if (is_array($item)) {
                $candidates = array_merge($candidates, self::extractMobileCandidates($item));
                continue;
            }

            $string = trim((string) $item);

            if ($string === '') {
                continue;
            }

            $parts = preg_split('/[,\n;\/|]+/', $string) ?: [$string];

            foreach ($parts as $part) {
                $normalized = self::normalizeMobile(trim($part));

                if ($normalized) {
                    $candidates[] = $normalized;
                    continue;
                }

                if (preg_match_all('/\+?\d[\d\s]{8,}/', $part, $matches)) {
                    foreach ($matches[0] as $match) {
                        $nested = self::normalizeMobile(trim($match));
                        if ($nested) {
                            $candidates[] = $nested;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($candidates));
    }

	private static function resolveAccountManagerName(): string
	{
		$user = Auth::user();
		return $user?->username
			?? $user?->name
			?? 'the Lanka Property Web team';
	}

	private static function resolveCustomerName($record): string
	{
		$names = array_filter([
			$record->firstname ?? null,
			$record->surname ?? null,
		]);
		$name = trim(implode(' ', $names));

		if ($name !== '') {
			return $name;
		}

		$customer = $record->customer ?? null;
		$customerNames = array_filter([
			$customer?->firstname ?? null,
			$customer?->surname ?? null,
		]);
		$customerName = trim(implode(' ', $customerNames));

		if ($customerName !== '') {
			return $customerName;
		}

		return 'Customer';
	}

    private static function sendMessageAction(): Action
    {
        return Action::make('send_message')
            ->label('Send Message')
            ->icon('heroicon-s-chat-bubble-bottom-center-text')
            ->color('primary')
            ->modalWidth('xl')
            ->modalHeading('Send WhatsApp Message')
            ->modalSubmitActionLabel('Open WhatsApp Web')
            ->fillForm(function (Action $action): array {
                $record = $action->getRecord();
                $templates = self::getWhatsappTemplates();
                $defaultTemplate = array_key_first($templates);

                $defaultMessage = $defaultTemplate ? self::buildWhatsappMessage($defaultTemplate, $record) : '';
                $mobileOptions = self::getMobileOptions($record);
                $hasStoredNumbers = ! empty($mobileOptions);
                $defaultMobile = $hasStoredNumbers
                    ? array_key_first($mobileOptions)
                    : self::MANUAL_MOBILE_VALUE;

                return [
                    'message_template' => $defaultTemplate,
                    'message_body' => $defaultMessage,
                    'mobile' => $defaultMobile,
                ];
            })
            ->form(function (Action $action) {
                $record = $action->getRecord();

                if ($record && method_exists($record, 'loadMissing')) {
                    $record->loadMissing('customer');
                }

                $mobileOptions = self::getMobileOptions($record);
                $hasStoredNumbers = ! empty($mobileOptions);
                $selectOptions = $hasStoredNumbers
                    ? $mobileOptions
                    : [self::MANUAL_MOBILE_VALUE => 'Enter mobile number manually'];

                return [
                    Select::make('message_template')
                        ->label('Message Template')
                        ->placeholder('Select a message template')
                        ->options(fn () => collect(self::getWhatsappTemplates())->mapWithKeys(fn ($template, $key) => [$key => $template['label']])->toArray())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (callable $set, $state) use ($record) {
                            if (!$state) {
                                $set('message_body', '');
                                return;
                            }
                            
                            $message = self::buildWhatsappMessage($state, $record);
                            $set('message_body', $message ?: '');
                        }),
                    Select::make('mobile')
                        ->label('Mobile Number')
                        ->placeholder($hasStoredNumbers ? 'Select a mobile number' : 'Enter a mobile number')
                        ->options($selectOptions)
                        ->searchable()
                        ->preload()
                        ->helperText($hasStoredNumbers
                            ? 'Pick an existing customer number.'
                            : 'No saved mobile numbers found. Please enter one manually.')
                        ->required()
                        ->reactive(),
                    TextInput::make('manual_mobile')
                        ->label('Manual Mobile Number')
                        ->placeholder('0XXXXXXXXX')
                        ->visible(fn (callable $get) => $get('mobile') === self::MANUAL_MOBILE_VALUE)
                        ->required(fn (callable $get) => $get('mobile') === self::MANUAL_MOBILE_VALUE)
                        ->helperText('Provide a Sri Lankan mobile number starting with 0.'),
                    Textarea::make('message_body')
                        ->label('Message')
                        ->rows(12)
                        ->helperText('Review and personalize the template before sending.')
                        ->required(),
                ];
            })
            ->action(function (array $data, $record, Action $action) {
                $templateKey = $data['message_template'] ?? null;
                $messageBody = trim($data['message_body'] ?? '');
                $selectedMobile = $data['mobile'] ?? null;
                if ($selectedMobile === self::MANUAL_MOBILE_VALUE) {
                    $selectedMobile = $data['manual_mobile'] ?? null;
                }
                $mobileNine = self::normalizeMobile($selectedMobile);

                if (! $templateKey) {
                    Notification::make()
                        ->title('Template is required')
                        ->danger()
                        ->body('Please select a WhatsApp template to send.')
                        ->send();
                    return;
                }

                if (! $mobileNine) {
                    Notification::make()
                        ->title('No valid mobile found')
                        ->danger()
                        ->body('Please choose a valid mobile number (format 0XXXXXXXXX).')
                        ->send();
                    return;
                }

                if ($messageBody === '') {
                    $messageBody = self::buildWhatsappMessage($templateKey, $record) ?? '';
                }

                if ($messageBody === '') {
                    Notification::make()
                        ->title('Template unavailable')
                        ->danger()
                        ->body('The selected template could not be prepared. Please try again.')
                        ->send();
                    return;
                }

                $message = $messageBody;

                if (! $message) {
                    Notification::make()
                        ->title('Message is required')
                        ->danger()
                        ->body('Please review the template message and ensure it is not empty before sending.')
                        ->send();
                    return;
                }

                if (! preg_match('/^\d{9}$/', $mobileNine)) {
                    Notification::make()
                        ->title('Invalid mobile number')
                        ->danger()
                        ->body('The selected mobile number must contain exactly 9 digits after removing the leading 0 or country code.')
                        ->send();
                    return;
                }

                $query = http_build_query([
                    'phone' => '94' . $mobileNine,
                    'text' => $message,
                    'type' => 'custom_url',
                    'app_absent' => '0',
                ]);

                $whatsAppUrl = 'https://api.whatsapp.com/send/?' . $query;

                if ($livewire = $action->getLivewire()) {
                    if (method_exists($livewire, 'dispatch')) {
                        $livewire->dispatch('lpw-open-whatsapp', url: $whatsAppUrl);
                    } elseif (method_exists($livewire, 'dispatchBrowserEvent')) {
                        $livewire->dispatchBrowserEvent('lpw-open-whatsapp', [
                            'url' => $whatsAppUrl,
                        ]);
                    } elseif (method_exists($livewire, 'js')) {
                        $encodedUrl = Js::from($whatsAppUrl);
                        $livewire->js(<<<JS
window.dispatchEvent(new CustomEvent('lpw-open-whatsapp', { detail: { url: {$encodedUrl} } }));
JS
                        );
                    }
                }

                Notification::make()
                    ->title('Opening WhatsApp Web')
                    ->success()
                    ->body('We are opening WhatsApp Web for 0' . $mobileNine . '.')
                    ->send();
            });
    }

	private static function getShowMoreActivitiesAction(): Action
	{
		return Action::make('show_more_activities')
			->label('Show more')
			->icon('heroicon-o-ellipsis-horizontal')
			->color('gray')
			// ->lazy()
			->modalWidth('4xl')
			->modalHeading('All Activities')
			->modalSubmitAction(false)
			->modalCancelActionLabel('Close')
			->action(function ($record, $action) {
				$activities = $record->activities()
					->with('user', 'paymentStatus')
					->latest()
					->skip(4)
					->take(50)
					->get();

				$action->modalContent(view('filament.activities.more', [
					'activities' => $activities,
				]));
			});
	}

    public static function getAddActivityAction(): Action
    {
        return Action::make('add_activity')
            ->label('Add Activity')
            ->modalWidth('xl')
            ->button()
            ->icon('heroicon-o-plus')
            ->color('primary')
                ->form([
                    Radio::make('activity_type')
                        ->label('Activity Type')
                        ->inline()
                        ->options([
                            'email' => 'Email',
                            'call' => 'Call',
                            'meeting' => 'Meeting',
                            'whatsapp' => 'WhatsApp',
                        ])
                        ->required()
                        ->reactive(),

                    // Radio button for status
                    Radio::make('status')
                        ->label('Status')
                        // Options from status column in the payment_status table   
                        ->options(function () {
                            return PaymentStatus::all()->pluck('status', 'id')->toArray();
                        })
                        ->searchable(),

                    // Select for sub status
                    Select::make('sub_status')
                        ->label('Sub Status')
                        // Options from sub_status column in the payment_status table
                        ->options(function () {
                            return PaymentStatus::all()->pluck('sub_status', 'id')->toArray();
                        })
                        ->searchable(),

                    Radio::make('follow_up_type')
                        ->label('Option')
                        ->inline()
                        ->options([
                            'follow_up' => 'Follow Up',
                            'reminder' => 'Reminder',
                        ]),

                    // Select::make('funnel_id')
                    //     ->label('Funnel (category - stage)')
                    //     ->options(function (callable $get) {
                    //         $paymentStatusId = (int) $get('payment_status_id');
                    
                    //         // Determine which funnel IDs to show
                    //         if (in_array($paymentStatusId, [2, 3])) {
                    //             $allowedIds = [1, 2, 3, 4, 5, 6, 7];
                    //         } elseif (in_array($paymentStatusId, [1, 15, 14])) {
                    //             $allowedIds = [8, 9, 10];
                    //         } elseif (in_array($paymentStatusId, [4, 9, 10])) {
                    //             $allowedIds = [11, 12];
                    //         } else {
                    //             $allowedIds = []; // empty means show all
                    //         }
                    
                    //         $query = Funnel::query()
                    //             ->orderBy('category')
                    //             ->orderBy('stage');
                    
                    //         if (!empty($allowedIds)) {
                    //             $query->whereIn('id', $allowedIds);
                    //         }
                    
                    //         return $query->get()
                    //             ->mapWithKeys(fn ($funnel) => [
                    //                 $funnel->id => ucfirst($funnel->category) . ' - Stage ' . $funnel->stage
                    //             ])
                    //             ->toArray();
                    //     })
                    //     ->searchable()
                    //     ->required()
                    //     ->reactive()
                    //     ->hint('Filtered by payment status'),


                    DateTimePicker::make('follow_up_date_time')
                        ->label('Follow Up Date & Time')
                        ->visible(fn ($get) => $get('follow_up_type') === 'follow_up')
                        ->required(fn ($get) => $get('follow_up_type') === 'follow_up')
                        ->reactive(),

                    DatePicker::make('reminder_date')
                        ->label('Reminder Date')
                        ->visible(fn ($get) => $get('follow_up_type') === 'reminder')
                        ->required(fn ($get) => $get('follow_up_type') === 'reminder')
                        ->reactive(),

                    Textarea::make('comments')
                        ->label('Comments')
                        ->rows(4),
                ])
                ->action(function (array $data, $record) {
                    $activity = $record->activities()->create([
                        'activity_type'     => $data['activity_type'] ?? null,
                        'funnel_id'         => $data['funnel_id'] ?? null,
                        'payment_status_id' => $data['payment_status_id'] ?? null,
                        'comments'          => $data['comments'] ?? null,
                        'assigned_by'       => Auth::id(),
                    ]);

                    $followUpProvided = ! empty($data['follow_up_date_time']);
                    $reminderProvided = ! empty($data['reminder_date']);

                    if ($activity && ($followUpProvided || $reminderProvided)) {
                        $payload = [
                            'activity_id'    => $activity->id,
                            'follow_up_time' => $followUpProvided ? $data['follow_up_date_time'] : null,
                            'reminder_at'    => $reminderProvided ? $data['reminder_date'] : null,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];

                        DB::table('activity_follow_up')->insert($payload);
                    }

                    Notification::make()->title('Activity added')->success()->send();
            });
    }

    private static function getActivityListSchema(): array
    {
        return [
            Section::make('Activity List')
				->lazy()
				->collapsible()
				->heading(function ($record) {
					$activityType = ucfirst($record->activity_type ?? 'Activity');
					return match ($record->activity_type) {
						'email' => '✉️ Email Activity',
						'meeting' => '📅 Meeting',
						'site_visit' => '🏠 Site Visit',
						'message' => '💬 Message',
						'follow_up' => '🔄 Follow Up',
						'call' => '📞 Call',
						default => "📋 {$activityType}",
					};
				})
				->description(function ($record) {
					$date = $record->created_at ? $record->created_at->format('M d, Y H:i') : 'No date';
					$user = User::find($record->assigned_by)?->username ?? 'Unknown';
					$paymentStatus = PaymentStatus::find($record->payment_status_id)?->status ?? 'Not found';
					$subStatus = PaymentStatus::find($record->payment_status_id)?->sub_status ?? 'Not found';
					$funnel = Funnel::find($record->funnel_id)?->category ?? 'Not found';
					$stage = Funnel::find($record->funnel_id)?->stage ?? 'Not found';
					return "{$date} | By: {$user} | Payment Status: {$paymentStatus} - {$subStatus} | Funnel: {$funnel} - {$stage}";
				})
				->schema([
					TextEntry::make('activity_type')->label('Activity Type')->weight('bold'),
					TextEntry::make('comments')->label('Comments')->placeholder('No comments'),
					TextEntry::make('created_at')->label('Date')->dateTime('M d, Y H:i'),
					// $user = User::find($record->assigned_by)?->username ?? 'Unknown'; This username should be displayed as Done By
					TextEntry::make('user.username')->label('Done By')->icon('heroicon-o-user'),
					TextEntry::make('paymentStatus.status')->label('Payment Status')->badge(),
					TextEntry::make('paymentStatus.sub_status')->label('Sub Status')->badge(),
				])
				->columns(3)
				->collapsed(),
        ];
    }

    // create pastActivity function.

    private static function createActivityTab(string $label, string $icon, callable $queryModifier): Tab
    {
        return Tab::make($label)
            ->lazy()
            ->icon($icon)
            ->schema([
                RepeatableEntry::make('activities')
                    ->lazy()
                    ->label($label)
                    ->getStateUsing(function ($record) use ($queryModifier) {
                        $query = $record->activities()->with('user', 'paymentStatus');
                        $queryModifier($query);
                        return $query->latest()->limit(5)->get();
                    })
                    ->schema(self::getActivityListSchema()),
            ]);
    }

    public static function getRecordActions(): array
    {
        $viewAction = ViewAction::make()
            ->label('')
            // ->lazy()
            ->icon('heroicon-o-eye')
            ->modalHeading(fn($record) => 'Property Details - ' . $record->heading)
            ->modalWidth('6xl')
            ->visible(fn($record) => Gate::allows('view', $record))
            ->closeModalByClickingAway(false)
            // ->closedByClickingAway(false)
            ->schema([
                Tabs::make('PropertyTabs')
                    ->persistTabInQueryString()
                    ->lazy()
                    ->tabs([
                    Tab::make('Overview')->icon('heroicon-o-information-circle')->lazy()->schema([
                        // Add a section to display contact details from the function self::contactDetailsSection()
                        self::contactDetailsSectionForOverview(),
                            // Section::make('Description')->schema([
                            //     TextEntry::make('desc')->label('Property Description')->placeholder('No description available')->columnSpanFull()->html(),
                            // ]),
                        ]),

                        Tab::make('Property Details')->icon('heroicon-o-information-circle')->schema([
                            Section::make('Property Information')
                            ->lazy()
                            ->schema([
                                RepeatableEntry::make('user_ads')
                                    ->label('')
                                    ->contained(false)
                                    ->lazy()
                                    ->getStateUsing(fn($record) => LpwData::getNormalizedUserAdsForRecord($record))
                                    ->schema([
                                        Section::make(fn($item) => ($item['heading'] ?? 'Property') . (isset($item['city']) && $item['city'] ? " • {$item['city']}" : ''))
                                            // ->collapsible()
                                            // ->collapsed()
                                            ->schema([
                                                TextEntry::make('heading')->label('Property Heading')->columnSpanFull()->size('lg')->weight('bold'),
                                                TextEntry::make('type')->label('Listing Type')->badge(),
                                                TextEntry::make('propty_type')->label('Property Type')->badge(),
                                                TextEntry::make('service_type')->label('Service Type')->badge(),
                                                TextEntry::make('price')->label('Price')->money('LKR')->size('md')->weight('bold')->color('success'),
                                                TextEntry::make('price_type')->label('Price Type'),
                                                TextEntry::make('desc')->label('Property Description')->columnSpanFull()->html()->placeholder('No description available'),
                                                TextEntry::make('street')->label('Street Address')->placeholder('Not specified'),
                                                TextEntry::make('city')->label('City')->icon('heroicon-o-map-pin'),
                                                TextEntry::make('lat')->label('Latitude')->placeholder('Not specified'),
                                                TextEntry::make('lng')->label('Longitude')->placeholder('Not specified'),

                                            ])
                                            ->columns(6),
                                    ]),
                            ])
                            ->headerActions([
                                Action::make('view_ad')
                                    ->label('View Ad')
                                    ->icon('heroicon-o-link')
                                    ->color('primary')
                                    ->url(fn($record) => $record->ad_url)
                                    ->openUrlInNewTab(),
                                    // ->visible(fn($record) => !empty($record->ad_url)),
                                // Action::make('view_customer_ads')
                                //     ->label('View Customer Ads')
                                //     ->icon('heroicon-o-newspaper')
                                //     ->color('primary')
                                //     ->url(fn($record) => HuntersResource::getUrl('customer-ads', ['record' => $record->cust_id]))
                                //     // ->openUrlInNewTab()
                                //     ->visible(fn($record) => !empty($record->cust_id)),
                            ]),
                        ]),

                        Tab::make('Activity')->lazy()->icon('heroicon-o-clipboard-document-list')->schema([
                            Tabs::make('ActivitySubTabs')->tabs([
                                Tab::make('All Activities')->lazy()->icon('heroicon-o-queue-list')->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('All Activities')
                                            ->icon('heroicon-o-queue-list')
                                            ->columnSpan(2)
                                            ->description('All activities of the customer.')
                                            ->headerActions([
                                                RecordActions::getAddActivityAction(),
                                            ])
                                            ->schema(self::allActivitiesSection()),
                                    ])
                                ]),

                                Tab::make('My Activities')->lazy()->icon('heroicon-o-user')->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('My Activities')
                                            ->icon('heroicon-o-user')
                                            ->columnSpan(2)
                                            ->description('Activities assigned by me.')
                                            ->headerActions([
                                                RecordActions::getAddActivityAction(),
                                            ])
                                            ->schema(self::myActivitiesSection()),
                                    ])
                                ]),

                                Tab::make('Call Activities')->lazy()->icon('heroicon-o-phone')->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Call Activities')
                                            ->icon('heroicon-o-phone')
                                            ->columnSpan(2)
                                            ->description('Call activities of the customer.')
                                            ->headerActions([
                                                RecordActions::getAddActivityAction(),
                                            ])
                                            ->schema(self::callActivitiesSection()),
                                    ])
                                ]),

                                Tab::make('Old Activities')->lazy()->icon('heroicon-o-clock')->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Old Activities')
                                            ->icon('heroicon-o-clock')
                                            ->columnSpan(2)
                                            ->description('Old activities of the customer.')
                                            ->schema(self::oldActivitiesSection()),
                                    ])
                                ]),
                            ]),
                        ]),

                        Tab::make('Calls')->icon('heroicon-o-phone')->lazy()->schema([
                            // Add 3 tabs for call logs, stats, and call scripts.
                            Tabs::make('CallSubTabs')->lazy()->tabs([
                                Tab::make('Call Logs')->lazy()->icon('heroicon-o-list-bullet')->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Call Logs')
                                            ->icon('heroicon-s-phone-arrow-up-right')
                                            ->lazy()
                                            ->columnSpan(2)
                                            ->description('Call logs Details')
                                            ->headerActions([
                                                RecordActions::getAddActivityAction(),
                                            ])
                                            ->schema(self::callLogSection()),
                                    ]),
                                ]),
                                Tab::make('Stats')->lazy()->icon('heroicon-s-chart-bar-square')->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        // Total Activities Stat
                                        Section::make()
                                            ->schema([
                                                TextEntry::make('total_activities')
                                                    ->label('Total Activities')
                                                    ->icon('heroicon-s-clipboard-document-list')
                                                    ->size('lg')
                                                    ->weight('bold')
                                                    ->color('success')
                                                    ->getStateUsing(fn($record) => $record->activities()->count()),
                                            ])
                                            ->columnSpan(1),

                                        // Recent Calls Stat
                                        Section::make()
                                            ->schema([
                                                TextEntry::make('recent_calls')
                                                    ->label('Calls (Last 30 Days)')
                                                    ->icon('heroicon-s-phone')
                                                    ->size('lg')
                                                    ->weight('bold')
                                                    ->color('primary')
                                                    ->getStateUsing(function ($record) {
                                                        return $record->activities()
                                                            ->where('stage', 'call')
                                                            ->where('created_at', '>=', now()->subDays(30))
                                                            ->count();
                                                    }),
                                            ])
                                            ->columnSpan(1),

                                        // Average Score Stat
                                        Section::make()
                                            ->schema([
                                                TextEntry::make('avg_score')
                                                    ->label('Average Lead Score')
                                                    ->icon('heroicon-s-star')
                                                    ->size('lg')
                                                    ->weight('bold')
                                                    ->color('warning')
                                                    ->getStateUsing(function ($record) {
                                                        $avg = $record->activities()
                                                            ->whereNotNull('level_score')
                                                            ->avg('level_score');
                                                        return $avg ? number_format($avg, 1) . '/10' : 'N/A';
                                                    }),
                                            ])
                                            ->columnSpan(1),
                                    ]),

                                    Section::make('API Stats')
                                        ->schema([
                                            RepeatableEntry::make('api_status')
                                                ->label('')
                                                ->contained(false)
                                                ->getStateUsing(fn($record) => LpwData::getUserStatsForAdsNormalized($record))
                                                ->schema([
                                                    // TextEntry::make('label')
                                                    //     ->label('')
                                                    //     ->formatStateUsing(function ($state, $record) {
                                                    //         $label = is_array($record) ? ($record['label'] ?? $state ?? '') : ($record?->label ?? $state ?? '');
                                                    //         $value = is_array($record) ? ($record['value'] ?? '') : ($record?->value ?? '');
                                                    //         return $label . ': ' . $value;
                                                    //     })
                                                    //     ->columnSpanFull(),
                                                ]),
                                        ])
                                        ->columnSpanFull(),

                                    Section::make('Charts & Analytics')
                                        ->schema([
                                            ComponentsGrid::make(3)->schema([
                                                ViewEntry::make('activities_by_type_chart')
                                                    ->label('')
                                                    ->view('filament.widgets.inline')
                                                    ->viewData(fn($record) => [
                                                        'widgetClass' => ActivitiesByTypeChart::class,
                                                        'leadId' => $record?->id,
                                                    ]),
                                                ViewEntry::make('funnel_chart')
                                                    ->label('')
                                                    ->view('filament.widgets.inline')
                                                    ->columnSpan(2)
                                                    ->viewData(fn($record) => [
                                                        'widgetClass' => FunnelChart::class,
                                                        'leadId' => $record?->id,
                                                    ]),
                                                    
                                            ]),
                                            ComponentsGrid::make(1)->schema([
                                                ViewEntry::make('activities_over_time_chart')
                                                    ->label('')
                                                    ->view('filament.widgets.inline')
                                                    ->viewData(fn($record) => [
                                                        'widgetClass' => ActivitiesOverTimeChart::class,
                                                        'leadId' => $record?->id,
                                                    ]),
                                            ]),
                                        ])
                                        ->columnSpanFull(),

                                    // Activity Summary by Date Range
                                    Section::make('Activity Summary')
                                        ->icon('heroicon-s-calendar-days')
                                        ->description('Activities distribution over time')
                                        ->collapsible()
                                        ->lazy()
                                        ->schema([
                                            ComponentsGrid::make(4)->schema([
                                                TextEntry::make('today_activities')
                                                    ->label('Today')
                                                    ->icon('heroicon-s-clock')
                                                    ->badge()
                                                    ->color('success')
                                                    ->getStateUsing(function ($record) {
                                                        return $record->activities()
                                                            ->whereDate('created_at', today())
                                                            ->count();
                                                    }),

                                                TextEntry::make('week_activities')
                                                    ->label('This Week')
                                                    ->icon('heroicon-s-calendar')
                                                    ->badge()
                                                    ->color('primary')
                                                    ->getStateUsing(function ($record) {
                                                        return $record->activities()
                                                            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                                                            ->count();
                                                    }),

                                                TextEntry::make('month_activities')
                                                    ->label('This Month')
                                                    ->icon('heroicon-s-calendar-days')
                                                    ->badge()
                                                    ->color('warning')
                                                    ->getStateUsing(function ($record) {
                                                        return $record->activities()
                                                            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                                                            ->count();
                                                    }),

                                                TextEntry::make('all_time_activities')
                                                    ->label('All Time')
                                                    ->icon('heroicon-s-chart-bar')
                                                    ->badge()
                                                    ->color('info')
                                                    ->getStateUsing(function ($record) {
                                                        return $record->activities()->count();
                                                    }),
                                            ]),
                                        ])
                                        ->columnSpanFull(),

                                    // Section to display API stats
                                    
                                    

                                    // Funnel stage progress section
                                    Section::make('Activity Progress')
                                        ->columns(2)
                                        ->icon('heroicon-s-chart-bar-square')
                                        ->description('Funnel stage progress')
                                        ->collapsible()
                                        // ->collapsed()
                                        ->schema([
                                            // Add text entry for represent the completed stages and to do stages.
                                            TextEntry::make('payment_status')
                                                ->label('Payment Status')
                                                ->badge()
                                                ->color('info')
                                                    ->getStateUsing(function ($record) {
                                                        $latestActivity = $record->activities()
                                                            ->with('paymentStatus')
                                                            ->whereNotNull('payment_status_id')
                                                            ->orderByDesc('created_at')
                                                            ->first();

                                                        return $latestActivity?->paymentStatus?->payment_status ?? 'N/A';
                                                    }),
                                        ]),

                                    // Activity Breakdown Section
                                    Section::make('Activity Breakdown')
                                        ->icon('heroicon-s-chart-pie')
                                        ->description('Activities by type')
                                        ->collapsible()
                                        ->schema([
                                            RepeatableEntry::make('activity_stats')
                                                ->label('')
                                                ->contained(false)
                                                ->getStateUsing(function ($record) {
                                                    return $record->activities()
                                                        ->selectRaw('stage, COUNT(*) as count')
                                                        ->groupBy('stage')
                                                        ->get()
                                                        ->map(function ($item) {
                                                            return [
                                                                'type' => ucfirst($item->stage ?? 'Other'),
                                                                'count' => $item->count,
                                                            ];
                                                        })
                                                        ->toArray();
                                                })
                                                ->schema([
                                                    ComponentsGrid::make(2)->schema([
                                                        TextEntry::make('type')
                                                            ->label('Type')
                                                            ->badge()
                                                            ->color('primary'),
                                                        TextEntry::make('count')
                                                            ->label('Count')
                                                            ->badge()
                                                            ->color('success'),
                                                    ]),
                                                ]),
                                        ])
                                        ->columnSpanFull(),
                                ]),
                                Tab::make('Call Script')->icon('heroicon-m-clipboard-document-list')->lazy()->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        self::contactDetailsSection(),
                                        Section::make('Call Transcript')
                                            ->icon('heroicon-m-clipboard-document-list')
                                            ->lazy()
                                            ->columnSpan(2)
                                            ->description('Call script for the customer.')
                                            ->headerActions([
                                                Action::make('call_script')
                                                    ->label('Call Script')
                                                    ->icon('heroicon-o-phone')
                                                    ->color('gray')
                                                    ->url(fn ($record) => route('call.script.sinhala', [
                                                        'uid' => ($record->cust_id ?? $record->customer_id ?? ''),
                                                        'mobile' => ($record->mobile ?? $record->mobile_no ?? ''),
                                                    ]))
                                                    ->extraAttributes(function ($record) {
                                                        $url = route('call.script.sinhala', [
                                                            'uid' => ($record->cust_id ?? $record->customer_id ?? ''),
                                                            'mobile' => ($record->mobile ?? $record->mobile_no ?? ''),
                                                        ]);
                                                        return [
                                                            'onclick' => new HtmlString("
                                                                event.preventDefault();
                                                                event.stopPropagation();
                                                                var popup = window.open(
                                                                    '{$url}',
                                                                    'CallScript',
                                                                    'width=1200,height=800,scrollbars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,status=no'
                                                                );
                                                                if (popup) {
                                                                    popup.focus();
                                                                } else {
                                                                    alert('Please allow popups for this site to view the call script.');
                                                                }
                                                                return false;
                                                            "),
                                                        ];
                                                    }),
                                            ])
                                            ->schema([
                                                // TextEntry::make('call_script_help')
                                                //     ->label('')
                                                //     ->state('Use the Refresh button to open the Call Script page.'),
                                            ]),
                                    ]),
                                ]),
                            ]),
                        ]),

                        Tab::make('Message')->lazy()->icon('heroicon-s-chat-bubble-bottom-center-text')->schema([
                            // Section::make('Send Message')
                            //     ->columns(2)
                            //     ->schema([
                            //         Select::make('message_template')
                            //             ->label('Message Template')
                            //             ->options(function () {
                            //                 return []; // replace with actual options
                            //             })
                            //             ->searchable()
                            //             ->required(), // optional

                            //         Textarea::make('message')
                            //             ->label('Message')
                            //             ->rows(4)
                            //             ->required(), // optional

                            //         ButtonAction::make('send_message')
                            //             ->label('Send Message')
                            //             ->icon('heroicon-o-paper-airplane')
                            //             ->action(function (array $data, $record) {
                            //                 // Handle sending message
                            //                 $template = $data['message_template'] ?? null;
                            //                 $message = $data['message'] ?? null;
                            //                 // send logic here
                            //             }),
                            //     ]),

                            // Send Message Action as a pop-up modal
                            self::sendMessageAction(),

                        ]),

                        Tab::make('Media')->icon('heroicon-o-camera')->schema([
                            Section::make('Media Information')->lazy()->schema([
                                TextEntry::make('pic')->label('Has Pictures')->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')->badge(),
                                TextEntry::make('pic_count')->label('Number of Pictures'),
                                TextEntry::make('youtube_link')->label('YouTube Link')->placeholder('No YouTube link')->formatStateUsing(fn($state) => $state ?: 'No YouTube link'),
                                TextEntry::make('video_link')->label('Video Link')->placeholder('No video link')->formatStateUsing(fn($state) => $state ?: 'No video link'),
                                TextEntry::make('image_360')->label('360° Image')->placeholder('No 360° image')->formatStateUsing(fn($state) => $state ?: 'No 360° image'),
                            ])->columns(2),
                        ]),

                        Tab::make('Payments')->lazy()->icon('heroicon-s-credit-card')->schema([
                            Section::make('Payments Information')->schema([
                                // placeholder for charts
                            ]),
                        ])->columnSpanFull(),

                        Tab::make('Billings')->lazy()->icon('heroicon-s-banknotes')->schema([
                            Section::make('Billings Information')->schema([
                                // placeholder for charts
                            ]),
                        ])->columnSpanFull(),

                        Tab::make('Add-ons')->lazy()->icon('heroicon-s-plus-circle')->schema([
                            Section::make('Add-ons Information')->schema([
                                // placeholder for charts
                            ]),
                        ])->columnSpanFull(),
                    ]),
            ]);

        $viewCallScript = Action::make('viewCallScript')
            ->label('')
            ->icon('heroicon-c-phone')
            ->visible(fn($record) => Gate::allows('view', $record))
            ->url(fn ($record) => route('call.script.sinhala', [
                'uid' => ($record->cust_id ?? $record->customer_id ?? ''),
                'mobile' => ($record->mobile ?? $record->mobile_no ?? ''),
            ]))
            ->extraAttributes(function ($record) {
                $url = route('call.script.sinhala', [
                    'uid' => ($record->cust_id ?? $record->customer_id ?? ''),
                    'mobile' => ($record->mobile ?? $record->mobile_no ?? ''),
                ]);
                return [
                    'onclick' => new HtmlString("
                        event.preventDefault();
                        event.stopPropagation();
                        var popup = window.open(
                            '{$url}',
                            'CallScript',
                            'width=1200,height=800,scrollbars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,status=no'
                        );
                        if (popup) {
                            popup.focus();
                        } else {
                            alert('Please allow popups for this site to view the call script.');
                        }
                        return false;
                    "),
                ];
            });

        $editAction = EditAction::make()
            ->label('')
            ->icon('heroicon-o-pencil')
            ->visible(fn($record) => Gate::allows('update', $record) && optional(Auth::user())->user_level_id !== 1)
            ->slideOver();

        $actionGroup = ActionGroup::make([
            Action::make('toggle_pin')
                ->label(fn($record) => $record->is_pin == 1 ? 'Unpin' : 'Pin')
                ->icon(fn($record) => $record->is_pin == 1 ? 'heroicon-s-bookmark' : 'heroicon-o-bookmark')
                ->color('success')
                ->visible(fn() => in_array(optional(Auth::user())->user_level_id, [2, 4, 5]))
                ->action(function ($record) {
                    $record->update([
                        'is_pin' => $record->is_pin == 1 ? 0 : 1,
                    ]);
                    
                    Notification::make()
                        ->title($record->is_pin == 1 ? 'Unpinned Successfully' : 'Pinned Successfully')
                        ->body('The lead has been ' . ($record->is_pin == 1 ? 'pinned' : 'unpinned') . '.')
                        ->success()
                        ->send();
                }),

            Action::make('toggle_favourite')
                ->label(fn($record) => $record->is_favourite == 1 ? 'Remove from Favourites' : 'Add to Favourites')
                ->icon(fn($record) => $record->is_favourite == 1 ? 'heroicon-s-heart' : 'heroicon-o-heart')
                ->color('warning')
                ->action(function ($record) {
                    $record->update([
                        // If is_favourite is 1, then set it to 0, otherwise set it to 1.
                        'is_favourite' => $record->is_favourite == 1 ? 0 : 1,
                    ]);
                    
                    Notification::make()
                        ->title($record->is_favourite == 1 ? 'Removed from Favourites' : 'Added to Favourites')
                        ->body('The lead has been ' . ($record->is_favourite == 1 ? 'added to favourites' : 'removed from favourites') . '.')
                        ->success()
                        ->send();
                }),

            DeleteAction::make('delete')
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete Property Lead')
                ->modalDescription('Are you sure you want to delete this property lead? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete it')
                // visible to the user_level_id 2, 4, 5.
                ->visible(fn() => optional(Auth::user())->user_level_id == 2 || optional(Auth::user())->user_level_id == 4 || optional(Auth::user())->user_level_id == 5)
                ->successNotificationTitle('Lead Deleted')
                ->after(function () {
                    Notification::make()
                        ->title('Lead Deleted Successfully')
                        ->body('The property lead has been permanently deleted.')
                        ->success()
                        ->send();
                }),
        ])
        ->label('')
        ->icon('heroicon-o-ellipsis-vertical')
        ->size('sm')
        ->color('gray')
        ->button();

        return [$viewAction, $viewCallScript, $editAction, $actionGroup];
    }
}
