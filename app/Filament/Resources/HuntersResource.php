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
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\Resources\HuntersResource\Pages\ListHunters;
use App\Filament\Resources\HuntersResource\Pages\CreateHunters;
use App\Filament\Resources\HuntersResource\Pages\EditHunters;
use App\Filament\Resources\HuntersResource\Pages;
use App\Filament\Resources\HuntersResource\RelationManagers;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\LpwApiService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Group;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class HuntersResource extends Resource
{
    protected static ?string $model = Lead::class;

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

        // Log the API data for debugging
        // Log::info('Hunters API Data:', [
        //     'count' => count($pendingPayments),
        //     'sample' => !empty($pendingPayments) ? array_slice($pendingPayments, 0, 2) : []
        // ]);

        return $table
            ->columns([
                // Print customer name and user name as new columns
                TextColumn::make('customer.firstname')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('AM Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('heading')
                    ->label('Property Heading')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('weight')
                    ->label('Priority Weight')
                    ->sortable()
                    ->badge(),
                // ->formatStateUsing(fn ($record) => $record->weight . ' (' . \App\Services\LeadWeightService::getWeightLevel($record->weight) . ')')
                // ->color(fn ($record) => \App\Services\LeadWeightService::getWeightColor($record->weight)),

                BadgeColumn::make('type')
                    ->label('Listing Type')
                    ->colors([
                        'primary' => 'sell',
                        'success' => 'rent',
                        'warning' => 'lease',
                    ])
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('propty_type')
                    ->label('Property Type')
                    ->colors([
                        'primary' => 'house',
                        'success' => 'apartment',
                        'warning' => 'land',
                        'danger' => 'commercial',
                        'info' => 'villa',
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin'),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('LKR')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('contact_name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_type')
                    ->label('Contact Type')
                    ->badge()
                    ->colors([
                        'primary' => 'owner',
                        'success' => 'agent',
                        'warning' => 'developer',
                    ]),

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

                // Tables\Columns\IconColumn::make('pic')
                //     ->label('Has Pictures')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-camera')
                //     ->falseIcon('heroicon-o-x-mark'),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '0' => 'danger',
                        '1' => 'success',
                        '2' => 'warning',
                        '3' => 'info',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '0' => 'Inactive',
                        '1' => 'Active',
                        '2' => 'Special',
                        '3' => 'Pending',
                        default => 'Unknown'
                    }),

                // Tables\Columns\IconColumn::make('is_trending')
                //     ->label('Trending')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-fire')
                //     ->falseIcon('heroicon-o-minus')
                //     ->trueColor('warning'),

                // Add the column posted_date

                TextColumn::make('posted_date')
                    ->label('Posted Date')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                // Add required filters
                // Add user name filter
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'New',
                        'follow_up' => 'Follow Up',
                        'system' => 'System',
                        'to_be_expired' => 'To Be Expired',
                        'expired' => 'Expired',
                    ]),

                // Weight filter for low, high, medium
                SelectFilter::make('weight')
                    ->label('Weight')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'pending_payments' => 'Pending Payments',
                        'ikman' => 'IKMAN',
                        'facebook' => 'Facebook',
                        'other' => 'Other',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        0 => 'Inactive',
                        1 => 'Active',
                        2 => 'Special',
                        3 => 'Pending',
                    ]),
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
                    ->label('Price Range')
            ])

            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn($record) => 'Property Details - ' . $record->heading)
                    ->modalWidth('6xl')
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
                                            ->columns(2),

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
                                            ->columns(2),

                                        Section::make('Description')
                                            ->schema([
                                                TextEntry::make('desc')
                                                    ->label('Property Description')
                                                    ->placeholder('No description available')
                                                    ->columnSpanFull()
                                                    ->html(),
                                            ]),
                                    ]),

                            Tab::make('Activities')
                                ->icon('heroicon-o-clipboard-document-list')
                                ->schema([
                                    // Add Activity button

                                    // Sub-tabs for Activity Log and Call Log
                                    Tabs::make('ActivitySubTabs')
                                        ->tabs([
                                            Tab::make('Activity Log')
                                                ->icon('heroicon-o-document-text')
                                                ->schema([
                                                    RepeatableEntry::make('activities')
                                                        ->schema([
                                                            Section::make()
                                                                ->collapsible()
                                                                ->collapsed()
                                                                ->heading(fn($record) => ucfirst($record->activity_type ?? 'Activity'))
                                                                ->description(fn($record) => $record->created_at?->format('M d, Y h:i A'))
                                                                ->schema([
                                                                    TextEntry::make('description')
                                                                        ->label('Details')
                                                                        ->columnSpanFull(),
                                                                    TextEntry::make('notes')
                                                                        ->label('Notes')
                                                                        ->columnSpanFull(),
                                                                    TextEntry::make('assigned_by')
                                                                        ->label('By'),
                                                                    TextEntry::make('created_at')
                                                                        ->label('Created At')
                                                                        ->dateTime(),
                                                                    TextEntry::make('followUp.status')
                                                                        ->label('Follow-up')
                                                                        ->badge(),
                                                                    TextEntry::make('followUp.level_score')
                                                                        ->label('Lead Score'),
                                                                ])
                                                                ->columns(2),
                                                        ])
                                                        ->contained(false)
                                                        //->emptyStateHeading('No activities yet'),
                                                ]),

                                            Tab::make('Call Log')
                                                ->icon('heroicon-o-phone')
                                                ->schema([
                                                    RepeatableEntry::make('call_activities')
                                                        ->schema([
                                                            Section::make()
                                                                ->collapsible()
                                                                ->collapsed()
                                                                ->heading(fn($record) => "📞 Call - " . ($record->status ?? 'Unknown'))
                                                                ->description(fn($record) => $record->created_at?->format('M d, Y h:i A'))
                                                                ->schema([
                                                                    TextEntry::make('status')
                                                                        ->label('Call Status')
                                                                        ->badge(),
                                                                    TextEntry::make('duration')
                                                                        ->label('Duration')
                                                                        ->formatStateUsing(fn($state) => $state ? "{$state} min" : 'N/A'),
                                                                    TextEntry::make('description')
                                                                        ->label('Summary')
                                                                        ->columnSpanFull(),
                                                                    TextEntry::make('notes')
                                                                        ->label('Notes')
                                                                        ->columnSpanFull(),
                                                                    TextEntry::make('assigned_by')
                                                                        ->label('Called By'),
                                                                    TextEntry::make('created_at')
                                                                        ->label('Date')
                                                                        ->dateTime(),
                                                                    TextEntry::make('followUp.status')
                                                                        ->label('Follow-up')
                                                                        ->badge(),
                                                                    TextEntry::make('followUp.level_score')
                                                                        ->label('Level Score'),
                                                                ])
                                                                ->columns(2),
                                                        ])
                                                        ->contained(false)
                                                        //->emptyStateHeading('No calls yet'),
                                                ]),
                                        ]),
                                ]),

                                Tab::make('Pricing')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->schema([
                                        Section::make('Price Information')
                                            ->schema([
                                                TextEntry::make('price')
                                                    ->label('Main Price')
                                                    ->money('LKR')
                                                    ->size('xl')
                                                    ->weight('bold')
                                                    ->color('success'),
                                                TextEntry::make('alt_price')
                                                    ->label('Alternative Price')
                                                    ->formatStateUsing(
                                                        fn($state, $record) =>
                                                        $state ? number_format($state) . ' ' . ($record->alt_currency ?? '') : 'Not set'
                                                    ),
                                                TextEntry::make('price_monthly')
                                                    ->label('Monthly Price')
                                                    ->money('LKR'),
                                                TextEntry::make('price_land_pp')
                                                    ->label('Land Price per Perch')
                                                    ->money('LKR'),
                                                TextEntry::make('price_land_total')
                                                    ->label('Total Land Price')
                                                    ->money('LKR'),
                                                TextEntry::make('price_type')
                                                    ->label('Price Type')
                                                    ->badge(),
                                            ])
                                            ->columns(2),
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
                            ->columnSpanFull()
                            ->visible(fn ($record) => Gate::allows('view', $record)),
                    ]),

                // Add edit action
                EditAction::make()
                    ->visible(fn ($record) => Gate::allows('update', $record))
                    ->slideOver(),
            ])
            ->headerActions([
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
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Property Leads')
                        ->modalDescription('Are you sure you want to delete these property leads? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them')
                        ->visible(fn () => auth()->user()->user_level_id != 1),

                    BulkAction::make('mark_as_active')
                        ->label('Mark as Active')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => 1]);
                            });

                            Notification::make()
                                ->title('Leads Updated')
                                ->body('Selected leads have been marked as active.')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('mark_as_inactive')
                        ->label('Mark as Inactive')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => 0]);
                            });

                            Notification::make()
                                ->title('Leads Updated')
                                ->body('Selected leads have been marked as inactive.')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
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
            ->recordUrl(null); // disable row click
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHunters::route('/'),
            'create' => CreateHunters::route('/create'),
            'edit' => EditHunters::route('/{record}/edit'),
        ];
    }
}
