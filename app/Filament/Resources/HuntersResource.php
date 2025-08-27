<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HuntersResource\Pages;
use App\Filament\Resources\HuntersResource\RelationManagers;
use App\Models\Lead;
use Filament\Forms;
// use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Components\Tabs;
use Filament\Tables\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Infolists\Components\Tabs as ComponentsTabs;
use Filament\Infolists\Components\Tabs\Tab as TabsTab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HuntersResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationLabel = 'Hunters';

    protected static ?string $navigationGroup = 'Private Sellers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('posted_date')
                    ->label('Posted Date')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('am')
                    ->label('AM')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('latest_comments')
                    ->label('Latest Comments')
                    ->limit(40)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('last_update_date')
                    ->label('Last Update Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_update_by')
                    ->label('Last Update By')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tel')
                    ->label('Tel')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('LKR')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('new')
                    ->label('New')
                    ->query(fn (Builder $query) => $query->where('status', 'new')),
                    // ->badge(fn () => Lead::where('status', 'new')->count()),

                Tables\Filters\Filter::make('follow_up')
                    ->label('Follow-Up')
                    ->query(fn (Builder $query) => $query->where('status', 'follow_up')),
                    // ->badge(fn () => Lead::where('status', 'follow_up')->count()),

                Tables\Filters\Filter::make('system')
                    ->label('System')
                    ->query(fn (Builder $query) => $query->where('status', 'system')),
                    // ->badge(fn () => Lead::where('status', 'system')->count()),

                Tables\Filters\Filter::make('all')
                    ->label('All')
                    ->query(fn (Builder $query) => $query),
                    // ->badge(fn () => Lead::count()),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Action::make('viewDetails')
                    ->label('View')
                    ->button()
                    ->modalHeading('Hunter Details')
                    ->modalWidth('lg')
                    ->modalContent(fn($record) => view(
                        'filament.hunters.details',
                        ['record' => $record]
                    ))
                    // ->modalActions([
                    //     Action::make('edit')
                    //         ->label('Edit')
                    //         ->color('primary')
                    //         ->url(fn($record) => static::getUrl('edit', ['record' => $record])),
                    //     Action::make('close')
                    //         ->label('Close')
                    //         ->color('secondary')
                    //         ->close(),
                    // ]),
            ])
            ->recordUrl(null); // disable row click
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHunters::route('/'),
            'create' => Pages\CreateHunters::route('/create'),
            'edit' => Pages\EditHunters::route('/{record}/edit'),
        ];
    }
}
