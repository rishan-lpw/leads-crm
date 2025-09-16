<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // // Add activity edit form interface
    // protected function getFormSchema(): array
    // {
    //     return [
    //         // Fields to add: action, notes, followup.status, followup.level_score
    //         Select::make('action')
    //             ->label('Action')
    //             ->options([
    //                 'view' => 'View',
    //                 'call' => 'Call',
    //                 'email' => 'Email',
    //                 'visit' => 'Visit',
    //                 'follow_up' => 'Follow Up',
    //             ])
    //             ->required(),
    //         Textarea::make('notes')
    //             ->label('Notes')
    //             ->rows(4)
    //             ->placeholder('Enter activity notes here...')
    //             ->required(),
    //         Section::make('Follow Up Details')
    //             ->schema([
    //                 Select::make('followUp.status')
    //                     ->label('Follow Up Status')
    //                     ->options([
    //                         'pending' => 'Pending',
    //                         'completed' => 'Completed',
    //                         'cancelled' => 'Cancelled',
    //                         'in_progress' => 'In Progress',
    //                     ])
    //                     ->required(),
    //                 Select::make('followUp.level_score')
    //                     ->label('Level Score')
    //                     ->options([
    //                         // 1 - 5 scale
    //                         1 => '1 - Very Low',
    //                         2 => '2 - Low',
    //                         3 => '3 - Medium',
    //                         4 => '4 - High',
    //                         5 => '5 - Very High',   
    //                     ])
    //                     ->required(),
    //             ])->columns(2)
    //             ->collapsible()
    //             ->collapsed(),
    //     ];
    // }
}
