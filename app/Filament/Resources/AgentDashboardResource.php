<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentDashboardResource\Pages;
use App\Filament\Resources\AgentDashboardResource\RelationManagers;
use App\Filament\Resources\AgentDashboardResource\Widgets\StatsOverview;
use App\Models\AgentDashboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgentDashboardResource extends Page
{
    protected static ?string $model = AgentDashboard::class;

    protected static ?string $navigationLabel = 'Agent Dashboard';

    protected static ?string $navigationGroup = 'Agents';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $slug = 'agent-dashboard';

    protected function getWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             //
    //         ]);
    // }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             //
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgentDashboards::route('/'),
            'create' => Pages\CreateAgentDashboard::route('/create'),
            'edit' => Pages\EditAgentDashboard::route('/{record}/edit'),
        ];
    }
}
