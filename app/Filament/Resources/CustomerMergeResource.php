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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCustomerMerges::route('/'),
            'create' => Pages\CreateCustomerMerge::route('/create'),
            'edit' => Pages\EditCustomerMerge::route('/{record}/edit'),
        ];
    }
}
