<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\CustomerMergeResource\Pages\ListCustomerMerges;
use App\Filament\Resources\CustomerMergeResource\Pages\CreateCustomerMerge;
use App\Filament\Resources\CustomerMergeResource\Pages\EditCustomerMerge;
use App\Filament\Resources\CustomerMergeResource\Pages;
use App\Filament\Resources\CustomerMergeResource\RelationManagers;
use App\Models\CustomerMerge;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerMergeResource extends Resource
{
    protected static ?string $model = CustomerMerge::class;

    protected static ?string $navigationLabel = 'Customer Merge';

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-s-users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Primary and secondary customers can be select from customer table.
                Select::make('primary_customer_id')
                    ->label('Primary Customer')
                    ->relationship('primaryCustomer', 'firstname')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('secondary_customer_id')
                    ->label('Secondary Customer')
                    ->relationship('secondaryCustomer', 'firstname')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('merge_at')
                    ->label('Merge Date')
                    ->required()
                    ->default(now()),
                // Merged By select the user.name from user table
                Select::make('merged_by')
                    ->label('Merged By')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->default(auth()->id())
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('primaryCustomer.firstname')
                    ->label('Primary Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('secondaryCustomer.firstname')
                    ->label('Secondary Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Merged By'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('merged_by')
                ->label('Merged By')
                ->relationship('user', 'name'),
                
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => ListCustomerMerges::route('/'),
            'create' => CreateCustomerMerge::route('/create'),
            'edit' => EditCustomerMerge::route('/{record}/edit'),
        ];
    }
}
