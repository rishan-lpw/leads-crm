<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\HuntersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Notifications\Notification;

class EditHunters extends EditRecord
{
    protected static string $resource = HuntersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Hunter Record')
                ->modalDescription('Are you sure you want to delete this hunter record? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete it'),

            Action::make('mark_as_upsell')
                ->label('Mark as Upsell')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark as Upsell')
                ->modalDescription('Are you sure you want to mark this hunter as upsell?')
                ->modalSubmitActionLabel('Mark Upsell')
                ->action(function () {
                    $this->record->update([
                        'status' => 'upsell',
                        'note' => 'payment_completed',
                        'last_update_date' => now(),
                        'last_update_by' => auth()->user()->name ?? 'System',
                        'latest_comments' => ($this->record->latest_comments ? $this->record->latest_comments . "\n\n" : '') . 
                                           '[' . now()->format('Y-m-d H:i:s') . '] Marked as upsell by ' . (auth()->user()->name ?? 'System'),
                    ]);

                    Notification::make()
                        ->title('Hunter Upsell')
                        ->body('The hunter has been successfully marked as upsell.')
                        ->success()
                        ->send();

                    return redirect()->to(HuntersResource::getUrl('index'));
                }),

            Action::make('mark_renew')
                ->label('Mark as Renew')
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Mark as Renew')
                ->modalDescription('Are you sure you want to mark this hunter as renew?')
                ->modalSubmitActionLabel('Mark Renew')
                ->action(function () {
                    $this->record->update([
                        'status' => 'renew',
                        'source' => 'pending payments',
                        'last_update_date' => now(),
                        'last_update_by' => auth()->user()->name ?? 'System',
                        'latest_comments' => ($this->record->latest_comments ? $this->record->latest_comments . "\n\n" : '') . 
                                           '[' . now()->format('Y-m-d H:i:s') . '] Moved to pending payments by ' . (auth()->user()->name ?? 'System'),
                    ]);

                    Notification::make()
                        ->title('Moved to Renew')
                        ->body('The hunter has been moved to renew.')
                        ->success()
                        ->send();
                }),

            Action::make('add_comment')
                ->label('Add Comment')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->schema([
                    Textarea::make('new_comment')
                        ->label('Add New Comment')
                        ->required()
                        ->rows(4)
                        ->placeholder('Enter your comment here...')
                        ->helperText('This comment will be added to the existing comments with timestamp.'),
                ])
                ->action(function (array $data) {
                    $timestamp = now()->format('Y-m-d H:i:s');
                    $user = auth()->user()->name ?? 'System';
                    $newComment = "[{$timestamp}] {$user}: {$data['new_comment']}";
                    
                    $existingComments = $this->record->latest_comments;
                    $updatedComments = $existingComments 
                        ? $existingComments . "\n\n" . $newComment 
                        : $newComment;

                    $this->record->update([
                        'latest_comments' => $updatedComments,
                        'last_update_date' => now(),
                        'last_update_by' => $user,
                    ]);

                    Notification::make()
                        ->title('Comment Added')
                        ->body('Your comment has been successfully added.')
                        ->success()
                        ->send();
                })
                ->slideOver(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Primary hunter details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter hunter name'),

                                TextInput::make('tel')
                                    ->label('Telephone')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('+94 XX XXX XXXX'),

                                Select::make('source')
                                    ->label('Source')
                                    ->options([
                                        'ikman' => 'Ikman',
                                        'facebook' => 'Facebook',
                                        'website' => 'Website',
                                        'referral' => 'Referral',
                                        'pending payments' => 'Pending Payments',
                                    ])
                                    ->required()
                                    ->searchable(),

                                TextInput::make('am')
                                    ->label('Account Manager')
                                    ->maxLength(100)
                                    ->placeholder('Enter AM name'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Property Details')
                    ->description('Property and pricing information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('property_type')
                                    ->label('Property Type')
                                    ->options([
                                        'house' => 'House',
                                        'apartment' => 'Apartment',
                                        'land' => 'Land',
                                        'commercial' => 'Commercial',
                                        'villa' => 'Villa',
                                        'townhouse' => 'Townhouse',
                                    ])
                                    ->searchable(),

                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('LKR')
                                    ->placeholder('0.00')
                                    ->formatStateUsing(fn ($state): string => number_format($state, 2))
                                    ->dehydrateStateUsing(fn ($state): float => (float) str_replace(',', '', $state)),

                                Select::make('ad_type')
                                    ->label('Ad Type')
                                    ->options([
                                        'sell' => 'Sell',
                                        'rent' => 'Rent',
                                        'lease' => 'Lease',
                                    ])
                                    ->searchable(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('city')
                                    ->label('City')
                                    ->maxLength(100)
                                    ->placeholder('Enter city'),

                                TextInput::make('location')
                                    ->label('Location')
                                    ->maxLength(255)
                                    ->placeholder('Enter specific location'),

                                TextInput::make('duration')
                                    ->label('Duration')
                                    ->maxLength(50)
                                    ->placeholder('e.g., 6 months'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Status & Management')
                    ->description('Current status and management details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'new' => 'New',
                                        'follow_up' => 'Follow Up',
                                        'closed' => 'Closed',
                                        'rejected' => 'Rejected',
                                        'pending_payment' => 'Pending Payment',
                                        'upsell' => 'Upsell',
                                        'not_interested' => 'Not Interested',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state === 'pending_payment') {
                                            $set('source', 'pending payments');
                                        }
                                    }),

                                TextInput::make('last_update_by')
                                    ->label('Last Updated By')
                                    ->maxLength(100)
                                    ->default(auth()->user()->name ?? 'System')
                                    ->disabled(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Comments & Notes')
                    ->description('Additional information and comments')
                    ->schema([
                        Textarea::make('latest_comments')
                            ->label('Comments')
                            ->rows(6)
                            ->placeholder('Enter comments, notes, or follow-up information...')
                            ->helperText('Use this field to track communication history and important notes.'),
                    ])
                    ->collapsible(),

                Section::make('System Information')
                    ->description('System-managed fields (read-only)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('posted_date')
                                    ->label('Posted Date')
                                    ->disabled(),

                                DateTimePicker::make('last_update_date')
                                    ->label('Last Update Date')
                                    ->default(now())
                                    ->disabled(),

                                TextInput::make('user_type_id')
                                    ->label('User Type ID')
                                    ->disabled(),
                            ]),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    protected function beforeSave(): void
    {
        // Update last_update_date and last_update_by before saving
        $this->record->last_update_date = now();
        $this->record->last_update_by = auth()->user()->name ?? 'System';
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Hunter Updated')
            ->body('The hunter record has been successfully updated.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure last_update_date and last_update_by are set
        $data['last_update_date'] = now();
        $data['last_update_by'] = auth()->user()->name ?? 'System';
        
        return $data;
    }
}