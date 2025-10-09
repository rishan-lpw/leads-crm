<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use App\Filament\Resources\HuntersResource;
use App\Filament\Resources\HuntersResource\Components\SendMessageAction;
use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewActivities extends ViewRecord
{
    protected static string $resource = HuntersResource::class;
    
    protected string $view = 'filament.resources.hunters-resource.pages.view-activities';
    
    public function getTitle(): string
    {
        return 'Activities - ' . $this->record->heading;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('addActivity')
                ->label('Add Activity')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalHeading('Add New Activity')
                ->form([
                    Select::make('stage')
                        ->label('Activity Type')
                        ->options([
                            'email' => 'Email',
                            'meeting' => 'Meeting',
                            'site_visit' => 'Site Visit',
                            'follow_up' => 'Follow Up',
                            'note' => 'Note',
                            'other' => 'Other',
                        ])
                        ->required(),
                        
                    TextInput::make('action')
                        ->label('Action Required')
                        ->required()
                        ->placeholder('What action needs to be taken?'),
                        
                    Textarea::make('comments')
                        ->label('Comments')
                        ->rows(4)
                        ->placeholder('Enter activity details...'),
                        
                    TextInput::make('qty')
                        ->label('Quantity')
                        ->numeric()
                        ->placeholder('1'),
                        
                    TextInput::make('value')
                        ->label('Value (LKR)')
                        ->numeric()
                        ->prefix('LKR'),
                        
                    Select::make('level_score')
                        ->label('Lead Score (1-10)')
                        ->options([
                            1 => '1 - Very Low',
                            2 => '2 - Low',
                            3 => '3 - Below Average',
                            4 => '4 - Average',
                            5 => '5 - Moderate',
                            6 => '6 - Above Average',
                            7 => '7 - Good',
                            8 => '8 - High',
                            9 => '9 - Very High',
                            10 => '10 - Excellent',
                        ]),
                        
                    DatePicker::make('reminder')
                        ->label('Reminder Date'),
                        
                    DateTimePicker::make('date_time')
                        ->label('Activity Date & Time')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        Activity::create([
                            'lead_id' => $this->record->id,
                            'user_id' => Auth::id(),
                            'stage' => $data['stage'],
                            'action' => $data['action'],
                            'comments' => $data['comments'] ?? null,
                            'qty' => $data['qty'] ?? null,
                            'value' => $data['value'] ?? null,
                            'level_score' => $data['level_score'] ?? null,
                            'ad_id' => $this->record->ad_id,
                            'reminder' => $data['reminder'] ?? null,
                            'date_time' => $data['date_time'],
                            'old_am' => Auth::user()?->name ?? 'System',
                        ]);
                        
                        Notification::make()
                            ->title('Activity Added Successfully')
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error Adding Activity')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                            
                        Log::error('Activity creation failed', [
                            'lead_id' => $this->record->id,
                            'error' => $e->getMessage(),
                            'data' => $data
                        ]);
                    }
                }),
                
            Action::make('addCallLog')
                ->label('Add Call Log')
                ->icon('heroicon-o-phone')
                ->color('success')
                ->modalHeading('Add New Call Log')
                ->form([
                    TextInput::make('action')
                        ->label('Call Purpose')
                        ->required()
                        ->placeholder('Reason for the call'),
                        
                    Textarea::make('comments')
                        ->label('Call Summary')
                        ->rows(4)
                        ->required()
                        ->placeholder('Summarize the call conversation...'),
                        
                    TextInput::make('qty')
                        ->label('Duration (minutes)')
                        ->numeric()
                        ->placeholder('Call duration in minutes'),
                        
                    TextInput::make('value')
                        ->label('Deal Value Discussed (LKR)')
                        ->numeric()
                        ->prefix('LKR'),
                        
                    Select::make('level_score')
                        ->label('Lead Quality After Call (1-10)')
                        ->options([
                            1 => '1 - Very Low Interest',
                            2 => '2 - Low Interest',
                            3 => '3 - Below Average',
                            4 => '4 - Average Interest',
                            5 => '5 - Moderate Interest',
                            6 => '6 - Above Average',
                            7 => '7 - Good Interest',
                            8 => '8 - High Interest',
                            9 => '9 - Very High Interest',
                            10 => '10 - Ready to Buy/Sell',
                        ])
                        ->required(),
                        
                    DatePicker::make('reminder')
                        ->label('Follow-up Reminder'),
                        
                    DateTimePicker::make('date_time')
                        ->label('Call Date & Time')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        Activity::create([
                            'lead_id' => $this->record->id,
                            'user_id' => Auth::id(),
                            'stage' => 'call',
                            'action' => $data['action'],
                            'comments' => $data['comments'],
                            'qty' => $data['qty'] ?? null,
                            'value' => $data['value'] ?? null,
                            'level_score' => $data['level_score'],
                            'ad_id' => $this->record->ad_id,
                            'reminder' => $data['reminder'] ?? null,
                            'date_time' => $data['date_time'],
                            'old_am' => Auth::user()?->name ?? 'System',
                        ]);
                        
                        Notification::make()
                            ->title('Call Log Added Successfully')
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error Adding Call Log')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                            
                        Log::error('Call log creation failed', [
                            'lead_id' => $this->record->id,
                            'error' => $e->getMessage(),
                            'data' => $data
                        ]);
                    }
                }),
            
            SendMessageAction::make($this->record),
        ];
    }
}
