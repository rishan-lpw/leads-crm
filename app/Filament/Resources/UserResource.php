<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\UserLevel;
use Dom\Text;
use Filament\Forms;
use Filament\Resources\Resource;
use App\Models\UserType;
use App\Models\UserValue;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'fas-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Manage Users';

    protected static ?int $navigationGroupSort = 3;

    // Hide from lower-level users
    public static function shouldRegisterNavigation(): bool
    {
    return true;
    }

    public static function form(Schema $schema): Schema
    {
        
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                // TextInput::make('password')
                //     ->required()
                //     ->password()
                //     ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                //     ->dehydrated(fn ($state) => filled($state))
                //     ->maxLength(255),
                // User Type should be the type_name option from user_type table.
                // score
                
                Select::make('user_value_id')
                    ->required()
                    ->options(UserValue::query()->pluck('category', 'id')->toArray())
                    ->label('User Value'),
                Select::make('user_level_id')
                    ->required()
                    ->options(UserLevel::query()->pluck('title', 'id')->toArray())
                    ->label('User Level'),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                // User value category
                TextColumn::make('userValue.category')->label('User Value'),
                // Department name from departments table
                TextColumn::make('department.name')->label('Department'),
                // level name from user_levels table
                TextColumn::make('level.title')->label('User Level'),
            ])
            ->filters([
                // Add filters here
                
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
