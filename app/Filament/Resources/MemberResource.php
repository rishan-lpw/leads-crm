<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Customer;
use App\Models\Member;
use App\Models\User;
use Filament\Forms;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Members';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Member Name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                Forms\Components\Textarea::make('address')
                    ->label('Address'),
                Forms\Components\TextInput::make('phone_number')
                    ->label('Contact Number'),
                Forms\Components\Select::make('membership_status')
                    ->label('Membership Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        'expired' => 'Expired',
                    ])
                    ->required(),
                Forms\Components\Select::make('membership_category')
                    ->label('Membership Category')
                    ->options([
                        'basic' => 'Basic',
                        'premium' => 'Premium',
                        'vip' => 'VIP',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('payment_exp_date')
                    ->label('Payment Expiry Date'),
                Forms\Components\DatePicker::make('membership_exp_date')
                    ->label('Membership Expiry Date'),
                Forms\Components\TextInput::make('available_boosts_source')
                    ->label('Available Boosts')
                    ->numeric()
                    ->default(0),
                Forms\Components\DatePicker::make('last_boost_added_date')
                    ->label('Last Boost Added Date'),
                Forms\Components\TextInput::make('ad_url')
                    ->label('Ad URL')
                    ->url(),
                Forms\Components\Textarea::make('customer_remarks')
                    ->label('Customer Remarks'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Member Name')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),

                // Add colourful badges for membership status
                Tables\Columns\TextColumn::make('membership_status')
                    ->label('Membership Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'suspended' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('membership_category')
                    ->label('Membership Category')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'basic' => 'gray',
                        'premium' => 'warning',
                        'vip' => 'success',
                        default => 'gray',
                    }),

                // AM name: user.name
                Tables\Columns\TextColumn::make('user.name')
                    ->label('AM')
                    ->sortable()
                    ->searchable(),

                // Add a field called No. of Ads as show the ads count from add_on table for a relevant member
                Tables\Columns\TextColumn::make('ads_count')
                    ->label('No. of Ads')
                    ->formatStateUsing(function ($record) {
                        return DB::table('add_on')
                            ->where('customer_id', $record->id)
                            ->count();
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state == 0 => 'gray',
                        $state <= 5 => 'warning',
                        $state <= 15 => 'success',
                        default => 'info',
                    })
                    ->sortable(false),

                // Payment Expiration Date
                Tables\Columns\TextColumn::make('payment_exp_date')
                    ->label('Payment Exp. Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        return \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success';
                    }),

                // Membership Expiration Date
                Tables\Columns\TextColumn::make('membership_exp_date')
                    ->label('Membership Exp. Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        return \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success';
                    }),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('membership_status')
                    ->label('Membership Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        'expired' => 'Expired',
                    ]),
                
                Tables\Filters\SelectFilter::make('membership_category')
                    ->label('Membership Category')
                    ->options([
                        'basic' => 'Basic',
                        'premium' => 'Premium',
                        'vip' => 'VIP',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn($record) => 'Member Details - ' . $record->name)
                    ->modalWidth('6xl')
                    ->infolist([
                        Tabs::make('MemberTabs')
                            ->tabs([
                                 Tab::make('Summary')
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        Section::make('Basic Information')
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Member Name')
                                                    ->size('lg')
                                                    ->weight('bold'),
                                                TextEntry::make('email')
                                                    ->label('Email')
                                                    ->icon('heroicon-o-envelope'),
                                                TextEntry::make('phone_number')
                                                    ->label('Contact Number')
                                                    ->icon('heroicon-o-phone'),
                                                TextEntry::make('address')
                                                    ->label('Address')
                                                    ->placeholder('Not provided')
                                                    ->icon('heroicon-o-map-pin'),
                                                TextEntry::make('membership_status')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'active' => 'success',
                                                        'inactive' => 'danger',
                                                        'suspended' => 'warning',
                                                        'expired' => 'danger',
                                                        default => 'gray',
                                                    }),
                                            ])
                                            ->columns(2),
                                        
                                        Section::make('Membership Details')
                                            ->schema([
                                                TextEntry::make('membership_category')
                                                    ->label('Category')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'basic' => 'gray',
                                                        'premium' => 'warning',
                                                        'vip' => 'success',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('ads_count')
                                                    ->label('Total Ads')
                                                    ->formatStateUsing(function ($record) {
                                                        return DB::table('add_on')
                                                            ->where('customer_id', $record->id)
                                                            ->count();
                                                    })
                                                    ->badge()
                                                    ->color('info'),
                                                TextEntry::make('available_boosts_source')
                                                    ->label('Available Boosts')
                                                    ->badge()
                                                    ->color('warning'),
                                                TextEntry::make('payment_exp_date')
                                                    ->label('Payment Expiry Date')
                                                    ->date('M d, Y')
                                                    ->placeholder('Not set')
                                                    ->color(function ($state) {
                                                        if (!$state) return 'gray';
                                                        return \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success';
                                                    }),
                                                TextEntry::make('membership_exp_date')
                                                    ->label('Membership Expiry Date')
                                                    ->date('M d, Y')
                                                    ->placeholder('Not set')
                                                    ->color(function ($state) {
                                                        if (!$state) return 'gray';
                                                        return \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success';
                                                    }),
                                                TextEntry::make('last_boost_added_date')
                                                    ->label('Last Boost Added')
                                                    ->date('M d, Y')
                                                    ->placeholder('Never'),
                                            ])
                                            ->columns(3),
                                        
                                        Section::make('Additional Information')
                                            ->schema([
                                                TextEntry::make('ad_url')
                                                    ->label('Ad URL')
                                                    ->placeholder('No URL provided')
                                                    ->url(fn ($record) => $record->ad_url)
                                                    ->openUrlInNewTab(),
                                                TextEntry::make('customer_remarks')
                                                    ->label('Customer Remarks')
                                                    ->placeholder('No remarks')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(1),
                                    ]),

                                Tab::make('Details')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Actions::make([
                                            InfolistAction::make('edit')
                                                ->label('Edit Member Details')
                                                ->icon('heroicon-o-pencil')
                                                ->color('primary')
                                                ->form([
                                                    Forms\Components\TextInput::make('name')
                                                        ->label('Member Name')
                                                        ->required(),
                                                    Forms\Components\TextInput::make('email')
                                                        ->label('Email')
                                                        ->email()
                                                        ->required(),
                                                    Forms\Components\Textarea::make('address')
                                                        ->label('Address'),
                                                    Forms\Components\TextInput::make('phone_number')
                                                        ->label('Contact Number'),
                                                    Forms\Components\Select::make('membership_status')
                                                        ->label('Membership Status')
                                                        ->options([
                                                            'active' => 'Active',
                                                            'inactive' => 'Inactive',
                                                            'suspended' => 'Suspended',
                                                            'expired' => 'Expired',
                                                        ])
                                                        ->required(),
                                                    Forms\Components\Select::make('membership_category')
                                                        ->label('Membership Category')
                                                        ->options([
                                                            'basic' => 'Basic',
                                                            'premium' => 'Premium',
                                                            'vip' => 'VIP',
                                                        ])
                                                        ->required(),
                                                    Forms\Components\DatePicker::make('payment_exp_date')
                                                        ->label('Payment Expiry Date'),
                                                    Forms\Components\DatePicker::make('membership_exp_date')
                                                        ->label('Membership Expiry Date'),
                                                    Forms\Components\TextInput::make('available_boosts_source')
                                                        ->label('Available Boosts')
                                                        ->numeric()
                                                        ->default(0),
                                                    Forms\Components\DatePicker::make('last_boost_added_date')
                                                        ->label('Last Boost Added Date'),
                                                    Forms\Components\TextInput::make('ad_url')
                                                        ->label('Ad URL')
                                                        ->url(),
                                                    Forms\Components\Textarea::make('customer_remarks')
                                                        ->label('Customer Remarks'),
                                                ])
                                                ->fillForm(fn ($record): array => [
                                                    'name' => $record->name,
                                                    'email' => $record->email,
                                                    'address' => $record->address,
                                                    'phone_number' => $record->phone_number,
                                                    'membership_status' => $record->membership_status,
                                                    'membership_category' => $record->membership_category,
                                                    'payment_exp_date' => $record->payment_exp_date,
                                                    'membership_exp_date' => $record->membership_exp_date,
                                                    'available_boosts_source' => $record->available_boosts_source,
                                                    'last_boost_added_date' => $record->last_boost_added_date,
                                                    'ad_url' => $record->ad_url,
                                                    'customer_remarks' => $record->customer_remarks,
                                                ])
                                                ->action(function (array $data, $record): void {
                                                    $record->update($data);
                                                })
                                                ->slideOver() // Changed from modal to slideOver
                                                ->stickyModalHeader()
                                                ->stickyModalFooter(),
                                        ]),
                                        
                                        Section::make('Non-Editable Information')
                                            ->description('These fields are system-managed and cannot be edited')
                                            ->schema([
                                                TextEntry::make('id')
                                                    ->label('Customer ID')
                                                    ->icon('heroicon-o-hashtag'),
                                                TextEntry::make('add_id')
                                                    ->label('Associated User ID')
                                                    ->icon('heroicon-o-link'),
                                                TextEntry::make('role_id')
                                                    ->label('Role ID')
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        '1' => 'Administrator',
                                                        '2' => 'Manager',
                                                        '3' => 'Member',
                                                        default => 'Unknown Role',
                                                    })
                                                    ->badge(),
                                                TextEntry::make('created_at')
                                                    ->label('Created At')
                                                    ->dateTime('M d, Y H:i')
                                                    ->icon('heroicon-o-plus'),
                                                TextEntry::make('updated_at')
                                                    ->label('Updated At')
                                                    ->dateTime('M d, Y H:i')
                                                    ->icon('heroicon-o-pencil'),
                                            ])
                                            ->columns(2)
                                            ->collapsed(),
                                    ]),

                                Tab::make('Stats')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        Section::make('Membership Statistics')
                                            ->schema([
                                                TextEntry::make('total_ads')
                                                    ->label('Total Ads')
                                                    ->formatStateUsing(function ($record) {
                                                        return DB::table('add_on')
                                                            ->where('customer_id', $record->id)
                                                            ->count();
                                                    })
                                                    ->badge()
                                                    ->size('lg')
                                                    ->color('primary'),
                                                
                                                TextEntry::make('membership_category')
                                                    ->label('Membership Level')
                                                    ->badge()
                                                    ->size('lg'),
                                                
                                                TextEntry::make('available_boosts_source')
                                                    ->label('Available Boosts')
                                                    ->badge()
                                                    ->size('lg')
                                                    ->color('warning'),
                                                
                                                TextEntry::make('membership_status')
                                                    ->label('Account Status')
                                                    ->badge()
                                                    ->size('lg'),
                                            ])
                                            ->columns(2),
                                        
                                        Section::make('Time Analysis')
                                            ->schema([
                                                TextEntry::make('created_at')
                                                    ->label('Member Since')
                                                    ->dateTime('M d, Y')
                                                    ->icon('heroicon-o-calendar'),
                                                TextEntry::make('days_since_join')
                                                    ->label('Days as Member')
                                                    ->formatStateUsing(fn ($record): string => 
                                                        $record->created_at ? now()->diffInDays($record->created_at) . ' days' : 'N/A'
                                                    )
                                                    ->icon('heroicon-o-clock'),
                                                TextEntry::make('membership_exp_date')
                                                    ->label('Membership Expires')
                                                    ->date('M d, Y')
                                                    ->color(function ($state) {
                                                        if (!$state) return 'gray';
                                                        return \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success';
                                                    }),
                                                TextEntry::make('days_until_expiry')
                                                    ->label('Days Until Expiry')
                                                    ->formatStateUsing(function ($record): string {
                                                        if (!$record->membership_exp_date) return 'Never expires';
                                                        $days = now()->diffInDays($record->membership_exp_date, false);
                                                        return $days > 0 ? $days . ' days' : 'Expired';
                                                    })
                                                    ->color(function ($record) {
                                                        if (!$record->membership_exp_date) return 'gray';
                                                        $days = now()->diffInDays($record->membership_exp_date, false);
                                                        return $days <= 0 ? 'danger' : ($days <= 30 ? 'warning' : 'success');
                                                    }),
                                            ])
                                            ->columns(2),
                                    ]),

                                Tab::make('Activity')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        Section::make('Recent Activity')
                                            ->schema([
                                                TextEntry::make('created_at')
                                                    ->label('Account Created')
                                                    ->dateTime('M d, Y H:i')
                                                    ->icon('heroicon-o-user-plus'),
                                                TextEntry::make('updated_at')
                                                    ->label('Last Updated')
                                                    ->dateTime('M d, Y H:i')
                                                    ->icon('heroicon-o-pencil'),
                                                TextEntry::make('last_boost_added_date')
                                                    ->label('Last Boost Added')
                                                    ->date('M d, Y')
                                                    ->placeholder('No boosts added')
                                                    ->icon('heroicon-o-arrow-trending-up'),
                                            ])
                                            ->columns(1),
                                        
                                        Section::make('Account Management')
                                            ->schema([
                                                TextEntry::make('user.name')
                                                    ->label('Account Manager')
                                                    ->placeholder('No AM assigned')
                                                    ->icon('heroicon-o-user'),
                                                TextEntry::make('role_id')
                                                    ->label('Role ID')
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        '1' => 'Administrator',
                                                        '2' => 'Manager',
                                                        '3' => 'Member',
                                                        default => 'Unknown Role',
                                                    })
                                                    ->badge(),
                                            ])
                                            ->columns(2),
                                        
                                        Section::make('System Information')
                                            ->schema([
                                                TextEntry::make('id')
                                                    ->label('Customer ID'),
                                                TextEntry::make('add_id')
                                                    ->label('Associated User ID'),
                                                TextEntry::make('customer_remarks')
                                                    ->label('Internal Remarks')
                                                    ->placeholder('No remarks')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->collapsed(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
                
                // Add a separate edit action in the table
                Tables\Actions\EditAction::make()
                    ->slideOver(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
