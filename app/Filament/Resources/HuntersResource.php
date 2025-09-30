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
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\LpwApiService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
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

                // Display activity stage with colored dots for level_score
                // TextColumn::make('latest_activity_stage')
                //     ->label('Stage')
                //     ->getStateUsing(function ($record) {
                //         $latestActivity = $record->activities()
                //             ->orderBy('created_at', 'desc')
                //             ->first();

                //         if (!$latestActivity) {
                //             return 'No Activity';
                //         }

                //         return ucfirst($latestActivity->stage ?? 'Unknown');
                //     })
                //     ->description(function ($record) {
                //         $latestActivity = $record->activities()
                //             ->orderBy('created_at', 'desc')
                //             ->first();

                //         if (!$latestActivity || $latestActivity->level_score === null) {
                //             return 'Score: N/A';
                //         }

                //         $score = (int) $latestActivity->level_score;
                //         $stage = $latestActivity->stage ?? '';

                //        // Use this emojis to represent colored dots
                //         $dot = match ($stage) {
                //             'contacted' => '🔵',
                //             'rna' => '🟠',
                //             'not_interested' => '🔴',
                //             default => '⚪', // Default gray dot
                //         };
                //         return str_repeat($dot, $score);
                //     })
                //     ->html() // allow rendering colored dots
                //     ->toggleable()
                //     ->sortable(),

                // Add a column to show the progress of each lead using below emojis:
                // Positive: 🟢, Pending: 🟡, RNA: 🟠, Not Interested: 🔴
                // Mark for every activity relevant to the lead using which status of the each activity.
                TextColumn::make('progress_icons')
                    ->label('Progress')
                    ->html()
                    // Add last acivity date as the first line. emojis should be in second line.
                    ->getStateUsing(function ($record) {
                        $activities = $record->activities()
                            ->orderBy('created_at', 'asc') // oldest → newest
                            ->get();

                        if ($activities->isEmpty()) {
                            return '<span class="text-gray-400">No Progress</span>';
                        }

                        $lastActivity = $activities->last();
                        $lastDate = $lastActivity ? Carbon::parse($lastActivity->created_at)->format('M d, Y') : 'N/A';

                        $icons = '';

                        foreach ($activities as $activity) {
                            $status = $activity->status ?? null;

                            // Map status → progress emoji
                            $map = [
                                'positive'        => '🟢',
                                'pending'         => '🟡',
                                'rna'             => '🟠',
                                'not_interested'  => '🔴',
                            ];

                            $icons .= $map[$status]; // default gray if status unknown
                        }

                        $icons = "<div class='text-xs text-gray-500 mb-1'>$lastDate</div>" . $icons;
                        return $icons;
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
                    ->modalWidth('7xl')
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
                                                                                        TextEntry::make('stage')
                                                                                            ->label('Stage')
                                                                                            ->badge()
                                                                                            ->color(fn(string $state): string => match ($state) {
                                                                                                'contacted' => 'primary',
                                                                                                'rna' => 'warning',
                                                                                                'not_interested' => 'danger',
                                                                                                default => 'gray',
                                                                                            }),
                                                                                        TextEntry::make('status')
                                                                                            ->label('Status')
                                                                                            ->badge()
                                                                                            ->color(fn(string $state): string => match ($state) {
                                                                                                'positive' => 'success',
                                                                                                'pending' => 'warning',
                                                                                                'rna' => 'orange',
                                                                                                'not_interested' => 'danger',
                                                                                                default => 'gray',
                                                                                            }),
                                                                                        TextEntry::make('level_score')
                                                                                            ->label('Level Score'),
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
                                                                            ->modalWidth('sm')
                                                                            ->button()
                                                                            // ->dropdown(true)
                                                                            ->icon('heroicon-o-plus')
                                                                            ->form([
                                                                                Select::make('activity_type')
                                                                                    ->label('Activity Type')
                                                                                    ->options([
                                                                                        'call' => 'Call',
                                                                                        'meeting' => 'Meeting',
                                                                                        'note' => 'Note',
                                                                                    ])
                                                                                    ->required(),

                                                                                // status
                                                                                Select::make('status')
                                                                                    ->label('Status')
                                                                                    ->options([
                                                                                        'positive' => 'Positive',
                                                                                        'pending' => 'Pending',
                                                                                        'rna' => 'RNA',
                                                                                        'not_interested' => 'Not Interested',
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

                                Tab::make('Status')
                                    // icon including letter 'R'
                                    ->icon('heroicon-o-cog')
                                    ->schema([
                                        Section::make('Status Information')
                                            ->schema([
                                                TextEntry::make('status')
                                                    ->label('Lead Status')
                                                    ->badge()
                                                    ->color(fn(string $state): string => match ($state) {
                                                        'new' => 'gray',
                                                        'contacted' => 'info',
                                                        'qualified' => 'warning',
                                                        'proposal' => 'primary',
                                                        'negotiation' => 'info',
                                                        'closed_won' => 'success',
                                                        'closed_lost' => 'danger',
                                                        'follow_up' => 'warning',
                                                        'on_hold' => 'gray',
                                                        'rejected' => 'danger',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('source')
                                                    ->label('Lead Source')
                                                    ->badge()
                                                    ->color(fn(string $state): string => match ($state) {
                                                        'website' => 'primary',
                                                        'api' => 'success',
                                                        'referral' => 'info',
                                                        'social_media' => 'warning',
                                                        'advertisement' => 'secondary',
                                                        'cold_call' => 'gray',
                                                        'email' => 'info',
                                                        'walk_in' => 'primary',
                                                        'other' => 'gray',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('is_active')
                                                    ->label('Active Status')
                                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                                        '0' => 'Inactive',
                                                        '1' => 'Active',
                                                        '2' => 'Special',
                                                        '3' => 'Pending',
                                                        default => 'Unknown'
                                                    })
                                                    ->badge()
                                                    ->color(fn(string $state): string => match ($state) {
                                                        '0' => 'danger',
                                                        '1' => 'success',
                                                        '2' => 'warning',
                                                        '3' => 'info',
                                                        default => 'gray'
                                                    }),
                                                TextEntry::make('is_trending')
                                                    ->label('Trending')
                                                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')
                                                    ->badge()
                                                    ->color(fn($state) => $state ? 'warning' : 'gray'),
                                                TextEntry::make('blocked')
                                                    ->label('Blocked')
                                                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')
                                                    ->badge()
                                                    ->color(fn($state) => $state ? 'danger' : 'success'),
                                                TextEntry::make('created_at')
                                                    ->label('Created At')
                                                    ->dateTime('M d, Y H:i'),
                                                TextEntry::make('updated_at')
                                                    ->label('Updated At')
                                                    ->dateTime('M d, Y H:i'),
                                            ])
                                            ->columns(2),

                                        Section::make('System Information')
                                            ->schema([
                                                TextEntry::make('ad_id')
                                                    ->label('Advertisement ID'),
                                                TextEntry::make('cust_id')
                                                    ->label('Customer ID'),
                                                TextEntry::make('user_id')
                                                    ->label('User ID')
                                                    ->placeholder('Not assigned'),
                                            ])
                                            ->columns(3)
                                            ->collapsed(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                ViewAction::make('viewActivities')
                    ->label('')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->modalHeading(fn($record) => 'Activities - ' . $record->heading)
                    ->modalWidth('6xl')
                    ->visible(fn($record) => Gate::allows('view', $record))
                    ->schema([
                        Tabs::make('ActivityTabs')
                            ->tabs([
                                Tab::make('Activities')
                                    ->icon('heroicon-o-eye')
                                    ->schema([
                                        // Activity Details(activity_type != 'call') using collapsible sections for each activity related to this lead
                                        // Add activity button to display a popup form to add a new activity.
                                        Action::make('addActivity')
                                            ->label('Add Activity')
                                            ->icon('heroicon-o-plus')
                                            ->schema([
                                                // Form to add a new activity
                                                // Should be updated activity table in the database.
                                                Select::make('activity_type')
                                                    ->label('Activity Type')
                                                    ->options([
                                                        'email' => 'Email',
                                                        'meeting' => 'Meeting',
                                                        'site_visit' => 'Site Visit',
                                                        'follow_up' => 'Follow Up',
                                                        'note' => 'Note',
                                                        'other' => 'Other',
                                                    ])
                                                    ->required(),

                                                Select::make('status')
                                                    ->label('Activity Status')
                                                    ->options([
                                                        'positive' => 'Positive',
                                                        'rna' => 'RNA',
                                                        'pending' => 'Pending',
                                                        'not_interested' => 'Not Interested',
                                                    ])
                                                    ->placeholder('Select status')
                                                    ->required(),
                                                // Add fields: stage, level_score, comments, qty, value, date_time, assined_by, old_am
                                                Select::make('stage')
                                                    ->label('Funnel Category')
                                                    ->options([
                                                        'contacted' => 'Contacted',
                                                        'rna' => 'RNA',
                                                        'not_interested' => 'Not Interested',
                                                    ])
                                                    ->required(),
                                                // numerical field to enter level_score.
                                                TextInput::make('level_score')
                                                    ->label('Funnel Score')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->maxValue(8)
                                                    ->required(),
                                                // Select::make('level_score')
                                                //     ->label('Lead Score')
                                                //     // Contacted: 1-8, Not Interested: 1-2, RNA: 1-3 - Numerical options.
                                                //     ->options(fn($record) => match($record->stage) {
                                                //         'contacted' => [
                                                //             1 => '1 - Very Low',
                                                //             2 => '2 - Low',
                                                //             3 => '3 - Below Average',
                                                //             4 => '4 - Average',
                                                //             5 => '5 - Moderate',
                                                //             6 => '6 - Above Average',
                                                //             7 => '7 - Good',
                                                //             8 => '8 - High',
                                                //         ],
                                                //         'not_interested' => [
                                                //             1 => '1 - Very Low',
                                                //             2 => '2 - Low',
                                                //         ],
                                                //         'rna' => [
                                                //             1 => '1 - Very Low',
                                                //             2 => '2 - Low',
                                                //             3 => '3 - Below Average',
                                                //         ],
                                                //         default => [],
                                                //     }),
                                                Textarea::make('comments')
                                                    ->label('Comments')
                                                    ->rows(4)
                                                    ->placeholder('Enter activity details...'),
                                                // TextInput::make('qty')
                                                //     ->label('Quantity')
                                                //     ->numeric()
                                                //     ->placeholder('1'),
                                                // TextInput::make('value')
                                                //     ->label('Value')
                                                //     ->numeric(),
                                                // Date and time picker for date_time
                                                DateTimePicker::make('date_time')
                                                    ->label('Activity Date & Time')
                                                    ->default(now())
                                                    ->required(),
                                                // assigned_by as the current logged in user
                                            ])
                                            ->action(function (array $data, $record) {
                                                try {
                                                    // Create the activity record
                                                    Activity::create([
                                                        'lead_id' => $record->id,
                                                        // 'user_id' => auth()->id(),
                                                        // 'ad_id' => $record->ad_id,
                                                        'activity_type' => $data['activity_type'] ?? 'other',
                                                        'stage' => $data['stage'],
                                                        'status' => $data['status'],
                                                        'level_score' => $data['level_score'],
                                                        'comments' => $data['comments'],
                                                        'qty' => $data['qty'] ?? 1,
                                                        'value' => $data['value'] ?? null,
                                                        'date_time' => $data['date_time'],
                                                        // 'reminder' => $data['reminder'] ?? null,
                                                        // 'old_am' => auth()->user()->name ?? 'System',
                                                        'created_at' => now(),
                                                        'updated_at' => now(),
                                                    ]);

                                                    Notification::make()
                                                        ->title('Activity Added Successfully')
                                                        ->body("New {$data['stage']} activity has been created for this lead.")
                                                        ->success()
                                                        ->duration(5000)
                                                        ->send();
                                                } catch (\Exception $e) {
                                                    Notification::make()
                                                        ->title('Error Adding Activity')
                                                        ->body('Failed to create activity: ' . $e->getMessage())
                                                        ->danger()
                                                        ->duration(8000)
                                                        ->send();

                                                    Log::error('Activity creation failed', [
                                                        'lead_id' => $record->id,
                                                        // 'user_id' => auth()->id(),
                                                        'error' => $e->getMessage(),
                                                        'data' => $data
                                                    ]);
                                                }
                                            })
                                            ->modalWidth('2xl')
                                            ->button(),

                                        RepeatableEntry::make('activities')
                                            ->schema([
                                                Section::make()
                                                    ->collapsible()
                                                    ->collapsed()
                                                    ->heading(fn($record) => match ($record->activity_type) {
                                                        'email' => '✉️ Email Activity',
                                                        'meeting' => '📅 Meeting Activity',
                                                        'site_visit' => '🏠 Site Visit Activity',
                                                        'follow_up' => '🔄 Follow Up Activity',
                                                        'note' => '📝 Note Activity',
                                                        'other' => '📋 Other Activity',
                                                        default => '📝 Activity',
                                                    })
                                                    ->description(function ($record) {
                                                        $date = $record->date_time ? $record->date_time->format('M d, Y h:i A') : 'No date';
                                                        $by = $record->old_am ?? 'Unknown';
                                                        return "{$date} • By: {$by}";
                                                    })
                                                    ->schema([
                                                        TextEntry::make('stage')
                                                            ->label('Activity Type')
                                                            ->badge()
                                                            ->color(fn($state) => match ($state) {
                                                                'email' => 'info',
                                                                'meeting' => 'success',
                                                                'site_visit' => 'warning',
                                                                'follow_up' => 'primary',
                                                                'note' => 'gray',
                                                                'other' => 'secondary',
                                                                default => 'gray'
                                                            }),
                                                        TextEntry::make('action')
                                                            ->label('Action Required')
                                                            ->placeholder('No action specified'),
                                                        TextEntry::make('comments')
                                                            ->label('Comments')
                                                            ->placeholder('No comments')
                                                            ->columnSpanFull(),
                                                        TextEntry::make('qty')
                                                            ->label('Quantity')
                                                            ->placeholder('N/A'),
                                                        TextEntry::make('value')
                                                            ->label('Value')
                                                            ->formatStateUsing(fn($state) => $state ? 'LKR ' . number_format($state) : 'N/A'),
                                                        TextEntry::make('level_score')
                                                            ->label('Lead Score')
                                                            ->formatStateUsing(fn($state) => $state ? "{$state}/10" : 'No score')
                                                            ->badge()
                                                            ->color(fn($state) => match (true) {
                                                                $state >= 8 => 'success',
                                                                $state >= 6 => 'warning',
                                                                $state >= 4 => 'primary',
                                                                default => 'gray'
                                                            }),
                                                        TextEntry::make('reminder')
                                                            ->label('Reminder Date')
                                                            ->date('M d, Y')
                                                            ->placeholder('No reminder'),
                                                        TextEntry::make('date_time')
                                                            ->label('Activity Date')
                                                            ->dateTime('M d, Y h:i A'),
                                                        TextEntry::make('old_am')
                                                            ->label('Assigned By')
                                                            ->placeholder('Unknown'),
                                                    ])
                                                    ->columns(2),
                                            ])
                                            ->contained(false)
                                    ]),
                                Tab::make('Call Log')
                                    ->icon('heroicon-o-phone')
                                    ->schema([
                                        // Call Log Details(activity_type == 'call') using collapsible sections for each call log related to this lead
                                        RepeatableEntry::make('callLogs')
                                            ->schema([
                                                Section::make()
                                                    ->collapsible()
                                                    ->collapsed()
                                                    ->heading(function ($record) {
                                                        $duration = $record->qty ? " ({$record->qty} min)" : '';
                                                        return "📞 Call Log{$duration}";
                                                    })
                                                    ->description(function ($record) {
                                                        $date = $record->date_time ? $record->date_time->format('M d, Y h:i A') : 'No date';
                                                        $by = $record->old_am ?? 'Unknown';
                                                        $score = $record->level_score ? " • Quality: {$record->level_score}/10" : '';
                                                        return "{$date} • By: {$by}{$score}";
                                                    })
                                                    ->schema([
                                                        TextEntry::make('action')
                                                            ->label('Call Purpose')
                                                            ->placeholder('No purpose specified'),
                                                        TextEntry::make('qty')
                                                            ->label('Duration')
                                                            ->formatStateUsing(fn($state) => $state ? "{$state} minutes" : 'Not recorded'),
                                                        TextEntry::make('comments')
                                                            ->label('Call Summary')
                                                            ->placeholder('No summary provided')
                                                            ->columnSpanFull(),
                                                        TextEntry::make('value')
                                                            ->label('Deal Value Discussed')
                                                            ->formatStateUsing(fn($state) => $state ? 'LKR ' . number_format($state) : 'Not discussed'),
                                                        TextEntry::make('level_score')
                                                            ->label('Lead Quality After Call')
                                                            ->formatStateUsing(function ($state) {
                                                                if (!$state) return 'Not rated';
                                                                return match (true) {
                                                                    $state >= 9 => "{$state}/10 - Excellent",
                                                                    $state >= 7 => "{$state}/10 - High Interest",
                                                                    $state >= 5 => "{$state}/10 - Moderate Interest",
                                                                    $state >= 3 => "{$state}/10 - Low Interest",
                                                                    default => "{$state}/10 - Very Low Interest"
                                                                };
                                                            })
                                                            ->badge()
                                                            ->color(fn($state) => match (true) {
                                                                $state >= 8 => 'success',
                                                                $state >= 6 => 'warning',
                                                                $state >= 4 => 'primary',
                                                                default => 'danger'
                                                            }),
                                                        TextEntry::make('reminder')
                                                            ->label('Follow-up Reminder')
                                                            ->date('M d, Y')
                                                            ->placeholder('No follow-up scheduled'),
                                                        TextEntry::make('date_time')
                                                            ->label('Call Date & Time')
                                                            ->dateTime('M d, Y h:i A'),
                                                        TextEntry::make('old_am')
                                                            ->label('Called By')
                                                            ->placeholder('Unknown'),
                                                    ])

                                                    ->columns(2),
                                            ])
                                            ->contained(false)
                                        // ->query(fn($record) => $record->activities()->where('stage', 'call')->orderBy('created_at', 'desc'))
                                        // ->emptyStateHeading('No Call Logs Found')
                                        // ->emptyStateDescription('No calls have been logged for this lead yet.')
                                        // ->emptyStateIcon('heroicon-o-phone'),
                                    ])
                            ])
                            ->columnSpanFull(),
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
