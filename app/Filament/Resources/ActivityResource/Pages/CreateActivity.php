<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;
    // Add activity form interface 

    // Fields:  
    // protected $fillable = [
    //     'lead_id', 'user_id', 'action', 'qty', 'value', 'ad_id', 'comments', 'reminder', 'date_time', 'old_am'
    // ];
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormSchema(): array
    {
        return [
            // Add these fields to the form
            // Select::make('lead_id')
            //     ->label('Lead')
            //     ->relationship('lead', 'id')
            //     ->searchable()
            //     ->preload()
            //     ->required(),
            Select::make('user_id')
                ->label('Agent')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('action')
                ->label('Action')
                ->required()
                ->maxLength(255),
            TextInput::make('qty')
                ->label('Quantity')
                ->numeric()
                ->required(),
            TextInput::make('value')
                ->label('Value')
                ->numeric()
                ->required(),
            TextInput::make('ad_id')
                ->label('Ad ID')
                ->maxLength(255),
            Textarea::make('comments')
                ->label('Comments')
                ->rows(3)
                ->maxLength(65535),
            DatePicker::make('reminder')
                ->label('Reminder Date')
                ->format('Y-m-d'),
            DateTimePicker::make('date_time')
                ->label('Date & Time')
                ->format('Y-m-d H:i:s'),
            // Select old_am from list of users
            Select::make('old_am')
                ->label('Old AM')
                ->options(User::pluck('name', 'name'))
                ->searchable()
                ->preload()
                ->maxLength(255),
        ];
    }

}
