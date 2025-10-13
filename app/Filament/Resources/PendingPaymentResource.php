<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Lead;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PendingPaymentResource\Components\TableColumns;
use App\Filament\Resources\PendingPaymentResource\Components\TableFilters;
use App\Filament\Resources\PendingPaymentResource\Components\RecordActions;
use App\Filament\Resources\PendingPaymentResource\Components\FormSections;
use App\Filament\Resources\PendingPaymentResource\Pages\ListPendingPayments;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class PendingPaymentResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $label = 'Open';

    protected static string | \UnitEnum | null $navigationGroup = 'Private Sellers';

    protected static string | \BackedEnum | null $navigationIcon = 'fas-hand-holding-hand';

    // Override the Eloquent query to filter pending payments
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'system');
    }

    public static function form(Schema $schema): Schema
    {
        // delegate form sections to component
        return $schema->components(FormSections::get());
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

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::where('status', 'system')->count();
        return $count > 0 ? (string)$count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPendingPayments::route('/'),
        ];
    }
}
