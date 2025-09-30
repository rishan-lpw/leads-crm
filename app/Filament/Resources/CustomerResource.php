<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use App\Models\Role;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationLabel = 'Customer Registration';

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';

    protected static string | \BackedEnum | null $navigationIcon = 'fas-user-plus';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstname')
                    ->required()
                    ->maxLength(255)
                    ->label('First Name'),
                TextInput::make('surname')
                    ->required()
                    ->maxLength(255)
                    ->label('Surname'),
                TextInput::make('mobile')
                    ->required()
                    ->maxLength(15)
                    ->label('Mobile Number'),
                TextInput::make('mobile_alt')
                    ->maxLength(15)
                    ->label('Alternative Mobile Number'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('address')
                    ->maxLength(255)
                    ->label('Address'),
                // Select::make('role_id')
                //     ->relationship('role', 'role_name')
                //     // ->required()
                //     ->label('Role'),
            ])->columns(2); // Display form fields in 2 columns
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('firstname')
                    ->label('First Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('surname')
                    ->label('Surname')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('mobile')
                    ->label('Mobile Number')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                // TextColumn::make('role.role_name')
                //     ->label('Role')
                //     ->searchable()
                //     ->sortable()
                //     ->limit(50),
            ])
            ->filters([
                // Add name, email, id filters
                Filter::make('name')
                    ->label('Name')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Search by First or Surname'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['name'], function (Builder $query, $value) {
                            $query->where(function (Builder $query) use ($value) {
                                $query->where('firstname', 'like', "%{$value}%")
                                      ->orWhere('surname', 'like', "%{$value}%");
                            });
                        });
                    }),
                Filter::make('email')
                    ->label('Email')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Search by Email'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['email'], function (Builder $query, $value) {
                            $query->where('email', 'like', "%{$value}%");
                        });
                    }),
                Filter::make('id')
                    ->label('ID')
                    ->schema([
                        TextInput::make('id')
                            ->label('ID')
                            ->placeholder('Search by ID'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['id'], function (Builder $query, $value) {
                            $query->where('id', 'like', "%{$value}%");
                        });
                    }),
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
