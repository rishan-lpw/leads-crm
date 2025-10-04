<?php

namespace App\Filament\Resources\HuntersResource\Components;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
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
use Filament\Actions\ButtonAction;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Support\View\Components\ButtonComponent;
use Illuminate\Support\HtmlString;

class TableRecordActions
{
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
                    $user = User::find($record->assigned_by)->username ?? 'Unknown';
                    return "{$date} • By: {$user}";
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
            ->schema([
                Tabs::make('PropertyTabs')->tabs([
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
                                    Section::make('Contact Details')->icon('iconsax-bul-profile-circle')->schema([
                                        TextEntry::make('customer.name'),
                                        TextEntry::make('customer.email'),
                                        TextEntry::make('customer.mobile'),
                                        TextEntry::make('customer.address'),
                                        TextEntry::make('customer.membership_exp_date'),
                                        TextEntry::make('customer.payment_exp_date'),
                                        TextEntry::make('customer.membership_status'),
                                    ]),

                                    Section::make('Activity History')->columnSpan(2)->schema([
                                        Tabs::make('ActivityFilterTabs')->tabs([
                                            self::createActivityTab('All', 'heroicon-o-queue-list', fn($query) => null),
                                            self::createActivityTab('My Activities', 'heroicon-o-user', fn($query) => $query->where('assigned_by', auth()->id())),
                                            self::createActivityTab('Call', 'heroicon-o-phone', fn($query) => $query->where('activity_type', 'call')),
                                        ]),
                                    ])->headerActions([
                                        \Filament\Actions\Action::make('add_activity')
                                            ->label('Add Activity')
                                            ->modalWidth('xl')
                                            ->button()
                                            ->icon('heroicon-o-plus')
                                            ->form([
                                                Select::make('payment_status_id')
                                                    ->label('Payment Status')
                                                    ->options(function () {
                                                        return PaymentStatus::all()->pluck('payment_status', 'id')->toArray();
                                                    })
                                                    ->searchable()
                                                    ->required(),

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

                                                Radio::make('activity_type')
                                                    ->label('Activity Type')
                                                    ->inline()
                                                    ->options([
                                                        'email' => 'Email',
                                                        'call' => 'Call',
                                                        'meeting' => 'Meeting',
                                                        'whatsapp' => 'WhatsApp',
                                                        'follow_up' => 'Follow Up',
                                                        'reminder' => 'Reminder',
                                                    ])
                                                    ->required()
                                                    ->reactive(),

                                                DateTimePicker::make('follow_up_date_time')
                                                    ->label('Follow Up Date & Time')
                                                    ->visible(fn ($get) => $get('activity_type') === 'follow_up')
                                                    ->required(fn ($get) => $get('activity_type') === 'follow_up')
                                                    ->reactive(),

                                                DatePicker::make('reminder_date')
                                                    ->label('Reminder Date')
                                                    ->visible(fn ($get) => $get('activity_type') === 'reminder')
                                                    ->required(fn ($get) => $get('activity_type') === 'reminder')
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
                                            }),
                                    ]),
                                ]),
                            ]),
                            Tab::make('Call Log')->icon('heroicon-s-phone-arrow-up-right')->schema([
                                ComponentsGrid::make(3)->schema([
                                    Section::make('Contact Details')->icon('iconsax-bul-profile-circle')->schema([
                                        TextEntry::make('customer.name'),
                                        TextEntry::make('customer.email'),
                                        TextEntry::make('customer.mobile'),
                                        TextEntry::make('customer.address'),
                                        TextEntry::make('customer.membership_exp_date'),
                                        TextEntry::make('customer.payment_exp_date'),
                                        TextEntry::make('customer.membership_status'),
                                    ]),
                                    Section::make('Call Log')->icon('heroicon-s-phone-arrow-up-right')->columnSpan(2)->schema([
                                        TextEntry::make('call_date'),
                                        TextEntry::make('call_time'),
                                        TextEntry::make('call_duration'),
                                        TextEntry::make('call_type'),
                                        TextEntry::make('call_status'),
                                    ]),
                                ]),
                            ]),
                            Tab::make('Call Script')->icon('heroicon-m-clipboard-document-list')->schema([
                                ComponentsGrid::make(3)->schema([
                                    Section::make('Contact Details')->icon('iconsax-bul-profile-circle')->schema([
                                        TextEntry::make('customer.name'),
                                        TextEntry::make('customer.email'),
                                        TextEntry::make('customer.mobile'),
                                        TextEntry::make('customer.address'),
                                        TextEntry::make('customer.membership_exp_date'),
                                        TextEntry::make('customer.payment_exp_date'),
                                        TextEntry::make('customer.membership_status'),
                                    ]),
                                    Section::make('Call Script')->icon('heroicon-m-clipboard-document-list')->columnSpan(2)->schema([
                                        TextEntry::make('call_script'),
                                    ]),
                                ]),
                            ]),
                        ]),
                    ]),

                    Tab::make('Message')->icon('heroicon-s-chat-bubble-bottom-center-text')->schema([
                        Section::make('Send Message')->schema([
                            // Display a form to send a message to the customer
                            // Add a dropdown to select the Whatsapp message template
                            Select::make('message_template')
                                ->label('Message Template')
                                ->options(function () {
                                    // return WhatsappMessageTemplate::all()->pluck('template_name', 'id')->toArray();
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
            // hide edit button for juniors (user_level_id == 1)
            ->visible(fn($record) => Gate::allows('update', $record) && optional(auth()->user())->user_level_id !== 1)
            ->slideOver();

        return [$viewAction, $viewCallScript, $editAction];
    }
}