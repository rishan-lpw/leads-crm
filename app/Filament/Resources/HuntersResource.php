<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HuntersResource\Pages;
use App\Filament\Resources\HuntersResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HuntersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Hunters';

    protected static ?string $navigationGroup = 'Private Sellers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        // I want to display hunter details in table, which retrieves data from the user table whose user_type_id is 1 or 2.
        return $table
            ->query(fn () => User::query()->whereIn('user_type', [1, 2]))
            ->columns([
                // Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('userType.type_name')->label('User Type'),
                Tables\Columns\TextColumn::make('userType.sub_type')->label('User Sub Type')->searchable(),
                Tables\Columns\TextColumn::make('level.title')->label('User Level'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHunters::route('/'),
            'create' => Pages\CreateHunters::route('/create'),
            'edit' => Pages\EditHunters::route('/{record}/edit'),
        ];
    }
}
