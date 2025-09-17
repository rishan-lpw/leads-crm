<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CronResource\Pages;
use App\Filament\Resources\CronResource\RelationManagers;
use App\Models\Cron;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Artisan;

class CronResource extends Resource
{
    protected static ?string $model = Cron::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-up-on-square-stack';

    protected static ?string $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Assign Rules';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Cron Name')
                    ->required(),
                Forms\Components\Select::make('category')
                    ->label('Select Channel')
                    ->options([
                        'Pending Payment' => 'Pending Payment',
                        'Ikman' => 'Ikman',
                        'Facebook-Ads' => 'Facebook-Ads',
                    ])
                    ->required(),

                // Select multiple options as user names in user table. User can be able to select many choices here.
                Forms\Components\Select::make('member')
                    ->label('Select Member/Members')
                    ->multiple()
                    // Give options as user names from user table whose user_type <= 3
                    ->options(User::query()
                        ->where('user_type', '<=', 3)
                        ->whereNotNull('name')
                        ->where('name', '!=', '')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->required(),

                Forms\Components\Select::make('rule_1_days')
                    ->label('No. of Days Assigned (Rule 1)')
                    ->options(array_combine(range(1, 30), range(1, 30)))
                    ->required(),

                Forms\Components\Select::make('rule_2_days')
                    ->label('No. of Days Assigned (Rule 2)')
                    ->options(array_combine(range(1, 30), range(1, 30)))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Cron Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('category')->label('Channel')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('member')->label('Members')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('rule_1_days')->label('Days (Rule 1)')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('rule_2_days')->label('Days (Rule 2)')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d-M-Y H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d-M-Y H:i')
                    ->sortable()
                    ->searchable(),
                
                // is_active, last_run_at, last_run_result
                // Tables\Columns\BooleanColumn::make('is_active')->label('Is Active')->sortable(),
                // Tables\Columns\TextColumn::make('last_run_at')->label('Last Run At')->sortable(),
                // Tables\Columns\TextColumn::make('last_run_result')->label('Last Run Result')->sortable(),
                ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Add a run now action
                Tables\Actions\Action::make('run_now')
                    ->label('Run Now')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Execute Cron Job')
                    ->modalDescription(fn (Cron $record): string => 
                        "Are you sure you want to execute the cron job '{$record->name}' for category '{$record->category}' now?"
                    )
                    ->modalSubmitActionLabel('Execute Now') 
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
            'index' => Pages\ListCrons::route('/'),
            'create' => Pages\CreateCron::route('/create'),
            'edit' => Pages\EditCron::route('/{record}/edit'),
        ];
    }
}
