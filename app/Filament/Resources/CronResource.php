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
                    ->label('Job Name')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g. Daily Cleanup'),

                Forms\Components\Select::make('command')
                    ->label('Artisan Command')
                    ->options(collect(Artisan::all())->mapWithKeys(fn ($command, $key) => [$key => $key])->toArray())
                    ->searchable()
                    ->required()
                    ->hint('Select a registered Artisan command'),

                Forms\Components\Select::make('frequency')
                    ->label('Frequency')
                    ->options([
                        '* * * * *' => 'Every Minute',
                        '0 * * * *' => 'Hourly',
                        '0 0 * * *' => 'Daily',
                        '0 0 * * 0' => 'Weekly',
                        '0 0 1 * *' => 'Monthly',
                        'custom'    => 'Custom Expression',
                    ])
                    ->reactive()
                    ->required(),

                Forms\Components\TextInput::make('custom_frequency')
                    ->label('Custom Cron Expression')
                    ->placeholder('e.g. 0 2 * * *')
                    ->visible(fn ($get) => $get('frequency') === 'custom')
                    ->helperText('Use standard cron format'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options([
                        'leads'    => 'Leads',
                        'calls'    => 'Calls',
                        'merge'    => 'Merge',
                        'followup' => 'Followup',
                    ])
                    ->required(),

                Forms\Components\Select::make('visibility')
                    ->label('Visibility')
                    ->options([
                        'all'     => 'All',
                        'seniors' => 'Seniors',
                        'hunters' => 'Hunters',
                        'ams'     => 'AMs',
                    ])
                    ->default('all')
                    ->required(),

                // Rules column want a longtext input field.
                Forms\Components\Textarea::make('rules')
                    ->label('Business Rules')
                    ->placeholder('Enter business rules here...')
                    ->rows(4),

                Forms\Components\Select::make('source_highlight')
                    ->label('Source Highlight')
                    ->options([
                        'transaction' => 'Transaction',
                        'pvt-seller'  => 'PVT Seller',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('allow_manual_trigger')
                    ->label('Allow Manual Trigger')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('command'),
                Tables\Columns\TextColumn::make('frequency'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('category')->label('Category'),
                Tables\Columns\TextColumn::make('source_highlight')->label('Source Highlight'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime(),
                Tables\Columns\TextColumn::make('last_run_at')->label('Last Run At')->dateTime(),
                Tables\Columns\TextColumn::make('rules')->label('Business Rules')->wrap(),
                Tables\Columns\TextColumn::make('visibility')->label('Visibility'),
                Tables\Columns\TextColumn::make('cron_log.status')->label('Cron Log Status')->badge(),
            ])
            
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('expandAll')
                    ->label(fn($livewire) => $livewire->expandAll ? 'Collapse All' : 'Expand All')
                    ->action(function ($livewire) {
                        $livewire->expandAll = ! $livewire->expandAll;
                    })
            ])
            ->recordUrl(null)
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
