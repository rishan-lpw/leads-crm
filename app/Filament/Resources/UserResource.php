<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\UserLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Forms\Components\Select;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    // Hide from lower-level users
    public static function shouldRegisterNavigation(): bool
    {
    return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('password')
                    ->required()
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                // User Type
                Select::make('user_type')
                    ->required()
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'user' => 'User',
                        'junior_user' => 'Junior User',
                    ])
                    ->label('User Type'),
                Select::make('user_level_id')
                    ->required()
                    ->options(UserLevel::all()->pluck('name', 'id'))
                    ->label('User Level')
                    ->visible(fn ($record) => true),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('user_type')
                    ->label('User Type')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'user' => 'User',
                        'junior_user' => 'Junior User',
                        default => 'Unknown',
                    }),
                TextColumn::make('user_level.name')->label('User Level'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn ($record) => auth()->user()->hasAtleastLevel(5)),
                DeleteAction::make()
                    ->visible(fn ($record) => auth()->user()->hasAtleastLevel(5)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn ($record) => auth()->user()->hasAtleastLevel(5)),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
