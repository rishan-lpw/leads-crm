<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\NotInterestedResource\Pages\ListNotInteresteds;
use App\Filament\Resources\NotInterestedResource\Pages;
use App\Filament\Resources\NotInterestedResource\RelationManagers;
use App\Models\NotInterested;
use Filament\Forms;
use App\Models\Lead;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NotInterestedResource\Components\TableColumns as NIColumns;

class NotInterestedResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Private Sellers';

    protected static ?string $navigationLabel = 'Not Interested';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-eye-slash';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'not_interested');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(NIColumns::get())
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'ikman' => 'Ikman',
                        'facebook' => 'Facebook',
                        'other' => 'Other',
                    ])
                    ->placeholder('All Sources'),
                SelectFilter::make('type')
                    ->label('Listing Type')
                    ->options([
                        'sell' => 'Sell',
                        'rent' => 'Rent',
                        'lease' => 'Lease',
                    ])
                    ->placeholder('All Types'),
                SelectFilter::make('propty_type')
                    ->label('Property Type')
                    ->options([
                        'house' => 'House',
                        'apartment' => 'Apartment',
                        'land' => 'Land',
                        'commercial' => 'Commercial',
                        'villa' => 'Villa',
                    ])
                    ->placeholder('All Property Types'),
                // Price range filter
                Filter::make('price_range')
                    ->schema([
                        TextInput::make('min_price')
                            ->label('Min Price')
                            ->numeric()
                            ->prefix('LKR'),
                        TextInput::make('max_price')
                            ->label('Max Price')
                            ->numeric()
                            ->prefix('LKR'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_price'], fn (Builder $query, $value) => $query->where('price', '>=', $value))
                            ->when($data['max_price'], fn (Builder $query, $value) => $query->where('price', '<=', $value));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['min_price'] && $data['max_price']) {
                            return 'Price: ' . 'LKR' . number_format($data['min_price']) . ' - LKR' . number_format($data['max_price']);
                        }

                        if ($data['min_price']) {
                            return 'Price: ' . 'LKR' . number_format($data['min_price']) . ' and up';
                        }

                        if ($data['max_price']) {
                            return 'Price: ' . 'LKR' . number_format($data['max_price']) . ' and below';
                        }

                        return null;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn($record) => 'Hunter Details - ' . $record->name)
                    ->modalWidth('6xl')
                    ->schema([
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
                                            Action::make('edit')
                                                ->label('Edit Hunter Details')
                                                ->icon('heroicon-o-pencil')
                                                ->color('primary')
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->label('Name')
                                                        ->required(),
                                                    TextInput::make('tel')
                                                        ->label('Telephone')
                                                        ->tel(),
                                                    Select::make('source')
                                                        ->label('Source')
                                                        ->options([
                                                            'ikman' => 'Ikman',
                                                            'facebook' => 'Facebook',
                                                            'website' => 'Website',
                                                            'referral' => 'Referral',
                                                        ])
                                                        ->required(),
                                                    TextInput::make('am')
                                                        ->label('Account Manager'),
                                                    Select::make('property_type')
                                                        ->label('Property Type')
                                                        ->options([
                                                            'house' => 'House',
                                                            'apartment' => 'Apartment',
                                                            'land' => 'Land',
                                                            'commercial' => 'Commercial',
                                                        ]),
                                                    TextInput::make('price')
                                                        ->label('Price')
                                                        ->numeric()
                                                        ->prefix('LKR'),
                                                    TextInput::make('city')
                                                        ->label('City'),
                                                    TextInput::make('location')
                                                        ->label('Location'),
                                                    TextInput::make('duration')
                                                        ->label('Duration'),
                                                    Select::make('ad_type')
                                                        ->label('Ad Type')
                                                        ->options([
                                                            'sell' => 'Sell',
                                                            'rent' => 'Rent',
                                                            'lease' => 'Lease',
                                                        ]),
                                                    Select::make('status')
                                                        ->label('Status')
                                                        ->options([
                                                            'new' => 'New',
                                                            'follow_up' => 'Follow Up',
                                                            'closed' => 'Closed',
                                                            'rejected' => 'Rejected',
                                                        ])
                                                        ->required(),
                                                    Textarea::make('latest_comments')
                                                        ->label('Latest Comments')
                                                        ->rows(3),
                                                    TextInput::make('last_update_by')
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

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::where('status', 'not_interested')->count();
        return $count > 0 ? (string)$count : null;
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
            'index' => ListNotInteresteds::route('/'),
        ];
    }
}
