<?php

namespace App\Filament\Resources\RenewResource\Components;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class TableFilters
{
    public static function get(): array
    {
        return [
            SelectFilter::make('user_id')
                ->label('User')
                ->relationship('user', 'name')
                ->searchable(),

            SelectFilter::make('propty_type')
                ->label('Property Type')
                ->options([
                    'house' => 'House',
                    'apartment' => 'Apartment',
                    'land' => 'Land',
                    'commercial' => 'Commercial',
                    'villa' => 'Villa',
                    'townhouse' => 'Townhouse',
                ]),

            Filter::make('posted_date')
                ->schema([
                    DatePicker::make('posted_date_from')->label('From'),
                    DatePicker::make('posted_date_to')->label('To'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['posted_date_from'] ?? null, fn($q, $v) => $q->whereDate('posted_date', '>=', $v))
                        ->when($data['posted_date_to'] ?? null, fn($q, $v) => $q->whereDate('posted_date', '<=', $v));
                }),

            Filter::make('price_range')
                ->schema([
                    TextInput::make('price_min')->label('Min')->numeric()->prefix('LKR'),
                    TextInput::make('price_max')->label('Max')->numeric()->prefix('LKR'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['price_min'] ?? null, fn($q, $v) => $q->where('price', '>=', $v))
                        ->when($data['price_max'] ?? null, fn($q, $v) => $q->where('price', '<=', $v));
                }),
        ];
    }
}