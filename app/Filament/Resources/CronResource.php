<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CronResource\Pages;
use App\Filament\Resources\CronResource\RelationManagers;
use App\Models\Cron;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Artisan;

class CronResource extends Resource
{
    protected static ?string $model = Cron::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('command')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('frequency')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('command'),
                Tables\Columns\TextColumn::make('frequency'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at'),
                Tables\Columns\TextColumn::make('updated_at'),
                // Tables\Columns\TextColumn::make('cron_log.status')->label('Cron Log Status'),
                // Tables\Columns\TextColumn::make('last_run_at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Run Now')
                    ->action(function ($record) {
                        try {
                            Artisan::call($record->command);
                            $output = Artisan::output();

                            \App\Models\CronLog::create([
                                'cron_id'    => $record->id,
                                'started_at' => now(),
                                'finished_at' => now(),
                                'status'     => true,
                                'output'     => $output,
                            ]);
                        } catch (\Throwable $e) {
                            \App\Models\CronLog::create([
                                'cron_id'    => $record->id,
                                'started_at' => now(),
                                'finished_at' => now(),
                                'status'     => false,
                                'output'     => $e->getMessage(),
                            ]);
                        }
                    })
                    ->requiresConfirmation(),
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
            'index' => Pages\ListCrons::route('/'),
            'create' => Pages\CreateCron::route('/create'),
            'edit' => Pages\EditCron::route('/{record}/edit'),
        ];
    }
}
