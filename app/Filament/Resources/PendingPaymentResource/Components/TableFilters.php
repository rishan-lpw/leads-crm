<?php

namespace App\Filament\Resources\PendingPaymentResource\Components;

use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class TableFilters
{
    public static function get(): array
    {
        return [
            Filter::make('status')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'new' => 'New',
                            'follow_up' => 'Follow Up',
                            'upsell' => 'Upsell',
                            'expired' => 'Expired',
                            'not_interested' => 'Not Interested',
                            'renew' => 'Renew',
                        ]),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        $data['status'] ?? null,
                        fn (Builder $query, $status): Builder => $query->where('status', $status),
                    );
                }),

            Filter::make('last_update_date')
                ->schema([
                    DatePicker::make('last_update_date')
                        ->label('Last Update Date'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        $data['last_update_date'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('last_update_date', $date),
                    );
                }),
        ];
    }
}