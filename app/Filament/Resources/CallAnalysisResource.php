<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CallAnalysisResource\Pages\ListCallAnalyses;
use App\Filament\Resources\CallAnalysisResource\Pages\CreateCallAnalysis;
use App\Filament\Resources\CallAnalysisResource\Pages\EditCallAnalysis;
use App\Filament\Resources\CallAnalysisResource\Pages;
use App\Filament\Resources\CallAnalysisResource\RelationManagers;
use App\Models\CallAnalysis;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CallAnalysisResource extends Resource
{
    protected static ?string $model = CallAnalysis::class;

    protected static ?string $navigationLabel = 'Call Script Analysis';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-phone';

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
            'index' => ListCallAnalyses::route('/'),
            'create' => CreateCallAnalysis::route('/create'),
            'edit' => EditCallAnalysis::route('/{record}/edit'),
        ];
    }
}
