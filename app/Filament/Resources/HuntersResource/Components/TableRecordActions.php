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
	private static function getNormalizedMobileForRecord($record): ?string
	{
		$possible = [
			$record->mobile ?? null,
			$record->mobile_no ?? null,
			$record->phone ?? null,
		];

		foreach ($possible as $raw) {
			$normalized = self::normalizeMobile($raw);
			if ($normalized) {
				return $normalized;
			}
		}

		$userId = $record->cust_id ?? $record->customer_id ?? null;
		if (! $userId) {
			return null;
		}

		try {
			$service = app(LpwApiService::class);
			$raw = $service->getUserDetails((string) $userId, 10, 2);

			$data = [];
			if (isset($raw['results'][0]) && is_array($raw['results'][0])) {
				$data = $raw['results'][0];
			} elseif (is_array($raw) && array_is_list($raw)) {
				$data = $raw[0] ?? [];
			} elseif (is_array($raw)) {
				$data = $raw;
			}

			$candidates = [
				'mobile_no', 'mobile_nos', 'mobile', 'tel', 'telephone', 'phone', 'contact_number',
			];
			foreach ($candidates as $key) {
				$value = data_get($data, $key);
				$normalized = self::normalizeMobile(is_array($value) ? ($value[0] ?? null) : $value);
				if ($normalized) {
					return $normalized;
				}
			}
		} catch (\Throwable $e) {
			// ignore
		}

		return null;
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
                    Radio::make('message_template')
                        ->label('Message Template')
                        ->options([
                            'annex|en' => 'Annex',
                            'final_annex|si_LK' => 'Final Annex',
                            'login_details|en' => 'Login Details',
                            'market_outlook|en_GB' => 'Market Outlook',
                            'market_report|en_US' => 'Market Report',
                            'mor23_is_live|en_US' => 'Mor23 Is Live',
                            'mor23_out_now|en_US' => 'Mor23 Out Now',
                        ])
                        ->required(),
                        
                        Select::make('mobile')
                            ->label('Mobile Number')
							->options(function ($get, $set, $state, $component) {
								$record = $component->getRecord();
								$mobileCandidates = [];

								// From LPW API normalized details
								$details = LpwData::getLpwUserDetailsForRecord($record);
								if (!empty($details['mobile'])) {
									$apiMob = $details['mobile'];
									if (is_array($apiMob)) {
										$mobileCandidates = array_merge($mobileCandidates, $apiMob);
									} else {
										$mobileCandidates[] = $apiMob;
									}
								}

								// From current record columns
								$recordMobiles = [
									$record->mobile ?? null,
									$record->mobile_no ?? null,
									$record->phone ?? null,
								];
								$mobileCandidates = array_merge($mobileCandidates, array_filter($recordMobiles));

								// Normalize, unique, and map to display (0XXXXXXXXX)
								$normalized = [];
								foreach ($mobileCandidates as $raw) {
									$nine = self::normalizeMobile(is_array($raw) ? ($raw[0] ?? null) : $raw);
									if ($nine) {
										$normalized['0' . $nine] = '0' . $nine; // value => label
									}
								}

								return $normalized;
							})
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        // Textarea::make('message')
                        //     ->label('Message')
                        //     ->rows(4)
                        //     ->required(),
                    // ]),
            ])
            ->action(function (array $data, $record) {
                // Handle sending message
                $template = $data['message_template'] ?? null;
                $mobileNine = self::getNormalizedMobileForRecord($record);

                if (! $template) {
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
                        ->body('Could not detect a valid mobile number for WhatsApp.')
                        ->send();
                    return;
                }

                $phoneParam = '94' . $mobileNine;

                $response = Http::withHeaders([
                        'accept' => 'application/json',
                        'X-API-KEY' => 'ipZTsQ6JNnWTbF7Y0PCxz3VjAeJosU5Wi3LsIP0NJmduItGjsP0TAdLbN7X9h9Dgoe2nVQEUaCqizOmlTKGNBJ9luScLMvbHwyjYzaTYmOK3ReWGkfFCJot6WweaV6jr',
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://n8n.srilankaproperty.lk/webhook/whatsapp-send-template', [
                        'phone_num' => $phoneParam,
                        'template' => $template,
                    ]);

                if ($response->successful()) {
                    Notification::make()
                        ->title('WhatsApp message queued')
                        ->success()
                        ->body('Template sent to ' . $phoneParam)
                        ->send();
                } else {
                    Notification::make()
                        ->title('Failed to send WhatsApp')
                        ->danger()
                        ->body('Error: ' . ($response->json('message') ?? $response->body()))
                        ->send();
                }
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
                ->collapsible()
                ->lazy()
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
                                Action::make('view_customer_ads')
                                    ->label('View Customer Ads')
                                    ->icon('heroicon-o-newspaper')
                                    ->color('primary')
                                    ->url(fn($record) => HuntersResource::getUrl('customer-ads', ['record' => $record->cust_id]))
                                    // ->openUrlInNewTab()
                                    ->visible(fn($record) => !empty($record->cust_id)),
                            ]),
                        ]),

                        Tab::make('Activity')->lazy()->icon('heroicon-o-clipboard-document-list')->schema([
                            Tabs::make('ActivitySubTabs')->tabs([
                                    Tab::make('Activity Log')->lazy()->icon('heroicon-o-list-bullet')->schema([
                                        ComponentsGrid::make(3)->schema([
                                            self::contactDetailsSection(),

                                            Section::make('Activity History')->columnSpan(2)->schema([
                                                Tabs::make('ActivityFilterTabs')
                                                    ->persistTabInQueryString('activity_filter')
                                                    ->tabs([
                                                        ActivityTabs::createActivityTab('All', 'heroicon-o-queue-list', fn($query) => null)->lazy(),
                                                        ActivityTabs::createActivityTab('My Activities', 'heroicon-o-user', fn($query) => $query->where('assigned_by', Auth::id()))->lazy(),
                                                        ActivityTabs::createActivityTab('Call', 'heroicon-o-phone', fn($query) => $query->where('activity_type', 'call'))->lazy(),
                                                    ]),
                                            ])->headerActions([
                                                RecordActions::getAddActivityAction(),
                                                // self::getShowMoreActivitiesAction(),
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

                                Tab::make('Old Activities')->lazy()->icon('heroicon-o-clock')->schema([
                                    ComponentsGrid::make(3)->lazy()->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Old Activities')
                                            ->icon('heroicon-o-clock')
                                            ->columnSpan(2)
                                            // ->lazy()
                                            ->description('Old activities of the customer.')
                                            ->schema(self::oldActivitiesSection()),
                                    ])
                                    // ->headerActions([
                                    //     self::getAddActivityAction(),
                                    // ]),
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
                                            // RepeatableEntry::make('api_status')
                                            //     ->label('')
                                            //     ->contained(false)
                                            //     ->getStateUsing(fn($record) => LpwData::getUserStatsForAdsNormalized($record))
                                            //     ->schema([
                                            //         ComponentsGrid::make(2)->schema([
                                            //             TextEntry::make('label')
                                            //                 ->label('Metric')
                                            //                 ->badge()
                                            //                 ->color('primary'),
                                            //             TextEntry::make('value')
                                            //                 ->label('Value')
                                            //                 ->badge()
                                            //                 ->color('success'),
                                            //         ]),
                                            //     ]),
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
                                    // Display the below fields from the return data of getUserStatsForAdsNormalized() function.
                                            // $mapping = [
                                            //     'total_ads' => ['totalAds', 'total_ads'],
                                            //     'active_ads' => ['activeAds', 'active_ads'],
                                            //     'expired_ads' => ['expiredAds', 'expired_ads'],
                                            //     'boosted_ads' => ['boostedAds', 'boosted_ads'],
                                            //     'total_views' => ['totalViews', 'views_total', 'views'],
                                            //     'today_views' => ['todayViews', 'views_today'],
                                            //     'week_views' => ['weekViews', 'views_week'],
                                            //     'month_views' => ['monthViews', 'views_month'],
                                            //     'total_messages' => ['totalMessages', 'messages_total', 'messages'],
                                            // ];
                                    

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
                                                    ->openUrlInNewTab(),
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
            ->modalHeading('Call Script')
            ->modalButton('Close')
            ->modalSubmitAction(false)
            ->schema(CallScriptSection::build())
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
