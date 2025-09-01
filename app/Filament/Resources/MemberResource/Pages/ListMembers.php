<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
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
};
