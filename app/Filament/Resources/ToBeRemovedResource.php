<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToBeRemovedResource\Pages;
use App\Filament\Resources\ToBeRemovedResource\RelationManagers;
use App\Models\ToBeRemoved;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ToBeRemovedResource extends Resource
{
    protected static ?string $model = ToBeRemoved::class;

    protected static ?string $navigationLabel = 'To Be Removed';

    protected static ?string $navigationGroup = 'Reports';

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
            'index' => Pages\ListToBeRemoveds::route('/'),
            'create' => Pages\CreateToBeRemoved::route('/create'),
            'edit' => Pages\EditToBeRemoved::route('/{record}/edit'),
        ];
    }
}
