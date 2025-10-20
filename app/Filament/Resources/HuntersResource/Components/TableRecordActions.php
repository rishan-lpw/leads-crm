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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
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
use Filament\Support\View\Components\ButtonComponent;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Filament\Resources\HuntersResource\Widgets\ActivityTimelineChart;
use App\Filament\Resources\HuntersResource\Widgets\ActivityTypeChart;
use App\Filament\Resources\HuntersResource\Widgets\ActivityScoreChart;
use App\Filament\Resources\HuntersResource\Widgets\MonthlyActivityChart;
use Livewire\Component as LivewireComponent;
use App\Filament\Resources\HuntersResource\Components\Sections\ContactDetailsSections;
use App\Filament\Resources\HuntersResource\Components\Sections\OldActivitiesSection;
use App\Filament\Resources\HuntersResource\Components\Sections\CallLogSection;
use App\Filament\Resources\HuntersResource\Components\Sections\CallScriptSection;
use App\Filament\Resources\HuntersResource\Components\Tabs\ActivityTabs;
use App\Filament\Resources\HuntersResource\Components\Actions\RecordActions;

class TableRecordActions
{
    /**
     * Fetch and normalize LPW user details for a given lead record.
     * Normalized keys: email, mobile, address, membership_exp_date, payment_exp_date, membership_status, firstname, last_activity
     */
    private static function getLpwUserDetailsForRecord($record): array
    {
        static $cache = [];
        
        $userId = $record->cust_id ?? null;
        if (! $userId) {
            return [];
        }

        if (array_key_exists($userId, $cache)) {
            return $cache[$userId];
        }

        try {
            $service = app(LpwApiService::class);
            $raw = $service->getUserDetails($userId, 10, 2);
            
            // Extract data from nested results structure
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

    /**
     * Fetch call logs for a given lead record from LPW API.
     */
    private static function getCallLogsForRecord($record): array
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
            // Cache for 5 minutes to reduce API calls
            $cacheKey = "call_logs_{$userId}";
            $cached = cache()->get($cacheKey);
            
            if ($cached !== null) {
                return $cache[$userId] = $cached;
            }
            
            $result = $service->getCallLogs($userId, 10, 5);
            cache()->put($cacheKey, $result, 300); // 5 minutes cache
            return $cache[$userId] = $result;
        } catch (\Throwable $e) {
            return $cache[$userId] = [];
        }
    }

    /**
     * Fetch old activities for a given lead record from LPW API.
     */
    private static function getOldActivitiesForRecord($record): array
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
        // Check cache first
        $cacheKey = "old_activities_{$userId}";
        $cached = cache()->get($cacheKey);
        
        if ($cached !== null) {
            return $cache[$userId] = $cached;
        }

        $service = app(\App\Services\LpwApiService::class);
        $response = $service->getOldActivities($userId, 10, 2);

        // Normalize result
        $activities = [];
        if (is_array($response)) {
            if (isset($response['data']) && is_array($response['data'])) {
                $activities = $response['data'];
            } elseif (array_is_list($response)) {
                $activities = $response;
            }
        }

        // Cache for 5 minutes to reduce API calls
        cache()->put($cacheKey, $activities, 300);
        
        // Log useful debug info
        Log::info('getOldActivitiesForRecord: normalized', [
            'user_id' => $userId,
            'count' => count($activities),
        ]);

        // Cache and return a safe array
        return $cache[$userId] = $activities;
    } catch (\Throwable $e) {
        Log::error('getOldActivitiesForRecord: Exception', [
            'user_id' => $userId,
            'message' => $e->getMessage(),
        ]);
        return $cache[$userId] = [];
    }
}


    private static function oldActivitiesSection(): array
    {
        return OldActivitiesSection::build();
    }

    /**
     * Build the reusable Call Log section schema.
     */
    private static function callLogSection(): array
    {
        return CallLogSection::build();
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

    /**
     * Return the first ad (normalized) for the record's user, for display in Property Details tab.
     */
    private static function getFirstUserAdForRecord($record): array
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

        // Map various potential keys from the LPW API to our UI fields
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

    /**
     * Return normalized list of ads for repeatable rendering.
     */
    private static function getNormalizedUserAdsForRecord($record): array
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
    
    /**
     * Fetch call scripts (transcripts) for a given lead record.
     * Uses the same call logs API but extracts transcript data.
     */
    private static function getCallScriptForRecord($record): array
    {
        static $cache = [];
        
        // $userId = $record->cust_id ?? $record->customer_id ?? null;
        // if (!$userId) {
        //     return [];
        // }
        $userId = 4; // Hardcoded as requested
        if (array_key_exists($userId, $cache)) {
            return $cache[$userId];
        }
        
        try {
            $service = app(LpwApiService::class);
            // dd($service);
            // Force clear cache if empty result encountered previously
            Cache::forget("lpw_call_script_{$userId}");
            $raw = $service->getCallScript($userId, 10);
            // dd($raw);
            // Debug: Uncomment to check raw API response
            // dd([
            //     'userId' => $userId,
            //     'raw' => $raw,
            //     'is_array' => is_array($raw),
            //     'count' => is_array($raw) ? count($raw) : 0,
            //     'keys' => is_array($raw) ? array_keys($raw) : null,
            // ]);
            
            Log::info('getCallScriptForRecord: Raw API response', [
                'user_id' => $userId,
                'is_array' => is_array($raw),
                'count' => is_array($raw) ? count($raw) : 0,
                'keys' => is_array($raw) ? array_keys($raw) : null,
            ]);
            
            // Handle malformed or empty response
            if (empty($raw) || !is_array($raw)) {
                Log::warning('getCallScriptForRecord: Empty or invalid response', [
                    'user_id' => $userId,
                    'is_empty' => empty($raw),
                    'is_array' => is_array($raw),
                ]);
                return $cache[$userId] = [];
            }

            // Flatten nested sections like "New leads/ hunters", "Pending Payment", etc.
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
                    // Sometimes the value itself can be text, not array
                    $formatted[] = [
                        'category' => (string) $category,
                        'title' => (string) $category,
                        'content' => (string) $scripts,
                    ];
                }
            }
            // dd($formatted);
            Log::info('getCallScriptForRecord: Formatted data', [
                'user_id' => $userId,
                'formatted_count' => count($formatted),
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

    /**
     * Build the reusable Call Script section schema.
     */
    private static function callScriptSection(): array
    {
        return [
            RepeatableEntry::make('call_scripts')
                ->label('')
                ->contained(false)
                ->lazy()
                ->getStateUsing(function ($record) {
                    $scripts = self::getCallScriptForRecord($record);
                    return $scripts;
                })
                ->schema([
                    Section::make()
                        ->heading(fn($state) => ($state['category'] ?? 'Script') . ' • ' . ($state['title'] ?? ''))
                        ->collapsible()
                        ->schema([
                            TextEntry::make('content')
                                ->label('')
                                ->columnSpanFull()
                                ->formatStateUsing(function ($state, $record) {
                                    $content = is_array($record) ? ($record['content'] ?? '') : ($state ?? '');
                                    
                                    if (empty($content)) {
                                        return new HtmlString('<span class="text-gray-500 italic">No content available</span>');
                                    }
                                    
                                    // Replace newlines with breaks and preserve whitespace
                                    $formatted = nl2br(e($content));
                                    
                                    return new HtmlString("
                                        <div class='bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm leading-relaxed whitespace-pre-wrap'>
                                            {$formatted}
                                        </div>
                                    ");
                                })
                                ->html(),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    private static function contactDetailsSectionForOverview(): Section
    {
        return ContactDetailsSections::forOverview();
    }
    
    /**
     * Build the reusable Contact Details section.
     */
    private static function contactDetailsSection(): Section
    {
        return ContactDetailsSections::forSidePanel();
    }

    private static function sendMessageAction(): Action
    {
        return Action::make('send_message')
            ->label('Send Message')
            ->icon('heroicon-s-chat-bubble-bottom-center-text')
            ->color('primary')
            // ->submitAction(false)
            ->modalWidth('xl')
            ->schema([
                // Section::make('Send Message')
                    // ->columns()
                    // ->schema([
                        Select::make('message_template')
                            ->label('Message Template')
                            ->options(function () {
                                return []; // replace with actual options
                            })
                            ->searchable()
                            ->required(),
                        Textarea::make('message')
                            ->label('Message')
                            ->rows(4)
                            ->required(),
                    // ]),
            ])
            ->action(function (array $data, $record) {
                // Handle sending message
                $template = $data['message_template'] ?? null;
                $message = $data['message'] ?? null;
                // send logic here
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

                    Select::make('payment_status_id')
                        ->label('Payment Status')
                        ->options(function () {
                            return PaymentStatus::all()->pluck('payment_status', 'id')->toArray();
                        })
                        ->searchable()
                        ->required(),

                    Radio::make('follow_up_type')
                        ->label('Option')
                        ->inline()
                        ->options([
                            'follow_up' => 'Follow Up',
                            'reminder' => 'Reminder',
                        ]),

                    Select::make('funnel_id')
                        ->label('Funnel (category - stage)')
                        ->options(function (callable $get) {
                            $paymentStatusId = (int) $get('payment_status_id');
                    
                            // Determine which funnel IDs to show
                            if (in_array($paymentStatusId, [2, 3])) {
                                $allowedIds = [1, 2, 3, 4, 5, 6, 7];
                            } elseif (in_array($paymentStatusId, [1, 15, 14])) {
                                $allowedIds = [8, 9, 10];
                            } elseif (in_array($paymentStatusId, [4, 9, 10])) {
                                $allowedIds = [11, 12];
                            } else {
                                $allowedIds = []; // empty means show all
                            }
                    
                            $query = Funnel::query()
                                ->orderBy('category')
                                ->orderBy('stage');
                    
                            if (!empty($allowedIds)) {
                                $query->whereIn('id', $allowedIds);
                            }
                    
                            return $query->get()
                                ->mapWithKeys(fn ($funnel) => [
                                    $funnel->id => ucfirst($funnel->category) . ' - Stage ' . $funnel->stage
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->hint('Filtered by payment status'),

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
                    $paymentStatus = PaymentStatus::find($record->payment_status_id)?->payment_status ?? 'Not found';
                    $funnel = Funnel::find($record->funnel_id)?->category ?? 'Not found';
                    return "{$date} | By: {$user} | Payment Status: {$paymentStatus} | Funnel: {$funnel}";
                })
                ->schema([
                    TextEntry::make('activity_type')->label('Activity Type')->weight('bold'),
                    TextEntry::make('comments')->label('Comments')->placeholder('No comments'),
                    TextEntry::make('created_at')->label('Date')->dateTime('M d, Y H:i'),
                    TextEntry::make('user.username')->label('Done By')->icon('heroicon-o-user'),
                    TextEntry::make('paymentStatus.payment_status')->label('Payment Status')->badge(),
                ])
                ->columns(3)
                ->collapsed(),
        ];
    }

    // create pastActivity function.

    private static function createActivityTab(string $label, string $icon, callable $queryModifier): Tab
    {
        return Tab::make($label)
            ->icon($icon)
            ->schema([
                RepeatableEntry::make('activities')
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
            ->icon('heroicon-o-eye')
            ->modalHeading(fn($record) => 'Property Details - ' . $record->heading)
            ->modalWidth('6xl')
            ->visible(fn($record) => Gate::allows('view', $record))
            ->closeModalByClickingAway(false)
            ->schema([
                Tabs::make('PropertyTabs')
                    ->persistTabInQueryString()
                    ->tabs([
                    Tab::make('Overview')->icon('heroicon-o-information-circle')->schema([
                        // Add a section to display contact details from the function self::contactDetailsSection()
                        self::contactDetailsSectionForOverview(),
                            // Section::make('Description')->schema([
                            //     TextEntry::make('desc')->label('Property Description')->placeholder('No description available')->columnSpanFull()->html(),
                            // ]),
                        ]),

                        Tab::make('Property Details')->icon('heroicon-o-information-circle')->schema([
                            Section::make('Property Information')
                            ->schema([
                                RepeatableEntry::make('user_ads')
                                    ->label('')
                                    ->contained(false)
                                    ->lazy()
                                    ->getStateUsing(fn($record) => self::getNormalizedUserAdsForRecord($record))
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
                                Action::make('view_customer_ads')
                                    ->label('View Customer Ads')
                                    ->icon('heroicon-o-newspaper')
                                    ->color('primary')
                                    ->url(fn($record) => HuntersResource::getUrl('customer-ads', ['record' => $record->cust_id]))
                                    // ->openUrlInNewTab()
                                    ->visible(fn($record) => !empty($record->cust_id)),
                            ]),
                        ]),

                        Tab::make('Activity')->icon('heroicon-o-clipboard-document-list')->schema([
                Tabs::make('ActivitySubTabs')->tabs([
                                Tab::make('Activity Log')->icon('heroicon-o-list-bullet')->schema([
                                    ComponentsGrid::make(3)->schema([
                                        self::contactDetailsSection(),

                                        Section::make('Activity History')->columnSpan(2)->schema([
                                            Tabs::make('ActivityFilterTabs')
                                                ->persistTabInQueryString('activity_filter')
                                                ->tabs([
                                                    ActivityTabs::createActivityTab('All', 'heroicon-o-queue-list', fn($query) => null),
                                                    ActivityTabs::createActivityTab('My Activities', 'heroicon-o-user', fn($query) => $query->where('assigned_by', Auth::id())),
                                                    ActivityTabs::createActivityTab('Call', 'heroicon-o-phone', fn($query) => $query->where('activity_type', 'call')),
                                                ]),
                                        ])->headerActions([
                                            RecordActions::getAddActivityAction(),
                                        ]),
                                    ]),
                                ]),
                                
                                // Tab::make('Call Log')->icon('heroicon-s-phone-arrow-up-right')->schema([
                                //     ComponentsGrid::make(3)->schema([
                                //         self::contactDetailsSection(),
                                        
                                //         Section::make('Call Logs')
                                //             ->icon('heroicon-s-phone-arrow-up-right')
                                //             ->columnSpan(2)
                                //             ->description('Call logs Details')
                                //             ->headerActions([
                                //                 self::getAddActivityAction(),
                                //             ])
                                //             ->schema(self::callLogSection()),
                                //     ]),
                                // ]),
                                
                                // Tab::make('Call Script')->icon('heroicon-m-clipboard-document-list')->schema([
                                //     ComponentsGrid::make(3)->schema([
                                //         self::contactDetailsSection(),
                                        
                                //         Section::make('Call Scripts')
                                //             ->icon('heroicon-m-clipboard-document-list')
                                //             ->columnSpan(2)
                                //             ->description('Call script for the customer.')
                                //             ->headerActions([
                                //                 self::getAddActivityAction(),
                                //                 Action::make('refresh')
                                //                     ->label('Refresh')
                                //                     ->icon('heroicon-o-arrow-path')
                                //                     ->color('gray')
                                //                     ->action(function ($record) {
                                //                         $userId = $record->cust_id ?? $record->customer_id ?? null;
                                //                         if ($userId) {
                                //                             Cache::forget("lpw_call_script_{$userId}");
                                //                         }
                                //                         Notification::make()->title('Call script refreshed')->success()->send();
                                //                     }),
                                //             ])
                                //             ->schema(self::callScriptSection()),
                                //     ]),
                                // ]),

                                Tab::make('Old Activities')->icon('heroicon-o-clock')->schema([
                                    ComponentsGrid::make(3)->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Old Activities')
                                            ->icon('heroicon-o-clock')
                                            ->columnSpan(2)
                                            ->description('Old activities of the customer.')
                                            ->schema(self::oldActivitiesSection()),
                                    ])
                                    // ->headerActions([
                                    //     self::getAddActivityAction(),
                                    // ]),
                                ]),
                            ]),
                        ]),

                        Tab::make('Calls')->icon('heroicon-o-phone')->schema([
                            // Add 3 tabs for call logs, stats, and call scripts.
                            Tabs::make('CallSubTabs')->tabs([
                                Tab::make('Call Logs')->icon('heroicon-o-list-bullet')->schema([
                                    ComponentsGrid::make(3)->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Call Logs')
                                            ->icon('heroicon-s-phone-arrow-up-right')
                                            ->columnSpan(2)
                                            ->description('Call logs Details')
                                            ->headerActions([
                                                RecordActions::getAddActivityAction(),
                                            ])
                                            ->schema(self::callLogSection()),
                                    ]),
                                ]),
                                Tab::make('Stats')->icon('heroicon-s-chart-bar-square')->schema([
                                    ComponentsGrid::make(3)->schema([
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

                                    // Chart Widgets Section
                                    // Section::make('Charts & Analytics')
                                    //     ->icon('heroicon-s-chart-bar')
                                    //     ->description('Visual representation of activities and trends')
                                    //     ->collapsible()
                                    //     ->schema([
                                    //         ComponentsGrid::make(2)->schema([
                                    //             ViewEntry::make('activity_timeline_chart')
                                    //                 // ->view('filament.widgets.chart-widget-view')
                                    //                 ->viewData(fn($record) => [
                                    //                     'widget' => ActivityTimelineChart::class,
                                    //                     'record' => $record,
                                    //                 ])
                                    //                 ->columnSpan(1),

                                    //             ViewEntry::make('activity_type_chart')
                                    //                 ->view('filament.widgets.chart-widget-view')
                                    //                 ->viewData(fn($record) => [
                                    //                     'widget' => ActivityTypeChart::class,
                                    //                     'record' => $record,
                                    //                 ])
                                    //                 ->columnSpan(1),
                                    //         ]),

                                    //         ComponentsGrid::make(2)->schema([
                                    //             ViewEntry::make('monthly_activity_chart')
                                    //                 // ->view('filament.widgets.chart-widget-view')
                                    //                 ->viewData(fn($record) => [
                                    //                     'widget' => MonthlyActivityChart::class,
                                    //                     'record' => $record,
                                    //                 ])
                                    //                 ->columnSpan(1),

                                    //             ViewEntry::make('activity_score_chart')
                                    //                 ->view('filament.widgets.chart-widget-view')
                                    //                 ->viewData(fn($record) => [
                                    //                     'widget' => ActivityScoreChart::class,
                                    //                     'record' => $record,
                                    //                 ])
                                    //                 ->columnSpan(1),
                                    //         ]),
                                    //     ])
                                    //     ->columnSpanFull(),

                                    // Activity Summary by Date Range
                                    Section::make('Activity Summary')
                                        ->icon('heroicon-s-calendar-days')
                                        ->description('Activities distribution over time')
                                        ->collapsible()
                                        
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

                                    // Activity Breakdown Section
                                    Section::make('Activity Breakdown')
                                        ->icon('heroicon-s-chart-pie')
                                        ->description('Activities by type')
                                        ->collapsible()
                                        ->collapsed()
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
                                Tab::make('Call Script')->schema([
                                    ComponentsGrid::make(3)->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Call Scripts')
                                            ->icon('heroicon-m-clipboard-document-list')
                                            ->columnSpan(2)
                                            ->description('Call script for the customer.')
                                            ->headerActions([
                                                self::getAddActivityAction(),
                                                Action::make('refresh')
                                                    ->label('Refresh')
                                                    ->icon('heroicon-o-arrow-path')
                                                    ->color('gray')
                                                    ->action(function ($record) {
                                                        Cache::forget("lpw_call_script_4");
                                                        Notification::make()->title('Call script refreshed')->success()->send();
                                                    }),
                                            ])
                                            ->schema(CallScriptSection::build()),
                                    ]),
                                ]),
                            ]),
                        ]),

                        Tab::make('Message')->icon('heroicon-s-chat-bubble-bottom-center-text')->schema([
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
                            Section::make('Media Information')->schema([
                                TextEntry::make('pic')->label('Has Pictures')->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')->badge(),
                                TextEntry::make('pic_count')->label('Number of Pictures'),
                                TextEntry::make('youtube_link')->label('YouTube Link')->placeholder('No YouTube link')->formatStateUsing(fn($state) => $state ?: 'No YouTube link'),
                                TextEntry::make('video_link')->label('Video Link')->placeholder('No video link')->formatStateUsing(fn($state) => $state ?: 'No video link'),
                                TextEntry::make('image_360')->label('360° Image')->placeholder('No 360° image')->formatStateUsing(fn($state) => $state ?: 'No 360° image'),
                            ])->columns(2),
                        ]),

                        Tab::make('Payments')->icon('heroicon-s-credit-card')->schema([
                            Section::make('Payments Information')->schema([
                                // placeholder for charts
                            ]),
                        ])->columnSpanFull(),

                        Tab::make('Billings')->icon('heroicon-s-banknotes')->schema([
                            Section::make('Billings Information')->schema([
                                // placeholder for charts
                            ]),
                        ])->columnSpanFull(),

                        Tab::make('Add-ons')->icon('heroicon-s-plus-circle')->schema([
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
            ->modalHeading('Call Script')
            ->modalButton('Close')
            ->modalSubmitAction(false)
            ->action(function ($record, $livewire, $data, $action) {
                $response = Http::get('https://your-api.com/call-script/' . $record->id);

                if ($response->successful()) {
                    $script = $response->json()['script'] ?? 'No script available.';
                } else {
                    $script = '⚠️ Failed to fetch call script.';
                }

                $action->modalHeading("Call Script for Lead #{$record->id}");
                $action->modalContent(view('filament.call-script-modal', ['script' => $script]));
            })
            ->modalWidth('4xl');

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
                        'is_favourite' => $record->is_favourite == 1 ? 0 : 1,
                    ]);
                    
                    Notification::make()
                        ->title($record->is_favourite == 1 ? 'Added to Favourites' : 'Removed from Favourites')
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
