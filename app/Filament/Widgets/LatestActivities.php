<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Forms\Components\Tabs\Tab;
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
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('lead.customer.firstname')->label('Customer')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('lead.user.name')->label('Hunter')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('activity_type')->label('Type')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(30)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('scheduled_at')->label('Scheduled At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('due_at')->label('Due At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('last_checked_at')->label('Last Checked At')->dateTime()->sortable(),
                
                // Activity data from activity table
                // Activity status and level_score
                Tables\Columns\TextColumn::make('activities.followUp.status')->label('Status')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('activities.followUp.level_score')->label('Level Score')->sortable()->searchable(),
            ])
            ->actions([
                // Tables\Actions\Action::make('view')
                //     ->url(fn (Activity $record): string => route('filament.admin.resources.activities.edit', ['record' => $record]))
                //     ->icon('heroicon-m-eye'),
            ]);
    }
}
