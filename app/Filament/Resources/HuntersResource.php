<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Exception;
use Filament\Tables\Actions\ColumnsAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\Resources\HuntersResource\Pages\ListHunters;
use App\Filament\Resources\HuntersResource\Pages\CreateHunters;
use App\Filament\Resources\HuntersResource\Pages\EditHunters;
use App\Filament\Resources\HuntersResource\Pages;
use App\Filament\Resources\HuntersResource\Pages\ViewActivities;
use App\Filament\Resources\HuntersResource\RelationManagers;
use App\Filament\Resources\HuntersResource\Widgets\LeadMonthlyTrend;
use App\Filament\Resources\HuntersResource\Widgets\LeadStatusChart;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\PaymentStatus;
use App\Services\LpwApiService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Collection;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Gate;
use Filament\Tables;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\ViewEntry;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Support\View\Components\ButtonComponent;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuntersResource extends Resource
{
    protected static ?string $model = Lead::class;

    // activity model also used
    protected static ?string $activityModel = Activity::class;

    protected static ?string $navigationLabel = 'Hunters';

    protected static string | \UnitEnum | null $navigationGroup = 'Private Sellers';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    // Show all leads since there's no source field in the new structure
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Check if current user has restricted access (user_level_id = 1)
        $user = auth()->user();

        if ($user && $user->user_level_id == 1) {
            // Restrict to only leads assigned to this user
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('heading')
                            ->label('Property Heading')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Listing Type')
                            ->options([
                                'sell' => 'For Sale',
                                'rent' => 'For Rent',
                                'lease' => 'For Lease',
                            ])
                            ->required(),
                        Select::make('propty_type')
                            ->label('Property Type')
                            ->options([
                                'house' => 'House',
                                'apartment' => 'Apartment',
                                'land' => 'Land',
                                'commercial' => 'Commercial',
                                'villa' => 'Villa',
                                'townhouse' => 'Townhouse',
                            ])
                            ->required(),
                        Select::make('service_type')
                            ->label('Service Type')
                            ->options([
                                'complete' => 'Complete Service',
                                'basic' => 'Basic Listing',
                                'premium' => 'Premium Service',
                            ]),
                    ])
                    ->columns(2),

                Section::make('Location Details')
                    ->schema([
                        TextInput::make('street')
                            ->label('Street Address')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('City')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.00000001),
                        TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.00000001),
                    ])
                    ->columns(2),

                Section::make('Property Description')
                    ->schema([
                        Textarea::make('desc')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pricing Information')
                    ->schema([
                        TextInput::make('price')
                            ->label('Main Price')
                            ->numeric()
                            ->prefix('LKR'),
                        TextInput::make('alt_price')
                            ->label('Alternative Price')
                            ->numeric(),
                        Select::make('alt_currency')
                            ->label('Alternative Currency')
                            ->options([
                                'LKR' => 'LKR',
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ]),
                        Select::make('price_type')
                            ->label('Price Type')
                            ->options([
                                'total' => 'Total Price',
                                'per_sq_ft' => 'Per Square Foot',
                                'per_month' => 'Per Month',
                                'negotiable' => 'Negotiable',
                            ]),
                        TextInput::make('price_monthly')
                            ->label('Monthly Price')
                            ->numeric()
                            ->prefix('LKR'),
                        TextInput::make('price_land_pp')
                            ->label('Land Price per Perch')
                            ->numeric()
                            ->prefix('LKR'),
                        TextInput::make('price_land_total')
                            ->label('Total Land Price')
                            ->numeric()
                            ->prefix('LKR'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        Select::make('contact_type')
                            ->label('Contact Type')
                            ->options([
                                'owner' => 'Property Owner',
                                'agent' => 'Real Estate Agent',
                                'developer' => 'Developer',
                            ]),
                        TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Media & Links')
                    ->schema([
                        Toggle::make('pic')
                            ->label('Has Pictures'),
                        TextInput::make('pic_count')
                            ->label('Picture Count')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('youtube_link')
                            ->label('YouTube Link')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('video_link')
                            ->label('Video Link')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('image_360')
                            ->label('360° Image Link')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Status & Settings')
                    ->schema([
                        Select::make('status')
                            ->label('Lead Status')
                            ->options([
                                'new' => 'New',
                                'follow_up' => 'Follow Up',
                                'system' => 'System',
                                'to_be_expired' => 'To Be Expired',
                                'expired' => 'Expired',
                            ])
                            ->default('new')
                            ->required(),
                        Select::make('source')
                            ->label('Lead Source')
                            ->options([
                                'pending_payments' => 'Pending Payments',
                                'ikman' => 'IKMAN',
                                'facebook' => 'Facebook',
                                'other' => 'Other',
                            ])
                            ->searchable(),

                        TextInput::make('weight')
                            ->label('Priority Weight')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1000)
                            ->helperText('Auto-calculated based on keywords and criteria. Higher = Higher Priority')
                            ->disabled() // Make it read-only as it's auto-calculated
                            ->dehydrated(false), // Don't include in form submission

                        Select::make('is_active')
                            ->label('Active Status')
                            ->options([
                                0 => 'Inactive',
                                1 => 'Active',
                                2 => 'Special',
                                3 => 'Pending'
                            ])
                            ->default(1)
                            ->native(false)
                            ->required(),
                        Toggle::make('is_trending')
                            ->label('Trending'),
                        Toggle::make('blocked')
                            ->label('Blocked')
                            ->helperText('Block this listing from public view'),
                    ])
                    ->columns(3),

                Section::make('System Fields')
                    ->schema([
                        TextInput::make('ad_id')
                            ->label('Advertisement ID')
                            ->numeric()
                            ->required(),
                        TextInput::make('cust_id')
                            ->label('Customer ID')
                            ->numeric()
                            ->required(),
                        TextInput::make('user_id')
                            ->label('User ID')
                            ->numeric(),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Fetch API data for hunters
        $apiService = new LpwApiService();
        $pendingPayments = $apiService->getPendingPayments();

        return $table
            ->columns([
                // Print customer name and user name as new columns
                IconColumn::make('is_active')
                    ->label('')
                    ->boolean()
                    ->trueIcon('bi-pin-fill')
                    ->falseIcon('bi-pin')
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->action(function ($record) {
                        $record->is_active = ! $record->is_active;
                        $record->save();
                    })
                    ->tooltip(fn($state): string => $state ? 'Unpin' : 'Pin')
                    ->sortable(),

                TextColumn::make('customer.firstname')
                    ->label('Customer')
                    // Add customer email as the second line under name. Use text: xs, color: gray-500.
                    ->description(fn($record) => $record->customer->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('posted_date')
                    ->label('Posted Date')
                    ->dateTime('M d, Y')
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->colors([
                        'primary' => 'pending_payments',
                        'success' => 'ikman',
                        'warning' => 'facebook',
                        'secondary' => 'other',
                    ])
                    ->sortable(),

                TextColumn::make('property_summary')
                    ->label('Property Details')
                    ->getStateUsing(function ($record) {
                        // Convert price values into Billions, Millions and thousands
                        $priceInMillions = $record->price / 1_000_000;
                        $priceInBillions = $record->price / 1_000_000_000;
                        $priceInThousands = $record->price / 1_000;
                        if ($priceInBillions >= 1) {
                            $priceFormatted = number_format($priceInBillions) . ' B';
                        } elseif ($priceInMillions >= 1) {
                            $priceFormatted = number_format($priceInMillions) . ' M';
                        } elseif ($priceInThousands >= 1) {
                            $priceFormatted = number_format($priceInThousands) . ' K';
                        } else {
                            $priceFormatted = number_format($record->price);
                        }
                        $price = 'LKR ' . $priceFormatted;
                        $type = ucfirst($record->type);
                        $city = ucfirst($record->city);
                        // Render only the first line here; description below will handle the second line. text-xs, gray-500 of propertyType.
                        // Add a badge to represent the type. For all types apply blue color badge.
                        return "$price - $type";
                    })
                    ->html()
                    ->description(function ($record) {
                        $propertyType = ucfirst($record->propty_type);
                        $city = ucfirst($record->city);
                        return "$propertyType | $city";
                    })
                    ->searchable(['type', 'propty_type', 'city', 'price'])
                    ->sortable()
                    // Limit the row height to 2 lines.
                    ->wrap(),

                // Add a column to show latest comment. Get the latest comment from activity table where comments is not null.
                TextColumn::make('latest_comment')
                    ->label('Latest Comment')
                    ->getStateUsing(function ($record) {
                        return $record->activities()
                            ->whereNotNull('comments')
                            ->orderBy('created_at', 'desc')
                            ->first()?->comments ?? 'No Comment';
                    })
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('progress_icons')
                    ->label('Progress')
                    ->html()
                    ->getStateUsing(function ($record) {
                        // Get the last 3 activities (newest first), then reverse to display oldest → newest (most recent at right)
                        $activities = $record->activities()
                            ->with('paymentStatus')
                            ->orderBy('created_at', 'desc')
                            ->take(3)
                            ->get()
                            ->reverse(); // now oldest -> newest (left -> right)

                        if ($activities->isEmpty()) {
                            return '🔘'; // default emoji when no activity
                        }

                        // Display last activity date as the first line in small gray text
                        $lastActivityDate = $activities->last()->created_at->format('M d');
                        // Map colors to emojis

                        $map = [
                            'red'    => '🔴',
                            'orange' => '🟠',
                            'yellow' => '🟡',
                            'green'  => '🟢',
                            'black'  => '⚫',
                            
                        ];

                        $result = [];
                        foreach ($activities as $activity) {
                            $color = strtolower(trim((string) ($activity->paymentStatus?->color ?? '')));
                            $result[] = $map[$color] ?? '🔘'; // use updated default emoji for unknown
                        }

                        return "<span class='text-xs text-gray-500'>$lastActivityDate</span><br>" . implode('', $result);
                    })
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('activity_icons')
                    ->label('Funnel Stage')
                    ->html() // allow raw HTML rendering
                    ->getStateUsing(function ($record) {
                        $activities = $record->activities()
                            ->orderBy('created_at', 'asc') // oldest → newest
                            ->get();

                        if ($activities->isEmpty()) {
                            return '<span class="text-gray-400">No Activity</span>';
                        }

                        $icons = '';

                        foreach ($activities as $activity) {
                            $stage = $activity->stage ?? null;
                            $level = (int) ($activity->level_score ?? 0);

                            // Emoji sets by stage
                            $emojiSets = [
                                'contacted' => ['🔴', '🟠', '🟡', '🟤', '🔵', '🟣', '🟢'],
                                'rna' => ['🟥', '🟧', '🟨'],
                                'not_interested' => ['🔶', '🔷'],
                            ];

                            if ($stage && isset($emojiSets[$stage])) {
                                $emojis = $emojiSets[$stage];

                                // Pick icon based on level
                                $index = max(0, min($level - 1, count($emojis) - 1));
                                $icons .= $emojis[$index]; // append without extra space
                            }
                        }

                        return $icons ?: '<span class="text-gray-400">No Activity</span>';
                    })
                    ->toggleable()
                    ->sortable(),

                BadgeColumn::make('weight')
                    ->label('Weight')
                    ->getStateUsing(function ($record) {
                        return $record->weight;
                    })
                    ->colors([
                        'primary' => fn($state) => $state >= 700, // High weight
                        'warning' => fn($state) => $state >= 300 && $state < 700, // Medium weight
                        'secondary' => fn($state) => $state < 300, // Low weight
                    ])
                    ->toggleable()
                    ->sortable(),

                // Street, service_type columns
                TextColumn::make('street')
                    ->label('Street')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('service_type')
                    ->label('Service Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // is_active, is_trending as icons
                // IconColumn::make('is_active')
                //     ->label('Active')
                //     ->boolean()
                //     ->trueIcon('heroicon-m-shield-check')
                //     ->falseIcon('heroicon-s-x-circle')
                //     ->trueColor('success')
                //     ->falseColor('danger')
                //     ->toggleable()
                //     ->sortable(),

                IconColumn::make('is_trending')
                    ->label('Trending')
                    ->boolean()
                    ->trueIcon('heroicon-s-fire')
                    ->falseIcon('heroicon-s-minus')
                    ->trueColor('warning')
                    ->falseColor('secondary')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'primary' => 'new',
                        'secondary' => 'follow_up',
                        'success' => 'system',
                        'warning' => 'to_be_expired',
                        'danger' => 'expired',
                    ])
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('user.username')
                    ->label('AM')
                    ->searchable()
                    ->sortable(),

                // TextColumn::make('heading')
                //     ->label('Property Heading')
                //     ->searchable()
                //     ->sortable()
                //     ->limit(50),

                // TextColumn::make('weight')
                //     ->label('Priority Weight')
                //     ->sortable()
                //     ->badge(),
                // ->formatStateUsing(fn ($record) => $record->weight . ' (' . \App\Services\LeadWeightService::getWeightLevel($record->weight) . ')')
                // ->color(fn ($record) => \App\Services\LeadWeightService::getWeightColor($record->weight)),

                // Tables\Columns\IconColumn::make('pic')
                //     ->label('Has Pictures')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-camera')
                //     ->falseIcon('heroicon-o-x-mark'),

                // Tables\Columns\IconColumn::make('is_trending')
                //     ->label('Trending')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-fire')
                //     ->falseIcon('heroicon-o-minus')
                //     ->trueColor('warning'),

                // Add the column posted_date

            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable(),
                SelectFilter::make('type')
                    ->label('Listing Type')
                    ->options([
                        'sell' => 'For Sale',
                        'rent' => 'For Rent',
                        'lease' => 'For Lease',
                    ]),
                SelectFilter::make('propty_type')
                    ->label('Property Type')
                    ->options([
                        'house' => 'House',
                        'apartment' => 'Apartment',
                        'land' => 'Land',
                        'commercial' => 'Commercial',
                        'villa' => 'Villa',
                        'townhouse' => 'Townhouse',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'pending_payments' => 'Pending Payments',
                        'ikman' => 'IKMAN',
                        'facebook' => 'Facebook',
                        'other' => 'Other',
                    ]),

                // SelectFilter::make('is_active')
                //     ->label('Status')
                //     ->options([
                //         0 => 'Inactive',
                //         1 => 'Active',
                //         2 => 'Special',
                //         3 => 'Pending',
                //     ]),
                // posted_date filter
                Filter::make('posted_date')
                    ->schema([
                        DatePicker::make('posted_date_from')
                            ->label('Posted Date From'),
                        DatePicker::make('posted_date_to')
                            ->label('Posted Date To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['posted_date_from'], fn(Builder $query, $value) => $query->whereDate('posted_date', '>=', $value))
                            ->when($data['posted_date_to'], fn(Builder $query, $value) => $query->whereDate('posted_date', '<=', $value));
                    })
                    ->label('Posted Date Range'),

                // price range filter
                Filter::make('price_range')
                    ->schema([
                        TextInput::make('price_min')
                            ->label('Min Price')
                            ->numeric()
                            ->prefix('LKR'),
                        TextInput::make('price_max')
                            ->label('Max Price')
                            ->numeric()
                            ->prefix('LKR'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['price_min'], fn(Builder $query, $value) => $query->where('price', '>=', $value))
                            ->when($data['price_max'], fn(Builder $query, $value) => $query->where('price', '<=', $value));
                    })
                    ->label('Price Range'),
                // Weight filter for low, high, medium
                SelectFilter::make('weight')
                    ->label('Weight')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),
            ])
            // Display layout as a popup modal
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Property Details - ' . $record->heading)
                    ->modalWidth('6xl')
                    ->visible(fn($record) => Gate::allows('view', $record))
                    ->schema([
                        Tabs::make('PropertyTabs')
                            ->tabs([
                                Tab::make('Overview')
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        Section::make('Property Information')
                                            ->schema([
                                                TextEntry::make('heading')
                                                    ->label('Property Heading')
                                                    ->size('lg')
                                                    ->weight('bold'),
                                                TextEntry::make('type')
                                                    ->label('Listing Type')
                                                    ->badge()
                                                    ->color(fn(string $state): string => match ($state) {
                                                        'sell' => 'primary',
                                                        'rent' => 'success',
                                                        'lease' => 'warning',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('propty_type')
                                                    ->label('Property Type')
                                                    ->badge()
                                                    ->color('info'),
                                                TextEntry::make('service_type')
                                                    ->label('Service Type')
                                                    ->badge(),
                                                TextEntry::make('price')
                                                    ->label('Price')
                                                    ->money('LKR')
                                                    ->size('lg')
                                                    ->weight('bold')
                                                    ->color('success'),
                                                TextEntry::make('price_type')
                                                    ->label('Price Type'),
                                            ])
                                            ->columns(3),

                                        Section::make('Location')
                                            ->schema([
                                                TextEntry::make('street')
                                                    ->label('Street Address')
                                                    ->placeholder('Not specified'),
                                                TextEntry::make('city')
                                                    ->label('City')
                                                    ->icon('heroicon-o-map-pin'),
                                                TextEntry::make('lat')
                                                    ->label('Latitude')
                                                    ->placeholder('Not specified'),
                                                TextEntry::make('lng')
                                                    ->label('Longitude')
                                                    ->placeholder('Not specified'),
                                            ])
                                            ->columns(3),

                                        Section::make('Description')
                                            ->schema([
                                                TextEntry::make('desc')
                                                    ->label('Property Description')
                                                    ->placeholder('No description available')
                                                    ->columnSpanFull()
                                                    ->html(),
                                            ]),
                                    ]),

                                // Add a new tab for Activity
                                Tab::make('Activity')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    // Add 2 tabs 'Activity Log' and 'Call Log' inside this tab
                                    ->schema([
                                        Tabs::make('ActivitySubTabs')
                                            ->tabs([
                                                Tab::make('Activity Log')
                                                    ->icon('heroicon-o-list-bullet')
                                                    ->schema([
                                                        Grid::make(3)
                                                            ->schema([
                                                                Section::make('Contact Details')
                                                                    // Reduce the size of the section
                                                                    ->icon('iconsax-bul-profile-circle')
                                                                    ->schema([
                                                                        // Display customer.name, customer.email, customer.mobile, customer.address, customer.membership_exp_date, customer.payment_exp_date, customer.membership_status
                                                                        TextEntry::make('customer.name'),
                                                                        TextEntry::make('customer.email'),
                                                                        TextEntry::make('customer.mobile'),
                                                                        TextEntry::make('customer.address'),
                                                                        TextEntry::make('customer.membership_exp_date'),
                                                                        TextEntry::make('customer.payment_exp_date'),
                                                                        TextEntry::make('customer.membership_status'),
                                                                    ]),

                                                                Section::make('Activity History')
                                                                    ->columnSpan(2)
                                                                    ->schema([
                                                                        // your activity timeline as collapsible list. Heading should be activity_type, Date, done by user included.
                                                                        RepeatableEntry::make('activities')
                                                                            ->label('Activities')
                                                                            ->schema([
                                                                                // Collapsible section for activities list.
                                                                                Section::make('Activity List')
                                                                                    ->collapsible()
                                                                                    // Recent activity should be displayed on top of the list.
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
                                                                                        $user = $record->user->name ?? $record->old_am ?? 'Unknown';
                                                                                        return "{$date} • By: {$user}";
                                                                                    })
                                                                                    ->schema([
                                                                                        TextEntry::make('activity_type')
                                                                                            ->label('Activity Type')
                                                                                            ->weight('bold'),
                                                                                        // paymentStatus relationship to get payment_status from payment_status table
                                                                                        TextEntry::make('paymentStatus.payment_status')
                                                                                            ->label('Payment Status')
                                                                                            ->badge()
                                                                                            ->color(fn($state) => match (strtolower($state)) {
                                                                                                'Red' => 'danger',
                                                                                                'Orange' => 'warning',
                                                                                                'Yellow' => 'yellow',
                                                                                                'Green' => 'success',
                                                                                                'Black' => 'dark',
                                                                                                default => 'secondary',
                                                                                            }),
                                                                                        // 
                                                                                        TextEntry::make('comments')
                                                                                            ->label('Comments')
                                                                                            ->placeholder('No comments'),
                                                                                        TextEntry::make('created_at')
                                                                                            ->label('Date')
                                                                                            ->dateTime('M d, Y H:i'),
                                                                                        TextEntry::make('user.name')
                                                                                            ->label('Done By')
                                                                                            ->icon('heroicon-o-user'),
                                                                                    ])
                                                                                    ->columns(3)
                                                                                    ->collapsed(),
                                                                            ]),
                                                                    ])
                                                                    ->headerActions([
                                                                        //
                                                                        Action::make('add_activity')
                                                                            ->label('Add Activity')
                                                                            ->modalWidth('4xl')
                                                                            ->button()
                                                                            // ->dropdown(true)
                                                                            ->icon('heroicon-o-plus')
                                                                            ->form([
                                                                                Select::make('payment_status_id')
                                                                                    ->label('Payment Status')
                                                                                    // Show options of payment_status from paymentStatus relationship
                                                                                    ->options(function () {
                                                                                        return PaymentStatus::all()->pluck('payment_status', 'id')->toArray();
                                                                                    })
                                                                                    ->searchable()
                                                                                    ->required(),

                                                                                // Add radio buttons for stage with options: contacted, not_interested, rna
                                                                                Radio::make('stage')
                                                                                    ->label('Funnel Category')
                                                                                    // Display categories from staged_funnel table
                                                                                    ->options([
                                                                                        'contacted' => 'Contacted',
                                                                                        'not_interested' => 'Not Interested',
                                                                                        'rna' => 'RNA',
                                                                                    ])
                                                                                    ->required(),
                                                                                
                                                                                // Add 

                                                                                // Add radio buttons for activity_type with options: email, meeting, site_visit, message, follow_up, call
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
                                                                                    ->required(),

                                                                                Select::make('stage')
                                                                                    ->label('Funnel Category')
                                                                                    ->options([
                                                                                        'contacted' => 'Contacted',
                                                                                        'rna' => 'RNA',
                                                                                        'not_interested' => 'Not Interested',
                                                                                    ])
                                                                                    ->required(),

                                                                                // level_score text input numeric between 1 to 10
                                                                                TextInput::make('level_score')
                                                                                    ->label('Funnel Stage')
                                                                                    ->numeric()
                                                                                    ->minValue(1)
                                                                                    ->maxValue(10)
                                                                                    ->placeholder('e.g. 2'),

                                                                                Textarea::make('comments')
                                                                                    ->label('Comments')
                                                                                    ->rows(3),
                                                                            ])
                                                                            ->action(function (array $data, $record) {
                                                                                // Save the new activity for this record
                                                                                $record->activities()->create([
                                                                                    'activity_type' => $data['activity_type'],
                                                                                    'stage'         => $data['stage'],
                                                                                    'status'        => $data['status'],
                                                                                    'level_score'   => $data['level_score'] ?? null,
                                                                                    'comments'      => $data['comments'] ?? null,
                                                                                    // Save current auth id into assigned_by column in the activity table
                                                                                    'assigned_by'   => auth()->id(),
                                                                                ]);
                                                                            }),
                                                                    ]),
                                                            ]),
                                                    ]),
                                                Tab::make('Call Log')
                                                    ->icon('heroicon-o-phone')
                                                    ->schema([]),
                                            ]),
                                    ]),

                                Tab::make('Contact')
                                    ->icon('heroicon-o-phone')
                                    ->schema([
                                        Section::make('Contact Information')
                                            ->schema([
                                                TextEntry::make('contact_name')
                                                    ->label('Contact Person')
                                                    ->size('lg')
                                                    ->weight('bold'),
                                                TextEntry::make('contact_type')
                                                    ->label('Contact Type')
                                                    ->badge()
                                                    ->color(fn(string $state): string => match ($state) {
                                                        'owner' => 'primary',
                                                        'agent' => 'success',
                                                        'developer' => 'warning',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('email')
                                                    ->label('Email')
                                                    ->icon('heroicon-o-envelope'),
                                            ])
                                            ->columns(2),
                                    ]),

                                Tab::make('Media')
                                    ->icon('heroicon-o-camera')
                                    ->schema([
                                        Section::make('Media Information')
                                            ->schema([
                                                TextEntry::make('pic')
                                                    ->label('Has Pictures')
                                                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')
                                                    ->badge()
                                                    ->color(fn($state) => $state ? 'success' : 'danger'),
                                                TextEntry::make('pic_count')
                                                    ->label('Number of Pictures'),
                                                TextEntry::make('youtube_link')
                                                    ->label('YouTube Link')
                                                    ->placeholder('No YouTube link')
                                                    ->formatStateUsing(fn($state) => $state ?: 'No YouTube link'),
                                                TextEntry::make('video_link')
                                                    ->label('Video Link')
                                                    ->placeholder('No video link')
                                                    ->formatStateUsing(fn($state) => $state ?: 'No video link'),
                                                TextEntry::make('image_360')
                                                    ->label('360° Image')
                                                    ->placeholder('No 360° image')
                                                    ->formatStateUsing(fn($state) => $state ?: 'No 360° image'),
                                            ])
                                            ->columns(2),
                                    ]),

                                Tab::make('Stats')
                                    // icon including letter 'R'
                                    ->icon('heroicon-s-chart-bar-square')
                                    ->schema([
                                        Section::make('Statistical Information')
                                            ->schema([
                                                // Add LeadStatusChart here
                                                // LeadStatusChart::make(),
                                                // // Add LeadMonthlyTrend here
                                                // LeadMonthlyTrend::make(),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // Add a new action to view the call script for each lead.
                Action::make('viewCallScript')
                    ->label('')
                    ->icon('heroicon-c-phone')
                    ->visible(fn($record) => Gate::allows('view', $record))
                    ->modalHeading('Call Script')
                    ->modalButton('Close')
                    ->modalSubmitAction(false) // disables footer buttons except Close
                    ->action(function ($record, $livewire, $data, $action) {
                        // Example: Fetch script from API
                        $response = Http::get('https://your-api.com/call-script/' . $record->id);

                        if ($response->successful()) {
                            $script = $response->json()['script'] ?? 'No script available.';
                        } else {
                            $script = '⚠️ Failed to fetch call script.';
                        }

                        // Update the modal content dynamically
                        $action->modalHeading("Call Script for Lead #{$record->id}");
                        $action->modalContent(view('filament.call-script-modal', ['script' => $script]));
                    })
                    ->modalWidth('4xl'),

                EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn($record) => Gate::allows('update', $record))
                    ->slideOver(),
            ])
            ->headerActions([
                // Add customize table columns action


                \Filament\Actions\Action::make('sync_api_data')
                    ->label('Sync API Data')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function () {
                        $apiService = new LpwApiService();
                        $apiResponse = $apiService->getPendingPayments(); // or your actual API method
                        $results = $apiResponse['results'] ?? [];

                        $created = 0;
                        $updated = 0;
                        $customerCreated = 0;
                        $customerUpdated = 0;

                        foreach ($results as $item) {
                            if (!isset($item['ad']['ad_id'])) {
                                continue;
                            }

                            $ad = $item['ad'];
                            $user = $item['user'] ?? null;

                            /**
                             * --- Sync Customer First ---
                             */
                            $customerId = null;
                            if ($user && isset($user['uid'])) {
                                // Check if customer already exists by uid (external ID from API)
                                $existingCustomer = Customer::where('id', $user['uid'])->first();

                                if (!$existingCustomer) {
                                    // Double-check by mobile number to prevent duplicates
                                    $mobileCheck = null;
                                    if (!empty($user['mobile'])) {
                                        $mobileCheck = Customer::where('mobile', $user['mobile'])->first();
                                    }

                                    if ($mobileCheck) {
                                        // Customer exists with same mobile but different ID
                                        $customerId = $mobileCheck->id;
                                        $this->info("Found existing customer with mobile {$user['mobile']}, using ID: {$customerId}");
                                    } else {
                                        // Create new customer using the uid as the primary key
                                        $customerPayload = [
                                            'id'               => $user['uid'],
                                            'firstname'        => $user['firstname'] ?? null,
                                            'surname'          => $user['surname'] ?? null,
                                            'mobile'           => $user['mobile'] ?? null,
                                            'mobile_alt'       => $user['mobile_alt'] ?? null,
                                            'email'            => $user['email'] ?? null,
                                        ];

                                        try {
                                            $customer = Customer::create($customerPayload);
                                            $customerId = $customer->id;
                                            $customerCreated++;
                                        } catch (Exception $e) {
                                            // If creation fails (e.g., duplicate ID), try to find existing
                                            $customer = Customer::where('id', $user['uid'])->first();
                                            $customerId = $customer ? $customer->id : null;
                                            $this->warn("Failed to create customer {$user['uid']}: " . $e->getMessage());
                                        }
                                    }
                                } else {
                                    // Use existing customer
                                    $customerId = $existingCustomer->id;

                                    // Check if customer data has changed and update if needed
                                    $newData = [
                                        'firstname'        => $user['firstname'] ?? null,
                                        'surname'          => $user['surname'] ?? null,
                                        'mobile'           => $user['mobile'] ?? null,
                                        'mobile_alt'       => $user['mobile_alt'] ?? null,
                                        'email'            => $user['email'] ?? null,
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

                            /**
                             * --- Sync Lead (Ad) ---
                             */
                            $existingLead = Lead::where('ad_id', $ad['ad_id'])->first();

                            $payload = [
                                'ad_id'          => $ad['ad_id'],
                                'cust_id'        => $customerId, // <-- use synced customer ID
                                'type'           => $ad['type'] ?? null,
                                'propty_type'    => $ad['propty_type'] ?? null,
                                'service_type'   => $ad['service_type'] ?? null,
                                'street'         => $ad['street'] ?? null,
                                'city'           => $ad['city'] ?? null,
                                'heading'        => $ad['heading'] ?? null,
                                'desc'           => $ad['desc'] ?? null,
                                'submit_date'    => $ad['submit_date'] ?? null,
                                'posted_date'    => $ad['posted_date'] ?? null,
                                'price'          => $ad['price'] ?? null,
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
                                'source'         => $ad['source'] ?? 'API',
                                'house_post_url' => $ad['house_post_url'] ?? null,
                                'api_sync_date'  => now(),
                                'last_update_date' => now(),
                                'last_update_by' => 'API Sync',
                            ];

                            if ($existingLead) {
                                $existingLead->update($payload);
                                $updated++;
                            } else {
                                Lead::create($payload);
                                $created++;
                            }
                        }

                        Notification::make()
                            ->title('API Sync Completed')
                            ->body("Leads: Created {$created}, Updated {$updated}\nCustomers: Created {$customerCreated}, Updated {$customerUpdated}")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->tooltip('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Property Leads')
                        ->modalDescription('Are you sure you want to delete these property leads? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them')
                        ->visible(fn() => auth()->user()->user_level_id != 1),

                    // 🔄 Toggle Pin
                    BulkAction::make('toggle_pin')
                        ->label('Toggle Pin')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $toggledCount = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'is_active' => $record->is_active == 1 ? 0 : 1,
                                ]);
                                $toggledCount++;
                            }
                            Notification::make()
                                ->title('Pin Updated')
                                ->body("Toggled pin status for {$toggledCount} leads.")
                                ->success()
                                ->send();
                        }),

                    // ⭐ Toggle Favourite
                    BulkAction::make('toggle_favourite')
                        ->label('Toggle Favourite')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $toggledCount = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'is_trending' => $record->is_trending == 1 ? 0 : 1,
                                ]);
                                $toggledCount++;
                            }
                            Notification::make()
                                ->title('Favourite Updated')
                                ->body("Toggled favourite status for {$toggledCount} leads.")
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('export_selected')
                        ->label('Export Selected')
                        // ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (Collection $records) {
                            // Export functionality can be implemented here
                            Notification::make()
                                ->title('Export Started')
                                ->body('Export of selected leads has been initiated.')
                                ->info()
                                ->send();
                        }),
                ]),
            ])
            // ->recordUrl(null) // disable row click
            ->recordUrl(null); // disable row click
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::where('is_active', 1)->count();
        return $count > 0 ? (string)$count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHunters::route('/'),
            'create' => CreateHunters::route('/create'),
            'edit' => EditHunters::route('/{record}/edit'),
            // 'activities' => ViewActivities::route('/{record}/activities'),
        ];
    }
}
