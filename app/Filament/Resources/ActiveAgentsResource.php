<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActiveAgentsResource\Pages;
use App\Models\ActiveAgents;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActiveAgentsResource extends Resource
{
    protected static ?string $model = ActiveAgents::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Active Agents';

    protected static ?string $navigationGroup = 'Agents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActiveAgents::route('/'),
            'create' => Pages\CreateActiveAgents::route('/create'),
            'edit' => Pages\EditActiveAgents::route('/{record}/edit'),
        ];
    }
}
