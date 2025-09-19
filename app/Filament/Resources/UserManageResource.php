<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserManageResource\Pages\ListUserManages;
use App\Filament\Resources\UserManageResource\Pages\CreateUserManage;
use App\Filament\Resources\UserManageResource\Pages\EditUserManage;
use App\Filament\Resources\UserManageResource\Pages;
use App\Filament\Resources\UserManageResource\RelationManagers;
use App\Models\UserManage;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserManageResource extends Resource
{
    protected static ?string $model = UserManage::class;

    protected static ?string $navigationLabel = 'Manage Users';

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
            'index' => ListUserManages::route('/'),
            'create' => CreateUserManage::route('/create'),
            'edit' => EditUserManage::route('/{record}/edit'),
        ];
    }
}
