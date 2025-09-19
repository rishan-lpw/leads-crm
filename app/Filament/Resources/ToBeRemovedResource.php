<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ToBeRemovedResource\Pages\ListToBeRemoveds;
use App\Filament\Resources\ToBeRemovedResource\Pages\CreateToBeRemoved;
use App\Filament\Resources\ToBeRemovedResource\Pages\EditToBeRemoved;
use App\Filament\Resources\ToBeRemovedResource\Pages;
use App\Filament\Resources\ToBeRemovedResource\RelationManagers;
use App\Models\ToBeRemoved;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ToBeRemovedResource extends Resource
{
    protected static ?string $model = ToBeRemoved::class;

    protected static ?string $navigationLabel = 'To Be Removed';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

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
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListToBeRemoveds::route('/'),
            'create' => CreateToBeRemoved::route('/create'),
            'edit' => EditToBeRemoved::route('/{record}/edit'),
        ];
    }
}
