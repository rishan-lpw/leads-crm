<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ActivityResource\Pages\ListActivities;
use App\Filament\Resources\ActivityResource\Pages\CreateActivity;
use App\Filament\Resources\ActivityResource\Pages\EditActivity;
use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\ActivityFollowUp;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string | \BackedEnum | null $navigationIcon = 'lucide-activity';

    protected static ?string $navigationLabel = 'Agent\'s Activities';

    protected static string | \UnitEnum | null $navigationGroup = 'Agents';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // protected $fillable = [
                //     'lead_id', 'user_id', 'action', 'qty', 'value', 'ad_id', 'comments', 'reminder', 'date_time', 'old_am'
                // ];
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Add the Columns: 'lead_id', 'user_id', 'action', 'qty', 'value', 'ad_id', 'comments', 'reminder', 'date_time', 'old_am'
                TextColumn::make('user.name')->label('Agent')->searchable()->sortable(),
                TextColumn::make('lead.customer.firstname')->label('Customer')->searchable()->sortable(),
                TextColumn::make('action')->label('Action')->searchable()->sortable(),
                TextColumn::make('qty')->label('Quantity')->searchable()->sortable(),
                TextColumn::make('value')->label('Value')->searchable()->sortable(),
                TextColumn::make('comments')->label('Comments')->searchable()->sortable(),
                TextColumn::make('date_time')->label('Date & Time')->dateTime()->searchable()->sortable(),
                ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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

    // Active Agents List
    // Agents' Dashboard
    // PAA Agents
    // Expired Agents
    // Prospects

    public static function getGlobalSearchAttributes(): array
    {
        return [
            'customer.name',
            'user.name',
            'activity_type',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return Activity::count() > 10 ? Activity::count() : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }
}
