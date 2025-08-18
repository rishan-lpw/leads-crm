<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserManageResource\Pages;
use App\Filament\Resources\UserManageResource\RelationManagers;
use App\Models\UserManage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserManageResource extends Resource
{
    protected static ?string $model = UserManage::class;

    protected static ?string $navigationLabel = 'Manage Users';

    protected static ?string $navigationGroup = 'Admin';

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
            'index' => Pages\ListUserManages::route('/'),
            'create' => Pages\CreateUserManage::route('/create'),
            'edit' => Pages\EditUserManage::route('/{record}/edit'),
        ];
    }
}
