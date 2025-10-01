<?php

namespace App\Filament\Resources\HuntersResource\Components;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class ToolbarActions
{
    public static function getToolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip('Delete Selected')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Property Leads')
                    ->modalDescription('Are you sure you want to delete these property leads? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete them')
                    ->visible(fn() => auth()->user()->user_level_id != 1),

                BulkAction::make('toggle_pin')
                    ->label('Toggle Pin')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $toggledCount = 0;
                        foreach ($records as $record) {
                            $record->update([
                                'is_active' => $record->is_active == 1 ? 0 : 1,
                            ]);
                            $toggledCount++;
                        }
                        Notification::make()
                            ->title('Pin Updated')
                            ->body("Toggled pin status for {$toggledCount} leads.")
                            ->success()
                            ->send();
                    }),

                BulkAction::make('toggle_favourite')
                    ->label('Toggle Favourite')
                    ->color('warning')
                    ->action(function (Collection $records) {
                        $toggledCount = 0;
                        foreach ($records as $record) {
                            $record->update([
                                'is_trending' => $record->is_trending == 1 ? 0 : 1,
                            ]);
                            $toggledCount++;
                        }
                        Notification::make()
                            ->title('Favourite Updated')
                            ->body("Toggled favourite status for {$toggledCount} leads.")
                            ->success()
                            ->send();
                    }),

                BulkAction::make('export_selected')
                    ->label('Export Selected')
                    ->color('info')
                    ->action(function (Collection $records) {
                        Notification::make()
                            ->title('Export Started')
                            ->body('Export of selected leads has been initiated.')
                            ->info()
                            ->send();
                    }),
            ]),
        ];
    }
}