<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Lead;
use App\Filament\Resources\UpsellResource\Components\TableColumns;
use App\Filament\Resources\UpsellResource\Components\TableFilters;
use App\Filament\Resources\UpsellResource\Components\RecordActions;
use App\Filament\Resources\UpsellResource\Components\FormSections;
use App\Filament\Resources\UpsellResource\Pages\ListUpsells;

class UpsellResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationLabel = 'Upsell';

    protected static string | \UnitEnum | null $navigationGroup = 'Private Sellers';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-m-fire';

    // scope to upsell status (adjust if your project uses a different flag)
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'upsell');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(FormSections::get());
    }

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
            'index' => ListUpsells::route('/'),
        ];
    }
}
