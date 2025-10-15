<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Lead;
use App\Models\Activity;
use App\Filament\Resources\HuntersResource\Components\FormSections;
use App\Filament\Resources\HuntersResource\Components\TableColumns;
use App\Filament\Resources\HuntersResource\Components\TableFilters;
use App\Filament\Resources\HuntersResource\Components\TableRecordActions;
use App\Filament\Resources\HuntersResource\Components\TableHeaderActions;
use App\Filament\Resources\HuntersResource\Components\ToolbarActions;
use App\Filament\Resources\HuntersResource\Pages\ListHunters;
use App\Filament\Resources\HuntersResource\Pages\CreateHunters;
use App\Filament\Resources\HuntersResource\Pages\EditHunters;
use App\Filament\Resources\HuntersResource\Pages;
use App\Filament\Resources\HuntersResource\Pages\CustomerAds;

class HuntersResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $activityModel = Activity::class;

    protected static ?string $navigationLabel = 'Hunters';

    protected static ?int $navigationGroupSort = 4;

    protected static string | \UnitEnum | null $navigationGroup = 'Private Sellers';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                // Only load essential data for list view - remove activities.funnel to optimize
                'customer:id,firstname,email,mobile,membership_status',
                'user:id,username,name',
                // 'activities.funnel', // Removed - will be loaded lazily when needed
            ]);
            
        $user = auth()->user();

        if ($user && $user->user_level_id == 1) {
            $query->where('user_id', $user->id);
        }

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
            // Highlight the rows which price is greater than 50000000
            // ->rowHighlight(fn($record) => $record->price > 50000000 ? 'bg-yellow-100' : null)
            ->filters(TableFilters::getFilters())
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->recordActions(TableRecordActions::getRecordActions())
            ->headerActions(TableHeaderActions::getHeaderActions())
            ->toolbarActions(ToolbarActions::getToolbarActions())
            // By default, Pin records to the top
            ->defaultSort('is_pin', 'desc')
            ->recordUrl(null);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::where('is_active', 1)->count();
        return $count > 0 ? (string)$count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHunters::route('/'),
            'create' => CreateHunters::route('/create'),
            'edit' => EditHunters::route('/{record}/edit'),
            'customer-ads' => CustomerAds::route('/{record}/customer-ads'),
        ];
    }
}
