<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerMergeResource\Pages;
use App\Filament\Resources\CustomerMergeResource\RelationManagers;
use App\Models\CustomerMerge;
use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?string $navigationGroup = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-s-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Primary and secondary customers can be select from customer table.
                Forms\Components\Select::make('primary_customer_id')
                    ->label('Primary Customer')
                    ->relationship('primaryCustomer', 'firstname')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('secondary_customer_id')
                    ->label('Secondary Customer')
                    ->relationship('secondaryCustomer', 'firstname')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('merge_at')
                    ->label('Merge Date')
                    ->required()
                    ->default(now()),
                // Merged By select the user.name from user table
                Forms\Components\Select::make('merged_by')
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
                Tables\Columns\TextColumn::make('primaryCustomer.firstname')
                    ->label('Primary Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('secondaryCustomer.firstname')
                    ->label('Secondary Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Merged By'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('merged_by')
                ->label('Merged By')
                ->relationship('user', 'name'),
                
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListCustomerMerges::route('/'),
            'create' => Pages\CreateCustomerMerge::route('/create'),
            'edit' => Pages\EditCustomerMerge::route('/{record}/edit'),
        ];
    }
}
