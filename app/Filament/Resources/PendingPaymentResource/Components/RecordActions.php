<?php

namespace App\Filament\Resources\PendingPaymentResource\Components;

use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RecordActions
{
    public static function get(): array
    {
        $view = ViewAction::make()
            ->modalHeading(fn($record) => 'Hunter Details - ' . ($record->name ?? $record->heading ?? ''))
            ->modalWidth('6xl')
            ->schema([
                Tabs::make('HunterTabs')
                    ->tabs([
                        Tab::make('Summary')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Basic Information')
                                    ->schema([
                                        TextEntry::make('name')->label('Name')->size('lg')->weight('bold'),
                                        TextEntry::make('status')->label('Status')->badge(),
                                        TextEntry::make('tel')->label('Telephone')->icon('heroicon-o-phone'),
                                        TextEntry::make('price')->label('Amount')->money('LKR')->size('lg')->weight('bold')->color('success'),
                                        TextEntry::make('source')->label('Source')->badge()->color('primary'),
                                        TextEntry::make('am')->label('Account Manager'),
                                    ])->columns(2),

                                // Additional sections kept minimal — reuse or extend as needed
                            ]),
                        // other tabs can be re-added or extended here
                    ])->columnSpanFull(),
            ]);

        return [$view];
    }
}