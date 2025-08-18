<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PasswordResetResource\Pages;
use App\Filament\Resources\PasswordResetResource\RelationManagers;
use App\Models\PasswordReset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PasswordResetResource extends Resource
{
    protected static ?string $model = PasswordReset::class;

    protected static ?string $navigationLabel = 'Password Reset';

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
            'index' => Pages\ListPasswordResets::route('/'),
            'create' => Pages\CreatePasswordReset::route('/create'),
            'edit' => Pages\EditPasswordReset::route('/{record}/edit'),
        ];
    }
}
