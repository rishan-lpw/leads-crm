<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingPaymentResource\Pages;
use App\Filament\Resources\PendingPaymentResource\RelationManagers;
use App\Models\PendingPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendingPaymentResource extends Resource
{
    protected static ?string $model = PendingPayment::class;

    protected static ?string $label = 'Pending Payment';

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
        return $table
            ->columns([
                //
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
            'index' => Pages\ListPendingPayments::route('/'),
            'create' => Pages\CreatePendingPayment::route('/create'),
            'edit' => Pages\EditPendingPayment::route('/{record}/edit'),
        ];
    }
}
