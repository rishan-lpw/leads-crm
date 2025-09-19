<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('lead.customer.firstname')->label('Customer')->sortable()->searchable(),
                TextColumn::make('lead.user.name')->label('Hunter')->sortable()->searchable(),
                TextColumn::make('activity_type')->label('Type')->sortable()->searchable(),
                TextColumn::make('notes')->label('Notes')->limit(30)->sortable()->searchable(),
                TextColumn::make('scheduled_at')->label('Scheduled At')->dateTime()->sortable(),
                TextColumn::make('due_at')->label('Due At')->dateTime()->sortable(),
                TextColumn::make('last_checked_at')->label('Last Checked At')->dateTime()->sortable(),
                
                // Activity data from activity table
                // Activity status and level_score
                TextColumn::make('followUp.status')->label('Status')->sortable()->searchable(),
                TextColumn::make('followUp.level_score')->label('Level Score')->sortable()->searchable(),
            ])
            ->recordActions([
                // Tables\Actions\Action::make('view')
                //     ->url(fn (Activity $record): string => route('filament.admin.resources.activities.edit', ['record' => $record]))
                //     ->icon('heroicon-m-eye'),
            ]);
    }
}
