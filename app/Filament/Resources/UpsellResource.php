<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UpsellResource\Pages;
use App\Filament\Resources\UpsellResource\RelationManagers;
use App\Models\Upsell;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UpsellResource extends Resource
{
    protected static ?string $model = Upsell::class;

    protected static ?string $label = 'Upsell';

    protected static ?string $navigationGroup = 'Private Sellers';

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
            'index' => Pages\ListUpsells::route('/'),
            'create' => Pages\CreateUpsell::route('/create'),
            'edit' => Pages\EditUpsell::route('/{record}/edit'),
        ];
    }
}
