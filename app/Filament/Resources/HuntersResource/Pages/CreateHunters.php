<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use App\Filament\Resources\HuntersResource;
use App\Models\Customer;
use App\Models\Lead;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Form as ComponentsForm;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CreateHunters extends CreateRecord
{
    protected static string $resource = HuntersResource::class;

    public function getTitle(): string
    {
        return 'Create Customer';
    }

    public function form(Schema $schema): Schema
    {
        $nextCustomerId = (Customer::max('id') ?? 0) + 1;

        return $schema
        ->schema([
                ComponentsSection::make('Customer Details')
                    ->collapsible()
                    ->schema([
                        // Show next customer id (disabled)
                        TextInput::make('cust_id_display')
                            ->label('Customer ID')
                            ->default($nextCustomerId)
                            ->disabled()
                            ->dehydrated(false), // don’t save this fake field
                        
                        // Hidden field to actually save into lead.cust_id                       
                        TextInput::make('cust_id')
                            ->label('Customer ID')
                            ->default($nextCustomerId)
                            ->hidden()
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                            
                        TextInput::make('customer.firstname')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer.surname')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer.mobile')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('customer.address')
                            ->label('Address')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                ComponentsSection::make('Property Details')
                    ->collapsible()
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'sell' => 'For Sale',
                                'rent' => 'For Rent',
                                'lease' => 'For Lease',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->required()
                            ->prefix('LKR'),
                        TextInput::make('heading')
                            ->label('Heading')
                            ->maxLength(255),
                        TextInput::make('location')
                            ->label('Location')
                            ->helperText('Optional, if different from Address')
                            ->maxLength(255),
                        Select::make('propty_type')
                            ->label('Property Type')
                            ->options([
                                'house' => 'House',
                                'apartment' => 'Apartment',
                                'land' => 'Land',
                                'commercial' => 'Commercial',
                                'villa' => 'Villa',
                                'townhouse' => 'Townhouse',
                            ])
                            ->native(false),
                        Select::make('service_type')
                            ->label('Listing Type')
                            ->options([
                                'complete' => 'Complete Service',
                                'basic' => 'Basic Listing',
                                'premium' => 'Premium Service',
                            ])
                            ->native(false),
                        Textarea::make('desc')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        DatePicker::make('posted_date')
                            ->label('Posted Date'),
                    ])
                    ->columns(2),

                ComponentsSection::make('System Information')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Select::make('source')
                            ->label('Source')
                            ->options([
                                'pending_payments' => 'Pending Payments',
                                'ikman' => 'IKMAN',
                                'facebook' => 'Facebook',
                                'other' => 'Other',
                            ])
                            ->native(false),
                        Select::make('user_id')
                            ->label('AM name')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            try {
                // First create the customer record
                $customerData = [
                    'firstname' => $data['customer']['firstname'],
                    'surname' => $data['customer']['surname'],
                    'email' => $data['customer']['email'],
                    'mobile' => $data['customer']['mobile'] ?? null,
                    'address' => $data['customer']['address'] ?? null,
                ];
                
                $customer = Customer::create($customerData);
                
                // Remove customer fields from lead data
                $leadData = $data;
                unset(
                    $leadData['customer'],
                    $leadData['cust_id_display']
                );
                
                // Fix the street field if location was provided
                if (isset($leadData['location'])) {
                    $leadData['street'] = $leadData['location'];
                    unset($leadData['location']);
                }
                
                // IMPORTANT: Explicitly set the cust_id field with the new customer ID
                $leadData['cust_id'] = $customer->id;

                Log::info('Creating lead with data', [
                    'customer_id' => $customer->id,
                    'lead_data' => $leadData
                ]);
                
                // Create the lead record
                $lead = Lead::create($leadData);
                
                Notification::make()
                    ->title('Lead Created')
                    ->body('New lead and customer records created successfully')
                    ->success()
                    ->send();
                    
                return $lead;
            } catch (\Exception $e) {
                Log::error('Error creating hunter record: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'data' => $data
                ]);
                
                Notification::make()
                    ->title('Error')
                    ->body('Failed to create records: ' . $e->getMessage())
                    ->danger()
                    ->send();
                
                throw $e;
            }
        });
    }
}
