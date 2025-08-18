<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignManageResource\Pages;
use App\Filament\Resources\AssignManageResource\RelationManagers;
use App\Models\AssignManage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignManageResource extends Resource
{
    protected static ?string $model = AssignManage::class;

    protected static ?string $navigationLabel = 'Manage Assigns';

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
            'index' => Pages\ListAssignManages::route('/'),
            'create' => Pages\CreateAssignManage::route('/create'),
            'edit' => Pages\EditAssignManage::route('/{record}/edit'),
        ];
    }
}
