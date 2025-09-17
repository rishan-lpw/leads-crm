<?php

namespace App\Filament\Resources;

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
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Forms\Form;
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
use Filament\Tables\Actions\Action;
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

    protected static ?string $navigationGroup = 'Private Sellers';

    protected static ?string $navigationIcon = 'heroicon-o-users';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('heading')
                            ->label('Property Heading')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Listing Type')
                            ->options([
                                'sell' => 'For Sale',
                                'rent' => 'For Rent',
                                'lease' => 'For Lease',
                            ])
                            ->required(),
                        Forms\Components\Select::make('propty_type')
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
                        Forms\Components\Select::make('service_type')
                            ->label('Service Type')
                            ->options([
                                'complete' => 'Complete Service',
                                'basic' => 'Basic Listing',
                                'premium' => 'Premium Service',
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location Details')
                    ->schema([
                        Forms\Components\TextInput::make('street')
                            ->label('Street Address')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label('City')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.00000001),
                        Forms\Components\TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.00000001),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Property Description')
                    ->schema([
                        Forms\Components\Textarea::make('desc')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pricing Information')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Main Price')
                            ->numeric()
                            ->prefix('LKR'),
                        Forms\Components\TextInput::make('alt_price')
                            ->label('Alternative Price')
                            ->numeric(),
                        Forms\Components\Select::make('alt_currency')
                            ->label('Alternative Currency')
                            ->options([
                                'LKR' => 'LKR',
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ]),
                        Forms\Components\Select::make('price_type')
                            ->label('Price Type')
                            ->options([
                                'total' => 'Total Price',
                                'per_sq_ft' => 'Per Square Foot',
                                'per_month' => 'Per Month',
                                'negotiable' => 'Negotiable',
                            ]),
                        Forms\Components\TextInput::make('price_monthly')
                            ->label('Monthly Price')
                            ->numeric()
                            ->prefix('LKR'),
                        Forms\Components\TextInput::make('price_land_pp')
                            ->label('Land Price per Perch')
                            ->numeric()
                            ->prefix('LKR'),
                        Forms\Components\TextInput::make('price_land_total')
                            ->label('Total Land Price')
                            ->numeric()
                            ->prefix('LKR'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Select::make('contact_type')
                            ->label('Contact Type')
                            ->options([
                                'owner' => 'Property Owner',
                                'agent' => 'Real Estate Agent',
                                'developer' => 'Developer',
                            ]),
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media & Links')
                    ->schema([
                        Forms\Components\Toggle::make('pic')
                            ->label('Has Pictures'),
                        Forms\Components\TextInput::make('pic_count')
                            ->label('Picture Count')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('youtube_link')
                            ->label('YouTube Link')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('video_link')
                            ->label('Video Link')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('image_360')
                            ->label('360° Image Link')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Settings')
                    ->schema([
                        Forms\Components\Select::make('status')
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
                        Forms\Components\Select::make('source')
                            ->label('Lead Source')
                            ->options([
                                'pending_payments' => 'Pending Payments',
                                'ikman' => 'IKMAN',
                                'facebook' => 'Facebook',
                                'other' => 'Other',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('weight')
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
                        Forms\Components\Toggle::make('is_trending')
                            ->label('Trending'),
                        Forms\Components\Toggle::make('blocked')
                            ->label('Blocked')
                            ->helperText('Block this listing from public view'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('System Fields')
                    ->schema([
                        Forms\Components\TextInput::make('ad_id')
                            ->label('Advertisement ID')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('cust_id')
                            ->label('Customer ID')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('user_id')
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
                Tables\Columns\TextColumn::make('customer.firstname')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('AM Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('heading')
                    ->label('Property Heading')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Priority Weight')
                    ->sortable()
                    ->badge(),
                // ->formatStateUsing(fn ($record) => $record->weight . ' (' . \App\Services\LeadWeightService::getWeightLevel($record->weight) . ')')
                // ->color(fn ($record) => \App\Services\LeadWeightService::getWeightColor($record->weight)),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Listing Type')
                    ->colors([
                        'primary' => 'sell',
                        'success' => 'rent',
                        'warning' => 'lease',
                    ])
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('propty_type')
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

                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('LKR')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_type')
                    ->label('Contact Type')
                    ->badge()
                    ->colors([
                        'primary' => 'owner',
                        'success' => 'agent',
                        'warning' => 'developer',
                    ]),

                Tables\Columns\TextColumn::make('status')
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

                Tables\Columns\TextColumn::make('source')
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

                Tables\Columns\TextColumn::make('is_active')
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

                Tables\Columns\TextColumn::make('posted_date')
                    ->label('Posted Date')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                // Add required filters
                // Add user name filter
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Listing Type')
                    ->options([
                        'sell' => 'For Sale',
                        'rent' => 'For Rent',
                        'lease' => 'For Lease',
                    ]),
                Tables\Filters\SelectFilter::make('propty_type')
                    ->label('Property Type')
                    ->options([
                        'house' => 'House',
                        'apartment' => 'Apartment',
                        'land' => 'Land',
                        'commercial' => 'Commercial',
                        'villa' => 'Villa',
                        'townhouse' => 'Townhouse',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'New',
                        'follow_up' => 'Follow Up',
                        'system' => 'System',
                        'to_be_expired' => 'To Be Expired',
                        'expired' => 'Expired',
                    ]),

                // Weight filter for low, high, medium
                Tables\Filters\SelectFilter::make('weight')
                    ->label('Weight')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),

                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'pending_payments' => 'Pending Payments',
                        'ikman' => 'IKMAN',
                        'facebook' => 'Facebook',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        0 => 'Inactive',
                        1 => 'Active',
                        2 => 'Special',
                        3 => 'Pending',
                    ]),
                // posted_date filter
                Tables\Filters\Filter::make('posted_date')
                    ->form([
                        Forms\Components\DatePicker::make('posted_date_from')
                            ->label('Posted Date From'),
                        Forms\Components\DatePicker::make('posted_date_to')
                            ->label('Posted Date To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['posted_date_from'], fn(Builder $query, $value) => $query->whereDate('posted_date', '>=', $value))
                            ->when($data['posted_date_to'], fn(Builder $query, $value) => $query->whereDate('posted_date', '<=', $value));
                    })
                    ->label('Posted Date Range'),

                // price range filter
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('price_min')
                            ->label('Min Price')
                            ->numeric()
                            ->prefix('LKR'),
                        Forms\Components\TextInput::make('price_max')
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

            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn($record) => 'Property Details - ' . $record->heading)
                    ->modalWidth('6xl')
                    ->infolist([
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

                                        // Add new activity button and call the EditActivity.php file and its action into that.
                                        // Actions\Action::make('add_activity')
                                        //     ->label('Add Activity')
                                        //     ->button()
                                        //     ->color('primary')
                                        //     ->icon('heroicon-o-plus')
                                        //     ->action(function (Lead $record, array $data): void {
                                        //         // Create a new activity related to this lead
                                        //         $record->activities()->create([
                                        //             'activity_type' => $data['activity_type'],
                                        //             'description' => $data['description'],
                                        //             'status' => $data['status'],
                                        //             'created_by' => auth()->user()->name,
                                        //         ]);

                                        //         Notification::make()
                                        //             ->title('Activity Added')
                                        //             ->success()
                                        //             ->send();
                                        //     })
                                        //     ->form([
                                        //         Forms\Components\Select::make('activity_type')
                                        //             ->label('Activity Type')
                                        //             ->options([
                                        //                 'call' => 'Call',
                                        //                 'email' => 'Email',
                                        //                 'meeting' => 'Meeting',
                                        //                 'note' => 'Note',
                                        //                 'other' => 'Other',
                                        //             ])
                                        //             ->required(),
                                        //         Forms\Components\Textarea::make('description')
                                        //             ->label('Description')
                                        //             ->rows(3)
                                        //             ->required(),
                                        //         Forms\Components\Select::make('status')
                                        //             ->label('Status')
                                        //             ->options([
                                        //                 'Pending' => 'Pending',
                                        //                 'Success' => 'Success',
                                        //                 'Failed' => 'Failed',
                                        //             ])
                                        //             ->required(),
                                        //     ])
                                        //     ->modalWidth('md'),

                                        RepeatableEntry::make('activities')
                                            // ->relationship('activities')
                                            // uses Lead::activities()
                                            ->schema([
                                                Section::make()
                                                    ->collapsible()
                                                    ->collapsed()
                                                    ->heading(fn($record) => $record->activity_type ?? 'Activity')
                                                    ->description(fn($record) => $record->created_at?->format('Y-m-d H:i:s'))
                                                    ->schema([
                                                        TextEntry::make('status')
                                                            ->badge()
                                                            ->color(fn($state) => match ($state) {
                                                                'Success' => 'success',
                                                                'Failed' => 'danger',
                                                                'Pending' => 'warning',
                                                                default => 'gray',
                                                            }),
                                                        TextEntry::make('description')
                                                            ->label('Details')
                                                            ->columnSpanFull(),
                                                        TextEntry::make('created_by')
                                                            ->label('By')
                                                            ->placeholder('System'),
                                                    ]),
                                            ])
                                        // ->orderable(false),
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
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => Gate::allows('update', $record))
                    ->slideOver(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync_api_data')
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
                                        } catch (\Exception $e) {
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Property Leads')
                        ->modalDescription('Are you sure you want to delete these property leads? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them')
                        ->visible(fn () => auth()->user()->user_level_id != 1),

                    Tables\Actions\BulkAction::make('mark_as_active')
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

                    Tables\Actions\BulkAction::make('mark_as_inactive')
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

                    Tables\Actions\BulkAction::make('export_selected')
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
            'index' => Pages\ListHunters::route('/'),
            'create' => Pages\CreateHunters::route('/create'),
            'edit' => Pages\EditHunters::route('/{record}/edit'),
        ];
    }
}
