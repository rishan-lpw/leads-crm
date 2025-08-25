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
                Forms\Components\TextInput::make('customer_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Select::make('activity_type')
                    ->required()
                    ->columnSpanFull()
                    ->options([
                        'call' => 'Call',
                        'email' => 'Email',
                        'meeting' => 'Meeting',
                        'message' => 'Message',
                    ]),
                Forms\Components\Select::make('Follow up Status')
                    ->relationship('activity_follow_up', 'status')
                    ->required(),
                Forms\Components\Select::make('Follow up Level Score')
                    ->relationship('activity_follow_up', 'level_score')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('activityFollowUp.status')
                    ->sortable()
                    ->label('Follow Up Status'),
                Tables\Columns\TextColumn::make('activityFollowUp.level_score')
                    ->sortable()
                    ->label('Follow Up Level Score'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
