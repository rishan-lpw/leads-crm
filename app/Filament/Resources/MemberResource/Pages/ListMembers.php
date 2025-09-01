<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MemberResource::getModel()::query()
                    ->withCount('addOns')
                    ->with('user')
            )
            ->columns($this->getResource()::table($table)->getColumns())
            ->filters($this->getResource()::table($table)->getFilters())
            ->actions($this->getResource()::table($table)->getActions())
            ->bulkActions($this->getResource()::table($table)->getBulkActions());
    }

    public function getTabs(): array
    {
        return [
            // Names of tabs:
            // 1. All Agents
            // 2. Expired - Grace Period(AM)
            // 3. Expired - Deactivated(AM)
            // 4. Expired 2+ (Hunters)
            // 5. Un-allocated Agents
            'All Agents' => Tab::make('All Agents'),
            'Expired - Grace Period(AM)' => Tab::make('Expired - Grace Period(AM)'),
            'Expired - Deactivated(AM)' => Tab::make('Expired - Deactivated(AM)'),
            'Expired 2+ (Hunters)' => Tab::make('Expired 2+ (Hunters)'),
            'Un-allocated Agents' => Tab::make('Un-allocated Agents'),
        ];
    }
};
