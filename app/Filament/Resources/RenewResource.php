<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Lead;
use App\Filament\Resources\RenewResource\Components\TableColumns;
use App\Filament\Resources\RenewResource\Components\TableFilters;
use App\Filament\Resources\RenewResource\Components\RecordActions;
use App\Filament\Resources\RenewResource\Components\FormSections;
use App\Filament\Resources\RenewResource\Pages\ListRenews;

class RenewResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationLabel = 'Renew';

    protected static string | \UnitEnum | null $navigationGroup = 'Private Sellers';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-receipt-refund';

    public static function getEloquentQuery(): Builder
    {
        // scope to renew status (adjust if your project uses another flag)
        return parent::getEloquentQuery()->where('status', 'renew');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(FormSections::get());
    }

    // getNavigationBadge can be added if needed
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string)$count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableColumns::get())
            ->filters(TableFilters::get())
            ->recordActions(RecordActions::get())
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRenews::route('/'),
        ];
    }
}
