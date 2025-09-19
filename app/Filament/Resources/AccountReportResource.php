<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AccountReportResource\Pages\ListAccountReports;
use App\Filament\Resources\AccountReportResource\Pages\CreateAccountReport;
use App\Filament\Resources\AccountReportResource\Pages\EditAccountReport;
use App\Filament\Resources\AccountReportResource\Pages;
use App\Filament\Resources\AccountReportResource\RelationManagers;
use App\Models\AccountReport;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountReportResource extends Resource
{
    protected static ?string $model = AccountReport::class;

    protected static ?string $navigationLabel = 'Account Reports';

    protected static string | \UnitEnum | null $navigationGroup = 'Accounts';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-document-chart-bar';

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
            'index' => ListAccountReports::route('/'),
            'create' => CreateAccountReport::route('/create'),
            'edit' => EditAccountReport::route('/{record}/edit'),
        ];
    }
}
