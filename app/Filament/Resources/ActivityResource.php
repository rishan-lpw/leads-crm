<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\ActivityFollowUp;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


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
                Tab::make('Activity Details')
                    ->schema([
                        Forms\Components\TextInput::make('lead_id')
                            ->label('Lead ID')
                            ->required()
                            ->numeric()
                            ->placeholder('Enter Lead ID'),
                        Forms\Components\Select::make('activity_type')
                            ->label('Activity Type')
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
                            ->placeholder('Select Activity Type'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Enter notes about the activity'),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Scheduled At')
                            ->placeholder('Select scheduled date and time'),
                        Forms\Components\DateTimePicker::make('due_at')
                            ->label('Due At')
                            ->placeholder('Select due date and time'),
                        Forms\Components\DateTimePicker::make('last_checked_at')
                            ->label('Last Checked At')
                            ->placeholder('Select last checked date and time'),
                        Forms\Components\TextInput::make('action')
                            ->label('Action')
                            ->maxLength(255)
                            ->placeholder('Enter action taken'),
                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->placeholder('Enter quantity'),
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->maxLength(255)
                            ->placeholder('Enter value'),
                        Forms\Components\TextInput::make('ad_id')
                            ->label('Ad ID')
                            ->maxLength(255)
                            ->placeholder('Enter Ad ID'),
                        Forms\Components\Textarea::make('comments')
                            ->label('Comments')
                            ->rows(3)
                            ->placeholder('Enter additional comments'),
                        Forms\Components\DateTimePicker::make('date_time')
                            ->label('Date & Time')
                            ->placeholder('Select date and time of the activity'),
                        Forms\Components\TextInput::make('assigned_by')
                            ->label('Assigned By (User ID)')
                            ->numeric()
                            ->placeholder('Enter User ID who assigned the activity'),
                        Forms\Components\TextInput::make('old_am')
                            ->label('Old AM (Customer ID)')
                            ->numeric()
                            ->placeholder('Enter Old Account Manager Customer ID'),
                    ]),
                Tab::make('Follow-Up Details')
                    ->schema([
                        Forms\Components\TextInput::make('activity_follow_up_id')
                            ->label('Follow-Up Activity ID')
                            ->numeric()
                            ->placeholder('Enter Follow-Up Activity ID')
                            ->nullable(),
                        Forms\Components\Textarea::make('follow_up_notes')
                            ->label('Follow-Up Notes')
                            ->rows(3)
                            ->placeholder('Enter notes about the follow-up activity'),
                        Forms\Components\DateTimePicker::make('follow_up_scheduled_at')
                            ->label('Follow-Up Scheduled At')
                            ->placeholder('Select follow-up scheduled date and time'),
                        Forms\Components\DateTimePicker::make('follow_up_due_at')
                            ->label('Follow-Up Due At')
                            ->placeholder('Select follow-up due date and time'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('id')
                //     ->label('ID')
                //     ->sortable(),
                // Lead ID
                Tables\Columns\TextColumn::make('lead_id')
                    ->label('Lead ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_type')
                    ->label('Activity Type')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                // Tables\Columns\TextColumn::make('notes')
                //     ->label('Notes')
                //     ->limit(50)
                //     ->wrap(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('last_checked_at')
                //     ->label('Last Checked At')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable(),
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
