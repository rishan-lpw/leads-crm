<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ActiveAgentsResource\Pages\ListActiveAgents;
use App\Filament\Resources\ActiveAgentsResource\Pages\CreateActiveAgents;
use App\Filament\Resources\ActiveAgentsResource\Pages\EditActiveAgents;
use App\Filament\Resources\ActiveAgentsResource\Pages;
use App\Models\ActiveAgents;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActiveAgentsResource extends Resource
{
    protected static ?string $model = ActiveAgents::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Active Agents';

    protected static string | \UnitEnum | null $navigationGroup = 'Agents';

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

    public static function getPages(): array
    {
        return [
            'index' => ListActiveAgents::route('/'),
            'create' => CreateActiveAgents::route('/create'),
            'edit' => EditActiveAgents::route('/{record}/edit'),
        ];
    }
}
