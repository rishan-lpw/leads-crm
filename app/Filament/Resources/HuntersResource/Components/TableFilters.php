<?php

namespace App\Filament\Resources\HuntersResource\Components;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

class TableFilters
{
    public static function getFilters(): array
    {
        return [
            SelectFilter::make('user_id')
                ->label('User')
                ->relationship('user', 'name')
                ->searchable(),

            SelectFilter::make('type')
                ->label('Listing Type')
                ->options([
                    'sell' => 'For Sale',
                    'rent' => 'For Rent',
                    'lease' => 'For Lease',
                ]),

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

            SelectFilter::make('source')
                ->label('Source')
                ->options([
                    'pending_payments' => 'Pending Payments',
                    'ikman' => 'IKMAN',
                    'facebook' => 'Facebook',
                    'other' => 'Other',
                ]),

            Filter::make('posted_date')
                ->schema([
                    DatePicker::make('posted_date_from')->label('Posted Date From'),
                    DatePicker::make('posted_date_to')->label('Posted Date To'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['posted_date_from'], fn(Builder $query, $value) => $query->whereDate('posted_date', '>=', $value))
                        ->when($data['posted_date_to'], fn(Builder $query, $value) => $query->whereDate('posted_date', '<=', $value));
                })
                ->label('Posted Date Range'),

            Filter::make('price_range')
                ->schema([
                    TextInput::make('price_min')->label('Min Price')->numeric()->prefix('LKR'),
                    TextInput::make('price_max')->label('Max Price')->numeric()->prefix('LKR'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['price_min'], fn(Builder $query, $value) => $query->where('price', '>=', $value))
                        ->when($data['price_max'], fn(Builder $query, $value) => $query->where('price', '<=', $value));
                })
                ->label('Price Range'),

            // Add filter for score range
            Filter::make('score_range')
                ->schema([
                    TextInput::make('score_min')->label('Min Score')->numeric(),
                    TextInput::make('score_max')->label('Max Score')->numeric(),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['score_min'], fn(Builder $query, $value) => $query->where('score', '>=', $value))
                        ->when($data['score_max'], fn(Builder $query, $value) => $query->where('score', '<=', $value));
                })
                ->label('Score Range'),
        ];
    }
}