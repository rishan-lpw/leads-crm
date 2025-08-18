<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallAnalysisResource\Pages;
use App\Filament\Resources\CallAnalysisResource\RelationManagers;
use App\Models\CallAnalysis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CallAnalysisResource extends Resource
{
    protected static ?string $model = CallAnalysis::class;

    protected static ?string $navigationLabel = 'Call Script Analysis';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index' => Pages\ListCallAnalyses::route('/'),
            'create' => Pages\CreateCallAnalysis::route('/create'),
            'edit' => Pages\EditCallAnalysis::route('/{record}/edit'),
        ];
    }
}
