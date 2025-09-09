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
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('lead.customer.name')->label('Customer')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('lead.user.name')->label('Hunter')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('activity_type')->label('Type')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(30)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('scheduled_at')->label('Scheduled At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('due_at')->label('Due At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('last_checked_at')->label('Last Checked At')->dateTime()->sortable(),
                // follow up from activity follow up table
                // level_score
                Tables\Columns\TextColumn::make('activityFollowUp.level_score')->label('Follow Up Level Score')->sortable(),
                Tables\Columns\TextColumn::make('activityFollowUp.due_at')->label('Follow Up Due At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('activityFollowUp.last_checked_at')->label('Follow Up Last Checked At')->dateTime()->sortable(),


                // Tables\Columns\BooleanColumn::make('auto_status_updated')->label('Auto Status Updated')->sortable(),
                // Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
                // Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Activity $record): string => route('filament.admin.resources.activities.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
