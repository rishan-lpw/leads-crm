<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddOnResource\Pages;
use App\Filament\Resources\AddOnResource\RelationManagers;
use App\Models\AddOn;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('title')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Textarea::make('location')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('category_id')
                    ->required()
                    ->relationship('category', 'name')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable()
                    ->label('Customer')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('customer.phone_number')
                //     ->label('Phone No.')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->label('Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.status')
                    ->sortable()
                    ->label('Status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.description')
                    ->sortable()
                    ->label('Description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->prefix('$'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->sortable()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // I want to add a filter for the category
            ->filters([
            // Add a proper category filter
            Tables\Filters\SelectFilter::make('category')
                ->relationship('category', 'name')
                ->label('Filter by Category'),
            Tables\Filters\Filter::make('price_range')
                ->form([
                    Forms\Components\Select::make('price_range')
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
            Tables\Filters\Filter::make('customer')
                ->form([
                    Forms\Components\Select::make('customer')
                        ->options(Customer::all()->pluck('name', 'id'))
                        ->label('Filter by Customer'),
                ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAddOns::route('/'),
            'create' => Pages\CreateAddOn::route('/create'),
            'edit' => Pages\EditAddOn::route('/{record}/edit'),
        ];
    }
}
