<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\CronLogResource\Pages\ListCronLogs;
use App\Filament\Resources\CronLogResource\Pages;
use App\Filament\Resources\CronLogResource\RelationManagers;
use App\Models\CronLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CronLogResource extends Resource
{
    protected static ?string $model = CronLog::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cron.name')->label('Cron Job'),
                TextColumn::make('started_at'),
                TextColumn::make('finished_at'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'Success',
                        'danger' => 'Failed',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'success' => 'Success',
                        'danger' => 'Failed',
                        default => $state,
                    }),
                TextColumn::make('output')->limit(50),
            ])
            ->filters([
                //
            ]);
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
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
            'index' => ListCronLogs::route('/'),
            // 'create' => Pages\CreateCronLog::route('/create'),
            // 'edit' => Pages\EditCronLog::route('/{record}/edit'),
        ];
    }
}
