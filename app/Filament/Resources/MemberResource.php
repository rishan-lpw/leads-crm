<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Customer;
use App\Models\Member;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Members';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Member Name')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),

                // Add colourful badges for membership status
                Tables\Columns\TextColumn::make('membership_status')
                    ->label('Membership Status')
                    ->sortable()
                    ->searchable()
                    ->badge(fn ($state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('membership_category')
                    ->label('Membership Category')
                    ->sortable()
                    ->searchable(),

                // AM name: user.name
                Tables\Columns\TextColumn::make('user.name')
                    ->label('AM')
                    ->sortable()
                    ->searchable(),

                // Add a field called No. of Ads as show the ads count from add_on table for a relevant member
                Tables\Columns\TextColumn::make('ads_count')
                    ->label('No. of Ads')
                    ->counts('addOns')
                    ->sortable()
                    ->searchable(),

                // Payment Expiration Date
                Tables\Columns\TextColumn::make('payment_exp_date')
                    ->label('Payment Exp. Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        return \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success';
                    }),

                // Membership Expiration Date
                Tables\Columns\TextColumn::make('membership_exp_date')
                    ->label('Membership Exp. Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        return \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success';
                    }),

            ])
            ->filters([
                //
            ]);
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
