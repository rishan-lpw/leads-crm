<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountReportResource\Pages;
use App\Filament\Resources\AccountReportResource\RelationManagers;
use App\Models\AccountReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountReportResource extends Resource
{
    protected static ?string $model = AccountReport::class;

    protected static ?string $navigationLabel = 'Account Reports';

    protected static ?string $navigationGroup = 'Accounts';

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
            'index' => Pages\ListAccountReports::route('/'),
            'create' => Pages\CreateAccountReport::route('/create'),
            'edit' => Pages\EditAccountReport::route('/{record}/edit'),
        ];
    }
}
