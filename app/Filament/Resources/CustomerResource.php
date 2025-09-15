<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use App\Models\Role;
use Filament\Forms\Form;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
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

    protected static ?string $navigationGroup = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Select::make('role_id')
                    ->relationship('role', 'role_name')
                    // ->required()
                    ->label('Role'),
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
                TextColumn::make('role.role_name')
                    ->label('Role')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
            ])
            ->filters([
                // Add name, email, id filters
                Tables\Filters\Filter::make('name')
                    ->label('Name')
                    ->form([
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
                Tables\Filters\Filter::make('email')
                    ->label('Email')
                    ->form([
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Search by Email'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['email'], function (Builder $query, $value) {
                            $query->where('email', 'like', "%{$value}%");
                        });
                    }),
                Tables\Filters\Filter::make('id')
                    ->label('ID')
                    ->form([
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
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
