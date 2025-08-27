<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CronResource\Pages;
use App\Filament\Resources\CronResource\RelationManagers;
use App\Models\Cron;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
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
                    ->label('Cron Name')
                    ->required(),
                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options([
                        'Pending Payment' => 'Pending Payment',
                        'Ikman' => 'Ikman',
                        'Facebook-Ads' => 'Facebook-Ads',
                    ])
                    ->required(),

                Forms\Components\Select::make('team_name')
                    ->label('Team Name')
                    ->options([
                        'Junior Hunters' => 'Junior Hunters',
                        'Senior Hunters' => 'Senior Hunters',
                        'Sales' => 'Sales',
                    ])
                    ->required(),

                Forms\Components\Select::make('rule_1_days')
                    ->label('No. of Days Assigned (Rule 1)')
                    ->options(array_combine(range(1, 30), range(1, 30)))
                    ->required(),

                Forms\Components\Select::make('rule_2_days')
                    ->label('No. of Days Assigned (Rule 2)')
                    ->options(array_combine(range(1, 30), range(1, 30)))
                    ->required(),

                // Running frequency
                Forms\Components\Select::make('frequency')
                    ->label('Frequency')
                    ->options([
                        'daily' => 'Daily',
                        '2 days' => '2 Days',
                        '3 days' => '3 Days',
                        'weekly' => 'Weekly',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Cron Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('category')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('team_name')->label('Team Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('rule_1_days')->label('Rule 1 Days')->sortable(),
                Tables\Columns\TextColumn::make('rule_2_days')->label('Rule 2 Days')->sortable(),
                Tables\Columns\TextColumn::make('frequency')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created At')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Updated At')->sortable(),
            ])
            
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('run')
                    ->label('Run Now')
                    ->action(function (Cron $record) {
                        // Directly call the command stored in the cron record
                        try {
                            $output = null;
                            if ($record->command) {
                                Artisan::call($record->command);
                                $output = Artisan::output();
                            }
                            // Optionally, log the run or show a notification
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
                    ->color('success'),
            ])

            
            // Removed unsupported expandable() method
        ;
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
