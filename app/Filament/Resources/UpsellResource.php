<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UpsellResource\Pages;
use App\Filament\Resources\UpsellResource\RelationManagers;
use App\Models\Lead;
use App\Models\Upsell;
use Filament\Forms;
// use Filament\Forms\Components\Actions;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UpsellResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $label = 'Upsell';

    protected static ?string $navigationGroup = 'Private Sellers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'upsell');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Print customer name and user name as new columns
                Tables\Columns\TextColumn::make('customer.firstname')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('heading')
                    ->label('Property Heading')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

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

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // Tables\Columns\IconColumn::make('is_trending')
                //     ->label('Trending')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-fire')
                //     ->falseIcon('heroicon-o-minus')
                //     ->trueColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                // Apply column filters and date filters
                Tables\Filters\Filter::make('posted_date')
                    ->form([
                        Forms\Components\DatePicker::make('posted_date')
                            ->label('Posted Date'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        $query->when(
                            $data['posted_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('posted_date', $date),
                        )
                    ),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'ikman' => 'Ikman',
                        'facebook' => 'Facebook',
                        'website' => 'Website',
                        'referral' => 'Referral',
                    ]),
                Tables\Filters\SelectFilter::make('am')
                    ->options([
                        'john' => 'John',
                        'jane' => 'Jane',
                        'mike' => 'Mike',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'follow_up' => 'Follow Up',
                        'closed' => 'Closed',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('last_update_date')
                    ->form([
                        Forms\Components\DatePicker::make('last_update_date')
                            ->label('Last Update Date'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        $query->when(
                            $data['last_update_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('last_update_date', $date),
                        )
                    ),
                Tables\Filters\SelectFilter::make('last_update_by'),
                Tables\Filters\Filter::make('tel')
                    ->form([
                        Forms\Components\TextInput::make('tel')
                            ->label('Telephone'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        $query->when(
                            $data['tel'],
                            fn (Builder $query, $tel): Builder => $query->where('tel', 'like', "%{$tel}%"),
                        )
                    ),
                Tables\Filters\Filter::make('price')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->label('Min Price')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_price')
                            ->label('Max Price')
                            ->numeric(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            )
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn($record) => 'Hunter Details - ' . $record->name)
                    ->modalWidth('6xl')
                    ->infolist([
                        Tabs::make('HunterTabs')
                            ->tabs([
                                Tab::make('Summary')
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        Section::make('Basic Information')
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Name')
                                                    ->size('lg')
                                                    ->weight('bold'),
                                                TextEntry::make('status')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'new' => 'success',
                                                        'follow_up' => 'warning',
                                                        'closed' => 'primary',
                                                        'rejected' => 'danger',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('tel')
                                                    ->label('Telephone')
                                                    ->icon('heroicon-o-phone'),
                                                TextEntry::make('price')
                                                    ->label('Amount')
                                                    ->money('LKR')
                                                    ->size('lg')
                                                    ->weight('bold')
                                                    ->color('success'),
                                                TextEntry::make('source')
                                                    ->label('Source')
                                                    ->badge()
                                                    ->color('primary'),
                                                TextEntry::make('am')
                                                    ->label('Account Manager'),
                                            ])
                                            ->columns(2),
                                        
                                        Section::make('Property Information')
                                            ->schema([
                                                TextEntry::make('property_type')
                                                    ->label('Property Type')
                                                    ->placeholder('Not specified'),
                                                TextEntry::make('city')
                                                    ->label('City')
                                                    ->icon('heroicon-o-map-pin')
                                                    ->placeholder('Not specified'),
                                                TextEntry::make('location')
                                                    ->label('Location')
                                                    ->placeholder('Not specified'),
                                                TextEntry::make('duration')
                                                    ->label('Duration')
                                                    ->placeholder('Not specified'),
                                                TextEntry::make('ad_type')
                                                    ->label('Ad Type')
                                                    ->badge()
                                                    ->placeholder('Not specified'),
                                            ])
                                            ->columns(2),
                                        
                                        Section::make('Latest Comments')
                                            ->schema([
                                                TextEntry::make('latest_comments')
                                                    ->label('')
                                                    ->placeholder('No comments available')
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),

                                Tab::make('Details')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Actions::make([
                                            InfolistAction::make('edit')
                                                ->label('Edit Hunter Details')
                                                ->icon('heroicon-o-pencil')
                                                ->color('primary')
                                                ->form([
                                                    Forms\Components\TextInput::make('name')
                                                        ->label('Name')
                                                        ->required(),
                                                    Forms\Components\TextInput::make('tel')
                                                        ->label('Telephone')
                                                        ->tel(),
                                                    Forms\Components\Select::make('source')
                                                        ->label('Source')
                                                        ->options([
                                                            'ikman' => 'Ikman',
                                                            'facebook' => 'Facebook',
                                                            'website' => 'Website',
                                                            'referral' => 'Referral',
                                                        ])
                                                        ->required(),
                                                    Forms\Components\TextInput::make('am')
                                                        ->label('Account Manager'),
                                                    Forms\Components\Select::make('property_type')
                                                        ->label('Property Type')
                                                        ->options([
                                                            'house' => 'House',
                                                            'apartment' => 'Apartment',
                                                            'land' => 'Land',
                                                            'commercial' => 'Commercial',
                                                        ]),
                                                    Forms\Components\TextInput::make('price')
                                                        ->label('Price')
                                                        ->numeric()
                                                        ->prefix('LKR'),
                                                    Forms\Components\TextInput::make('city')
                                                        ->label('City'),
                                                    Forms\Components\TextInput::make('location')
                                                        ->label('Location'),
                                                    Forms\Components\TextInput::make('duration')
                                                        ->label('Duration'),
                                                    Forms\Components\Select::make('ad_type')
                                                        ->label('Ad Type')
                                                        ->options([
                                                            'sell' => 'Sell',
                                                            'rent' => 'Rent',
                                                            'lease' => 'Lease',
                                                        ]),
                                                    Forms\Components\Select::make('status')
                                                        ->label('Status')
                                                        ->options([
                                                            'new' => 'New',
                                                            'follow_up' => 'Follow Up',
                                                            'closed' => 'Closed',
                                                            'rejected' => 'Rejected',
                                                        ])
                                                        ->required(),
                                                    Forms\Components\Textarea::make('latest_comments')
                                                        ->label('Latest Comments')
                                                        ->rows(3),
                                                    Forms\Components\TextInput::make('last_update_by')
                                                        ->label('Last Updated By'),
                                                ])
                                                ->fillForm(fn ($record): array => [
                                                    'name' => $record->name,
                                                    'tel' => $record->tel,
                                                    'source' => $record->source,
                                                    'am' => $record->am,
                                                    'property_type' => $record->property_type,
                                                    'price' => $record->price,
                                                    'city' => $record->city,
                                                    'location' => $record->location,
                                                    'duration' => $record->duration,
                                                    'ad_type' => $record->ad_type,
                                                    'status' => $record->status,
                                                    'latest_comments' => $record->latest_comments,
                                                    'last_update_by' => $record->last_update_by,
                                                ])
                                                ->action(function (array $data, $record): void {
                                                    $record->update($data);
                                                })
                                                ->modalHeading('Edit Hunter Details')
                                                ->modalSubmitActionLabel('Save Changes')
                                                ->modalWidth('4xl'),
                                        ]),
                                        
                                        Section::make('Non-Editable Information')
                                            ->description('These fields are system-managed and cannot be edited')
                                            ->schema([
                                                TextEntry::make('posted_date')
                                                    ->label('Posted Date')
                                                    ->date('M d, Y')
                                                    ->icon('heroicon-o-calendar'),
                                                TextEntry::make('last_update_date')
                                                    ->label('Last Update Date')
                                                    ->dateTime('M d, Y H:i')
                                                    ->icon('heroicon-o-clock'),
                                                TextEntry::make('user_type_id')
                                                    ->label('User Type ID')
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        '2' => 'Premium User',
                                                        '3' => 'Standard User',
                                                        default => 'Unknown Type',
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

                                Tab::make('Activity')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        Section::make('Recent Activity')
                                            ->schema([
                                                TextEntry::make('posted_date')
                                                    ->label('Posted Date')
                                                    ->date('M d, Y')
                                                    ->icon('heroicon-o-calendar'),
                                                TextEntry::make('last_update_date')
                                                    ->label('Last Update Date')
                                                    ->dateTime('M d, Y H:i')
                                                    ->icon('heroicon-o-clock'),
                                                TextEntry::make('last_update_by')
                                                    ->label('Last Updated By')
                                                    ->icon('heroicon-o-user'),
                                                TextEntry::make('status')
                                                    ->label('Current Status')
                                                    ->badge(),
                                            ])
                                            ->columns(2),
                                        
                                        Section::make('Comments History')
                                            ->schema([
                                                TextEntry::make('latest_comments')
                                                    ->label('Latest Comments')
                                                    ->placeholder('No comments available')
                                                    ->columnSpanFull(),
                                            ]),
                                        
                                        Section::make('System Information')
                                            ->schema([
                                                TextEntry::make('user_type_id')
                                                    ->label('User Type ID'),
                                                TextEntry::make('created_at')
                                                    ->label('Created At')
                                                    ->dateTime('M d, Y H:i'),
                                                TextEntry::make('updated_at')
                                                    ->label('Updated At')
                                                    ->dateTime('M d, Y H:i'),
                                            ])
                                            ->columns(3),
                                    ]),

                                Tab::make('Stats')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        Section::make('Lead Statistics')
                                            ->schema([
                                                TextEntry::make('price')
                                                    ->label('Lead Value')
                                                    ->money('LKR')
                                                    ->size('xl')
                                                    ->weight('bold')
                                                    ->color('success'),
                                                TextEntry::make('status')
                                                    ->label('Status Category')
                                                    ->badge()
                                                    ->size('lg'),
                                                TextEntry::make('source')
                                                    ->label('Lead Source')
                                                    ->badge()
                                                    ->color('info'),
                                                TextEntry::make('user_type_id')
                                                    ->label('User Type')
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        '2' => 'Premium User',
                                                        '3' => 'Standard User',
                                                        default => 'Unknown Type',
                                                    })
                                                    ->badge(),
                                            ])
                                            ->columns(2),
                                        
                                        Section::make('Time Analysis')
                                            ->schema([
                                                TextEntry::make('created_at')
                                                    ->label('Days Since Created')
                                                    ->formatStateUsing(fn ($state): string => 
                                                        $state ? now()->diffInDays($state) . ' days ago' : 'N/A'
                                                    )
                                                    ->icon('heroicon-o-calendar'),
                                                TextEntry::make('last_update_date')
                                                    ->label('Days Since Last Update')
                                                    ->formatStateUsing(fn ($state): string => 
                                                        $state ? now()->diffInDays($state) . ' days ago' : 'N/A'
                                                    )
                                                    ->icon('heroicon-o-clock'),
                                                TextEntry::make('duration')
                                                    ->label('Project Duration')
                                                    ->placeholder('Not specified')
                                                    ->icon('heroicon-o-arrow-trending-up'),
                                            ])
                                            ->columns(1),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
                    // Add separate edit action
                    // Tables\Actions\EditAction::make()
                    //     ->slideOver(),
            ])
            ->recordUrl(null); // disable row click
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
            'index' => Pages\ListUpsells::route('/'),
            'create' => Pages\CreateUpsell::route('/create'),
            'edit' => Pages\EditUpsell::route('/{record}/edit'),
        ];
    }
}
