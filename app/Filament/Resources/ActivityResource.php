<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\ActivityFollowUp;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'lucide-activity';

    protected static ?string $navigationLabel = 'Agent\'s Activities';

    protected static ?string $navigationGroup = 'Agents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Tables\Columns\TextColumn::make('user.name')->label('Agent')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('lead.customer.firstname')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('action')->label('Action')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('qty')->label('Quantity')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('value')->label('Value')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('comments')->label('Comments')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date_time')->label('Date & Time')->dateTime()->searchable()->sortable(),
                ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
