<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RenewResource\Pages;
use App\Filament\Resources\RenewResource\RelationManagers;
use App\Models\Renew;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RenewResource extends Resource
{
    protected static ?string $model = Renew::class;

    protected static ?string $label = 'Renew';

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
            'index' => Pages\ListRenews::route('/'),
            'create' => Pages\CreateRenew::route('/create'),
            'edit' => Pages\EditRenew::route('/{record}/edit'),
        ];
    }
}
