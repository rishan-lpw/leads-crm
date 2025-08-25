<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CronLogResource\Pages;
use App\Filament\Resources\CronLogResource\RelationManagers;
use App\Models\CronLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CronLogResource extends Resource
{
    protected static ?string $model = CronLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cron.name')->label('Cron Job'),
                Tables\Columns\TextColumn::make('started_at'),
                Tables\Columns\TextColumn::make('finished_at'),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Columns\TextColumn::make('output')->limit(50),
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
            'index' => Pages\ListCronLogs::route('/'),
            // 'create' => Pages\CreateCronLog::route('/create'),
            // 'edit' => Pages\EditCronLog::route('/{record}/edit'),
        ];
    }
}
