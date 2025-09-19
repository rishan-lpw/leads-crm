<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AssignManageResource\Pages\ListAssignManages;
use App\Filament\Resources\AssignManageResource\Pages\CreateAssignManage;
use App\Filament\Resources\AssignManageResource\Pages\EditAssignManage;
use App\Filament\Resources\AssignManageResource\Pages;
use App\Filament\Resources\AssignManageResource\RelationManagers;
use App\Models\AssignManage;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignManageResource extends Resource
{
    protected static ?string $model = AssignManage::class;

    protected static ?string $navigationLabel = 'Manage Assigns';

    protected static string | \UnitEnum | null $navigationGroup = 'Admin';

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
            'index' => ListAssignManages::route('/'),
            'create' => CreateAssignManage::route('/create'),
            'edit' => EditAssignManage::route('/{record}/edit'),
        ];
    }
}
