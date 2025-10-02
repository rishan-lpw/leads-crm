<?php

namespace App\Filament\Resources\UpsellResource\Components;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Gate;

class RecordActions
{
    public static function get(): array
    {
        $view = ViewAction::make()
            ->label('')
            ->icon('heroicon-o-eye')
            ->modalHeading(fn($record) => 'Upsell - ' . ($record->heading ?? $record->id))
            ->modalWidth('4xl')
            ->visible(fn($record) => Gate::allows('view', $record))
            ->schema([
                Tabs::make('UpsellTabs')->tabs([
                    Tab::make('Overview')->schema([
                        Section::make('Basic')->schema([
                            TextEntry::make('heading')->label('Heading')->size('lg')->weight('bold'),
                            TextEntry::make('price')->label('Price')->money('LKR'),
                            TextEntry::make('status')->label('Status')->badge(),
                        ])->columns(2),
                    ])->columnSpanFull(),
                ]),
            ]);

        $edit = EditAction::make()
            ->label('')
            ->icon('heroicon-o-pencil')
            ->visible(fn($record) => Gate::allows('update', $record))
            ->slideOver();

        return [$view, $edit];
    }
}