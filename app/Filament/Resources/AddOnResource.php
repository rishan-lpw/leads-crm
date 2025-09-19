<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AddOnResource\Pages\ListAddOns;
use App\Filament\Resources\AddOnResource\Pages\CreateAddOn;
use App\Filament\Resources\AddOnResource\Pages\EditAddOn;
use App\Filament\Resources\AddOnResource\Pages;
use App\Filament\Resources\AddOnResource\RelationManagers;
use App\Models\AddOn;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Resource;
use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddOnResource extends Resource
{
    protected static ?string $model = AddOn::class;

    protected static ?string $navigationLabel = 'Add List';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-rectangle-group';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('title')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Textarea::make('location')
                    ->required()
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->required()
                    ->relationship('category', 'name')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->sortable()
                    ->label('Customer')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('customer.phone_number')
                //     ->label('Phone No.')
                //     ->searchable(),
                TextColumn::make('description')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('location')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->label('Category')
                    ->searchable(),
                TextColumn::make('category.status')
                    ->sortable()
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('category.description')
                    ->sortable()
                    ->label('Description')
                    ->searchable(),
                TextColumn::make('price')
                    ->sortable()
                    ->prefix('$'),
                TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->sortable()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // I want to add a filter for the category
            ->filters([
            // Add a proper category filter
            SelectFilter::make('category')
                ->relationship('category', 'name')
                ->label('Filter by Category'),
            Filter::make('price_range')
                ->schema([
                    Select::make('price_range')
                        ->options([
                            'under_50' => 'Under $50',
                            '50_100' => '$50 to $100',
                            'over_100' => 'Over $100',
                        ])
                        ->label('Price Range'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query->when($data['price_range'] === 'under_50', function (Builder $q) {
                        return $q->where('price', '<', 50);
                    })->when($data['price_range'] === '50_100', function (Builder $q) {
                        return $q->whereBetween('price', [50, 100]);
                    })->when($data['price_range'] === 'over_100', function (Builder $q) {
                        return $q->where('price', '>', 100);
                    });
                }),
            Filter::make('customer')
                ->schema([
                    Select::make('customer')
                        ->options(Customer::all()->pluck('name', 'id'))
                        ->label('Filter by Customer'),
                ])
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListAddOns::route('/'),
            'create' => CreateAddOn::route('/create'),
            'edit' => EditAddOn::route('/{record}/edit'),
        ];
    }
}
