<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AllHuntersResource\Pages;
use App\Filament\Resources\HuntersResource\Components\FormSections;
use App\Filament\Resources\HuntersResource\Components\TableColumns;
use App\Filament\Resources\HuntersResource\Components\TableFilters;
use App\Filament\Resources\HuntersResource\Components\TableHeaderActions;
use App\Filament\Resources\HuntersResource\Components\TableRecordActions;
use App\Filament\Resources\HuntersResource\Components\ToolbarActions;
use App\Models\Activity;
use App\Models\Lead;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AllHuntersResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $activityModel = Activity::class;

    protected static ?string $navigationLabel = 'All Hunters';

    protected static ?int $navigationGroupSort = 5;

    protected static string | \UnitEnum | null $navigationGroup = 'Private Sellers';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';

    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Lead> $query */
        $query = parent::getEloquentQuery();

        $query->with([
            'customer:id,firstname,email,mobile,membership_status',
        ])->whereIn('status', ['system', 'not_interested', 'upsell', 'renew', 'new', 'follow_up', 'un_mapped', 'transferred']);

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(FormSections::getSections());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableColumns::getColumns())
            ->filters(TableFilters::getFilters())
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->recordActions(TableRecordActions::getRecordActions())
            ->headerActions(TableHeaderActions::getHeaderActions())
            ->toolbarActions(ToolbarActions::getToolbarActions())
            ->defaultSort('is_pin', 'desc')
            ->recordUrl(null);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::whereIn('status', ['system', 'not_interested', 'upsell', 'renew', 'new', 'follow_up', 'un_mapped', 'transferred'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAllHunters::route('/'),
            'create' => Pages\CreateAllHunters::route('/create'),
            'edit' => Pages\EditAllHunters::route('/{record}/edit'),
        ];
    }
}


