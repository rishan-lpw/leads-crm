<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CronResource\Pages\ListCrons;
use App\Filament\Resources\CronResource\Pages\CreateCron;
use App\Filament\Resources\CronResource\Pages\EditCron;
use App\Filament\Resources\CronResource\Pages;
use App\Filament\Resources\CronResource\RelationManagers;
use App\Models\Cron;
use App\Models\User;
use App\Models\UserValue;
use Filament\Forms;
use Filament\Forms\Components\Radio;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-m-arrow-up-on-square-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Assign Rules';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Cron Name')
                    ->required(),

                Radio::make('user_value')
                    ->label('Select Level')
                    // Add options as user_value_id from user_value table
                    ->options(UserValue::query()->pluck('category', 'id')->toArray())
                    ->inline()
                    ->required(),
            
                Select::make('category')
                    ->label('Select Channel')
                    ->options([
                        'Pending Payment' => 'Pending Payment',
                        'Ikman' => 'Ikman',
                        'Facebook-Ads' => 'Facebook-Ads',
                    ])
                    ->required(),

                // Select multiple options as user names in user table. User can be able to select many choices here.
                Select::make('member')
                    ->label('Select Member/Members')
                    ->multiple()
                    ->required()
                    ->options(function (callable $get) {
                        $userValueId = $get('user_value'); // Get the selected radio value

                        if (!$userValueId) {
                            return [];
                        }

                        return User::query()
                            ->where('user_value_id', $userValueId)
                            ->whereNotNull('name')
                            ->where('name', '!=', '')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),

                Select::make('rule_1_days')
                    ->label('No. of Days Assigned (Rule 1)')
                    ->options(array_combine(range(1, 30), range(1, 30)))
                    ->required(),

                Select::make('rule_2_days')
                    ->label('No. of Days Assigned (Rule 2)')
                    ->options(array_combine(range(1, 30), range(1, 30)))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Cron Name')->sortable()->searchable(),
                TextColumn::make('category')->label('Channel')->sortable()->searchable(),
                TextColumn::make('member')->label('Members')->sortable()->searchable(),
                TextColumn::make('rule_1_days')->label('Days (Rule 1)')->sortable()->searchable(),
                TextColumn::make('rule_2_days')->label('Days (Rule 2)')->sortable()->searchable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d-M-Y H:i')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('updated_at')
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
            ->recordActions([
                EditAction::make(),
                // Add a run now action
                Action::make('run_now')
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
            'index' => ListCrons::route('/'),
            'create' => CreateCron::route('/create'),
            'edit' => EditCron::route('/{record}/edit'),
        ];
    }
}
