<?php

namespace App\Filament\Resources\HuntersResource\Components;

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
use Filament\Actions\ButtonAction;
use Filament\Notifications\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Support\View\Components\ButtonComponent;
use Illuminate\Support\HtmlString;

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
                'membership_status' => $normalize(['payment', 'membership_status', 'status']),
                'firstname' => $normalize(['firstname', 'first_name', 'name']),
                'last_activity' => $normalize(['latest_action', 'last_activity', 'last_activity_at', 'latest_activity']),
                'id' => $normalize(['UID', 'uid', 'id', 'user_id']),
                'reg_date' => $normalize(['reg_date', 'registered_at', 'registration_date', 'member_since']),
                'source' => $normalize(['source', 'source_type']),
                'category' => $normalize(['category', 'type']),
                'payment_status' => $normalize(['payment', 'payment_status']),
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
            // dd($service->getCallLogs($userId, 10, 5));
            return $cache[$userId] = $service->getCallLogs($userId, 10, 5);
        } catch (\Throwable $e) {
            return $cache[$userId] = [];
        }
    }

    /**
     * Build the reusable Call Log section schema.
     */
    private static function callLogSection(): array
    {
        return [
            RepeatableEntry::make('call_logs')
            ->label('Call Logs')
            ->contained(false)
            ->getStateUsing(function ($record) {
                $logs = self::getCallLogsForRecord($record);
                if (empty($logs)) {
                    return [];
                }

                return collect($logs)
                    ->map(fn($log) => is_array($log) ? (object) $log : (object) $log)
                    ->values()
                    ->toArray();
            })
            ->schema([
                Section::make('Call Log')
                    ->collapsible()
                    ->collapsed()
                    ->heading(function ($record) {
                        // 🔍 Now `$record` works here (each call log object)
                        // dd($record);
                        if (empty($record)) {
                            return '📞 Call Log';
                        }

                        $datetime = $record->date_time ?? $record->datetime ?? $record->date ?? 'N/A';
                        $talktime = $record->talktime ?? $record->talk_time ?? $record->duration ?? 'N/A';
                        $agent = $record->agent ?? $record->user ?? $record->am ?? 'N/A';
                        $sentiment = $record->sentiment ?? 'N/A';

                        $sentimentIcon = match (strtolower((string) $sentiment)) {
                            'positive' => '😊',
                            'negative' => '😞',
                            'neutral' => '😐',
                            'mixed' => '🤔',
                            default => '📞',
                        };
                        // dd($datetime, $talktime, $agent);
                        return "{$sentimentIcon} {$datetime} • {$talktime} • Agent: {$agent}";
                    })
                    ->description(function ($record) {
                        if (empty($record)) {
                            return 'No data available';
                        }

                        $summary = $record->summary ?? $record->summery ?? '';
                        $status = $record->status ?? 'N/A';
                        $comments = $record->comments ?? $record->comment ?? '';

                        $parts = [];

                        if ($summary) {
                            $parts[] = "Summary: " . (strlen($summary) > 100 ? substr($summary, 0, 100) . '...' : $summary);
                        }

                        if ($status !== 'N/A') {
                            $parts[] = "Status: {$status}";
                        }

                        if ($comments) {
                            $commentPreview = strlen($comments) > 20 ? substr($comments, 0, 20) . '...' : $comments;
                            $parts[] = "Comments: {$commentPreview}";
                        }

                        return !empty($parts) ? implode(' | ', $parts) : 'No description available';
                    })
                    ->schema([
                        ComponentsGrid::make(3)->schema([
                            TextEntry::make('date_time')
                                ->label('Date & Time')
                                ->default('N/A')
                                ->icon('heroicon-o-calendar'),

                            TextEntry::make('talktime')
                                ->label('Talk Time')
                                ->default('N/A')
                                ->icon('heroicon-o-clock'),

                            TextEntry::make('agent')
                                ->label('Agent')
                                ->default('N/A')
                                ->icon('heroicon-o-user'),

                            TextEntry::make('sentiment')
                                ->label('Sentiment')
                                ->badge()
                                ->color(fn($state) => match (strtolower((string)($state ?? ''))) {
                                    'positive' => 'success',
                                    'negative' => 'danger',
                                    'neutral' => 'gray',
                                    'mixed' => 'warning',
                                    default => 'info',
                                })
                                ->default('N/A'),

                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color('info')
                                ->default('N/A'),

                            TextEntry::make('call_type')
                                ->label('Call Type')
                                ->badge()
                                ->color('primary')
                                ->default('N/A'),
                        ]),

                        TextEntry::make('summary')
                            ->label('Summary')
                            ->default('No summary available')
                            ->columnSpanFull(),

                        TextEntry::make('comments')
                            ->label('Comments')
                            ->default('No comments')
                            ->tooltip(fn($state) => is_string($state) && strlen($state) > 20 ? $state : null)
                            ->columnSpanFull(),

                        TextEntry::make('recording_url')
                            ->label('Recording')
                            ->formatStateUsing(fn($state) =>
                                new HtmlString(
                                    $state ? '<audio controls style="width: 100%; max-width: 400px; height: 32px;">
                                        <source src="' . e($state) . '" type="audio/mpeg">
                                        <source src="' . e($state) . '" type="audio/wav">
                                        Your browser does not support the audio element.
                                    </audio>' : 'No recording available'
                                )
                            )
                            ->html()
                            ->visible(fn($state) => !empty($state))
                            ->columnSpanFull(),

                        ComponentsGrid::make(2)->schema([
                            TextEntry::make('caller')
                                ->label('Caller')
                                ->icon('heroicon-o-phone-arrow-up-right')
                                ->default('N/A'),

                            TextEntry::make('receiver')
                                ->label('Receiver')
                                ->icon('heroicon-o-phone-arrow-down-left')
                                ->default('N/A'),
                        ]),
                    ])
                    
                    ->columnSpanFull(),
            ]),
        ];
    }
    
    /**
     * Fetch call scripts (transcripts) for a given lead record.
     * Uses the same call logs API but extracts transcript data.
     */
    private static function getCallScriptForRecord($record): array
    {
        $callLogs = self::getCallLogsForRecord($record);
        
        if (empty($callLogs)) {
            return [];
        }

        // Helper function to extract transcript from different possible field names
        $getTranscript = function ($log) {
            return $log['transcript'] 
                ?? $log['transcription'] 
                ?? $log['text'] 
                ?? $log['script'] 
                ?? $log['conversation'] 
                ?? null;
        };

        // Extract and filter logs that have transcripts
        return collect($callLogs)
            ->filter(fn($log) => !empty($getTranscript($log)))
            ->map(function ($log) use ($getTranscript) {
                return [
                    'transcript' => $getTranscript($log),
                    'date_time' => $log['date_time'] ?? $log['datetime'] ?? $log['date'] ?? 'N/A',
                    'agent' => $log['agent'] ?? $log['user'] ?? $log['am'] ?? 'Unknown',
                    'duration' => $log['talktime'] ?? $log['talk_time'] ?? $log['duration'] ?? 'N/A',
                    'sentiment' => $log['sentiment'] ?? 'N/A',
                    'call_type' => $log['call_type'] ?? 'N/A',
                ];
            })
            ->values()
            ->toArray();
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
                ->getStateUsing(function ($record) {
                    // Get the recent call script and return the recent call script
                    // Arrange the call scripts in descending order of date_time
                    $scripts = self::getCallScriptForRecord($record);
                    if (empty($scripts)) {
                        return [];
                    }

                    return collect($scripts)->sortByDesc('date_time')->toArray();
                    // return the recent call script
                    return $scripts[0];
                    // return the recent call script
                })
                ->schema([
                    Section::make('Call Transcript')
                        ->collapsible()
                        ->collapsed()
                        ->heading(function ($record) {
                            if (empty($record)) {
                                return '📝 Call Transcript';
                            }

                            $datetime = $record->date_time ?? 'N/A';
                            $agent = $record->agent ?? 'Unknown';
                            $duration = $record->duration ?? 'N/A';
                            $sentiment = $record->sentiment ?? 'N/A';

                            $sentimentIcon = match (strtolower((string) $sentiment)) {
                                'positive' => '😊',
                                'negative' => '😞',
                                'neutral' => '😐',
                                'mixed' => '🤔',
                                default => '📝',
                            };

                            return "{$sentimentIcon} {$datetime} • Duration: {$duration} • Agent: {$agent}";
                        })
                        ->description(function ($record) {
                            // Take the recent transcript
                            $transcript = $record->transcript ?? '';
                            if (empty($transcript)) {
                                return 'No transcript available';
                            }

                            if (!empty($transcript)) {
                                return strlen($transcript) > 150 
                                    ? substr($transcript, 0, 150) . '...' 
                                    : $transcript;
                            }

                            // Show first 150 characters as preview
                            return 'No transcript available';
                        })
                        ->schema([
                            ComponentsGrid::make(3)->schema([
                                TextEntry::make('date_time')
                                    ->label('Date & Time')
                                    ->default('N/A')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('agent')
                                    ->label('Agent')
                                    ->default('Unknown')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('duration')
                                    ->label('Duration')
                                    ->default('N/A')
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('sentiment')
                                    ->label('Sentiment')
                                    ->badge()
                                    ->color(fn($state) => match (strtolower((string)($state ?? ''))) {
                                        'positive' => 'success',
                                        'negative' => 'danger',
                                        'neutral' => 'gray',
                                        'mixed' => 'warning',
                                        default => 'info',
                                    })
                                    ->default('N/A')
                                    ->visible(fn($state) => $state && $state !== 'N/A'),

                                TextEntry::make('call_type')
                                    ->label('Call Type')
                                    ->badge()
                                    ->color('primary')
                                    ->default('N/A')
                                    ->visible(fn($state) => $state && $state !== 'N/A'),
                            ]),

                            TextEntry::make('transcript')
                                ->label('Full Transcript')
                                ->default('No transcript available')
                                ->columnSpanFull()
                                ->formatStateUsing(function ($state) {
                                    if (!$state) {
                                        return new HtmlString('<span class="fi-text-gray-500 italic">No transcript available</span>');
                                    }

                                    // Preserve paragraph spacing and format nicely
                                    $formatted = nl2br(e($state));
                                    
                                    return new HtmlString("
                                        <div class='fi-bg-gray-50 fi-border fi-border-gray-200 fi-rounded-xl fi-p-4 fi-text-sm fi-leading-relaxed'>
                                            {$formatted}
                                        </div>
                                    ");
                                })
                                ->html(),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }
    
    /**
     * Build the reusable Contact Details section.
     */
    private static function contactDetailsSection(): Section
    {
        return Section::make(fn($record) => self::getLpwUserDetailsForRecord($record)['firstname'] ?? ($record->customer->firstname ?? 'Contact Details'))
            ->icon('iconsax-bul-profile-circle')
            ->schema([
                TextEntry::make('lpw_email')
                    ->label('')
                    ->icon('heroicon-s-envelope')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['email'] ?? ($record->customer->email ?? 'N/A')),

                TextEntry::make('lpw_mobile')
                    ->label('')
                    ->icon('heroicon-s-phone')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['mobile'] ?? ($record->customer->mobile ?? 'N/A')),

                TextEntry::make('lpw_address')
                    ->label('')
                    ->icon('heroicon-s-map-pin')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['address'] ?? ($record->customer->address ?? 'N/A')),

                TextEntry::make('lpw_id')
                    ->label('ID')
                    ->icon('heroicon-s-identification')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['id'] ?? ($record->customer->id ?? 'N/A')),

                TextEntry::make('lpw_reg_date')
                    ->label('Registration Date')
                    ->icon('heroicon-s-calendar')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['reg_date'] ?? ($record->customer->reg_date ?? 'N/A')),

                TextEntry::make('lpw_source')
                    ->label('Source')
                    ->icon('heroicon-s-arrow-path-rounded-square')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['source'] ?? ($record->customer->source ?? 'N/A')),

                TextEntry::make('lpw_category')
                    ->label('Category')
                    ->icon('heroicon-s-tag')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['category'] ?? ($record->customer->category ?? 'N/A')),

                TextEntry::make('lpw_payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->icon('heroicon-s-credit-card')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['payment_status'] ?? ($record->customer->payment_status ?? 'N/A')),

                TextEntry::make('lpw_payment_exp_date')
                    ->label('Payment Expiry Date')
                    ->icon('heroicon-s-calendar')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['payment_exp_date'] ?? ($record->customer->payment_exp_date ?? 'N/A')),

                TextEntry::make('lpw_latest_commented_at')
                    ->label('Latest Commented At')
                    ->icon('heroicon-s-calendar')
                    ->getStateUsing(fn($record) => self::getLpwUserDetailsForRecord($record)['latest_commented_at'] ?? ($record->customer->latest_commented_at ?? 'N/A')),
            ]);
    }

    private static function getAddActivityAction(): Action
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
                        ->options(function () {
                            return Funnel::orderBy('category')
                                ->orderBy('stage')
                                ->get()
                                ->mapWithKeys(function ($funnel) {
                                    return [$funnel->id => ucfirst($funnel->category) . ' - Stage ' . $funnel->stage];
                                })
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),

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
                        'assigned_by'       => auth()->id(),
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
                        Section::make('Property Information')->schema([
                            TextEntry::make('heading')->label('Property Heading')->size('lg')->weight('bold'),
                            TextEntry::make('type')->label('Listing Type')->badge(),
                            TextEntry::make('propty_type')->label('Property Type')->badge(),
                            TextEntry::make('service_type')->label('Service Type')->badge(),
                            TextEntry::make('price')->label('Price')->money('LKR')->size('lg')->weight('bold')->color('success'),
                            TextEntry::make('price_type')->label('Price Type'),
                        ])->columns(3),

                            Section::make('Location')->schema([
                                TextEntry::make('street')->label('Street Address')->placeholder('Not specified'),
                                TextEntry::make('city')->label('City')->icon('heroicon-o-map-pin'),
                                TextEntry::make('lat')->label('Latitude')->placeholder('Not specified'),
                                TextEntry::make('lng')->label('Longitude')->placeholder('Not specified'),
                            ])->columns(3),

                            Section::make('Description')->schema([
                                TextEntry::make('desc')->label('Property Description')->placeholder('No description available')->columnSpanFull()->html(),
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
                                                    self::createActivityTab('All', 'heroicon-o-queue-list', fn($query) => null),
                                                    self::createActivityTab('My Activities', 'heroicon-o-user', fn($query) => $query->where('assigned_by', auth()->id())),
                                                    self::createActivityTab('Call', 'heroicon-o-phone', fn($query) => $query->where('activity_type', 'call')),
                                                ]),
                                        ])->headerActions([
                                            self::getAddActivityAction(),
                                        ]),
                                    ]),
                                ]),
                                
                                Tab::make('Call Log')->icon('heroicon-s-phone-arrow-up-right')->schema([
                                    ComponentsGrid::make(3)->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Call Logs')
                                            ->icon('heroicon-s-phone-arrow-up-right')
                                            ->columnSpan(2)
                                            ->description('Call logs Details')
                                            ->headerActions([
                                                self::getAddActivityAction(),
                                                // Action::make('refresh')
                                                //     ->label('Refresh')
                                                //     ->icon('heroicon-o-arrow-path')
                                                //     ->color('gray')
                                                //     ->action(fn() => null),
                                            ])
                                            ->schema(self::callLogSection()),
                                    ]),
                                ]),
                                
                                Tab::make('Call Script')->icon('heroicon-m-clipboard-document-list')->schema([
                                    ComponentsGrid::make(3)->schema([
                                        self::contactDetailsSection(),
                                        
                                        Section::make('Call Scripts')
                                            ->icon('heroicon-m-clipboard-document-list')
                                            ->columnSpan(2)
                                            ->description('Call transcripts from conversations')
                                            ->headerActions([
                                                self::getAddActivityAction(),
                                                Action::make('refresh')
                                                    ->label('Refresh')
                                                    ->icon('heroicon-o-arrow-path')
                                                    ->color('gray')
                                                    ->action(fn() => null),
                                            ])
                                            ->schema(self::callScriptSection()),
                                    ]),
                                ]),
                            ]),
                        ]),

                        Tab::make('Message')->icon('heroicon-s-chat-bubble-bottom-center-text')->schema([
                            Section::make('Send Message')->schema([
                                Select::make('message_template')
                                    ->label('Message Template')
                                    ->options(function () {
                                        return [];
                                    })
                                    ->searchable(),
                                Textarea::make('message')
                                    ->label('Message')
                                    ->rows(4),
                                ButtonAction::make('send_message')
                                    ->label('Send Message')
                                    ->button()
                                    ->icon('heroicon-o-paper-airplane'),
                            ])->columns(2),
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

                        Tab::make('Stats')->icon('heroicon-s-chart-bar-square')->schema([
                            Section::make('Statistical Information')->schema([
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
            ->visible(fn($record) => Gate::allows('update', $record) && optional(auth()->user())->user_level_id !== 1)
            ->slideOver();

        $actionGroup = ActionGroup::make([
            Action::make('toggle_pin')
                ->label(fn($record) => $record->is_active == 1 ? 'Unpin' : 'Pin')
                ->icon(fn($record) => $record->is_active == 1 ? 'heroicon-s-bookmark' : 'heroicon-o-bookmark')
                ->color('success')
                ->visible(fn() => in_array(auth()->user()->user_level_id, [2, 4, 5]))
                ->action(function ($record) {
                    $record->update([
                        'is_active' => $record->is_active == 1 ? 0 : 1,
                    ]);
                    
                    Notification::make()
                        ->title($record->is_active == 1 ? 'Pinned Successfully' : 'Unpinned Successfully')
                        ->body('The lead has been ' . ($record->is_active == 1 ? 'pinned' : 'unpinned') . '.')
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
                ->visible(fn() => auth()->user()->user_level_id == 2 || auth()->user()->user_level_id == 4 || auth()->user()->user_level_id == 5)
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
