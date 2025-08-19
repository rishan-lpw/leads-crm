<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerMergeResource\Pages;
use App\Filament\Resources\CustomerMergeResource\RelationManagers;
use App\Models\CustomerMerge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerMergeResource extends Resource
{
    protected static ?string $model = CustomerMerge::class;

    protected static ?string $navigationLabel = 'Customer Merge';

    protected static ?string $navigationGroup = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Primary and secondary customers can be select from customer table.
                Forms\Components\Select::make('primary_customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Forms\Components\Select::make('secondary_customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('merge_at')
                    ->required()
                    ->default(now()),
                // Merged By select the user.name from user table
                Forms\Components\Select::make('merged_by')
                    ->relationship('user', 'name')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Primary Customer'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Secondary Customer'),
                Tables\Columns\TextColumn::make('merge_at')
                    ->label('Merge Date'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Merged By'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCustomerMerges::route('/'),
            'create' => Pages\CreateCustomerMerge::route('/create'),
            'edit' => Pages\EditCustomerMerge::route('/{record}/edit'),
        ];
    }
}
