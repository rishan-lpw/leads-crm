<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\ActivityFollowUp;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'lucide-activity';

    protected static ?string $navigationLabel = 'Agent\'s Activities';

    protected static ?string $navigationGroup = 'Agents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('lead_id')
                    ->label('Lead')
                    ->relationship('lead', 'heading')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->placeholder('Select Lead')
                    ->columnSpanFull(),
                Select::make('activity_type')
                    ->options([
                        'call' => 'Call',
                        'email' => 'Email',
                        'meeting' => 'Meeting',
                        'whatsapp' => 'WhatsApp',
                        'sms' => 'SMS',
                        'payment' => 'Payment',
                        // 'follow_up' => 'Follow Up',
                        'site_visit' => 'Site Visit',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->placeholder('Select Activity Type')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Scheduled At')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('due_at')
                    ->label('Due At')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('last_checked_at')
                    ->label('Last Checked At')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('action')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('value')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ad_id')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('comments')
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('date_time')
                    ->label('Date & Time')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('assigned_by')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('old_am')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_type')
                    ->label('Activity Type')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('Last Checked At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->limit(20)
                    ->wrap(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->limit(20)
                    ->wrap(),
                // Tables\Columns\TextColumn::make('ad_id')
                //     ->label('Ad ID')
                //     ->limit(20)
                //     ->wrap(),
                // Tables\Columns\TextColumn::make('comments')
                //     ->label('Comments')
                //     ->limit(20)
                //     ->wrap(),
                Tables\Columns\TextColumn::make('date_time')
                    ->label('Date & Time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assigned_by')
                    ->label('Assigned By')
                    ->limit(20)
                    ->wrap(),
                // Tables\Columns\TextColumn::make('old_am')
                //     ->label('Old AM')
                //     ->limit(20)
                //     ->wrap(),
            ])
            ->filters([
                // Add filters if needed
                Tables\Filters\Filter::make('activity_type')
                    ->label('Activity Type')
                    ->form([
                        Forms\Components\Select::make('activity_type')
                            ->options([
                                'call' => 'Call',
                                'email' => 'Email',
                                'meeting' => 'Meeting',
                                'whatsapp' => 'WhatsApp',
                                'sms' => 'SMS',
                                'payment' => 'Payment',
                                // 'follow_up' => 'Follow Up',
                                'site_visit' => 'Site Visit',
                                'other' => 'Other',
                            ])
                            ->placeholder('Select Activity Type'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['activity_type'], function (Builder $query, $value) {
                            $query->where('activity_type', $value);
                        });
                    }),
                // Date range filter for scheduled_at
                Tables\Filters\Filter::make('scheduled_at')
                    ->label('Scheduled At')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_at_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('scheduled_at_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['scheduled_at_from'], function (Builder $query, $value) {
                                $query->whereDate('scheduled_at', '>=', $value);
                            })
                            ->when($data['scheduled_at_to'], function (Builder $query, $value) {
                                $query->whereDate('scheduled_at', '<=', $value);
                            });
                    }),
                // Date range filter for due_at
                Tables\Filters\Filter::make('due_at')
                    ->label('Due At')
                    ->form([
                        Forms\Components\DatePicker::make('due_at_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('due_at_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['due_at_from'], function (Builder $query, $value) {
                                $query->whereDate('due_at', '>=', $value);
                            })
                            ->when($data['due_at_to'], function (Builder $query, $value) {
                                $query->whereDate('due_at', '<=', $value);
                            });
                    }),
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

    // Active Agents List
    // Agents' Dashboard
    // PAA Agents
    // Expired Agents
    // Prospects

    public static function getGlobalSearchAttributes(): array
    {
        return [
            'customer.name',
            'user.name',
            'activity_type',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return Activity::count() > 10 ? Activity::count() : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
