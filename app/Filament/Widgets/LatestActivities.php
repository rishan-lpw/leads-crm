<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestActivities extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::with(['customer', 'user'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Call' => 'success',
                        'Email' => 'info',
                        'Meeting' => 'warning',
                        'Sale' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Activity $record): string => route('filament.admin.resources.activities.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
