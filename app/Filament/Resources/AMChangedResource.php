<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AMChangedResource\Pages\ListAMChangeds;
use App\Filament\Resources\AMChangedResource\Pages\CreateAMChanged;
use App\Filament\Resources\AMChangedResource\Pages\EditAMChanged;
use App\Filament\Resources\AMChangedResource\Pages;
use App\Filament\Resources\AMChangedResource\RelationManagers;
use App\Models\AMChanged;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AMChangedResource extends Resource
{
    protected static ?string $model = AMChanged::class;

    protected static ?string $navigationLabel = 'AM Changes';

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
            'index' => ListAMChangeds::route('/'),
            'create' => CreateAMChanged::route('/create'),
            'edit' => EditAMChanged::route('/{record}/edit'),
        ];
    }
}
