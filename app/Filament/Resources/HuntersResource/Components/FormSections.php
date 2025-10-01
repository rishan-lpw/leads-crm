<?php

namespace App\Filament\Resources\HuntersResource\Components;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput as FormsTextInput;

class FormSections
{
    public static function getSections(): array
    {
        return [
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('heading')
                        ->label('Property Heading')
                        ->required()
                        ->maxLength(255),
                    Select::make('type')
                        ->label('Listing Type')
                        ->options([
                            'sell' => 'For Sale',
                            'rent' => 'For Rent',
                            'lease' => 'For Lease',
                        ])
                        ->required(),
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
                        ->required(),
                    Select::make('service_type')
                        ->label('Service Type')
                        ->options([
                            'complete' => 'Complete Service',
                            'basic' => 'Basic Listing',
                            'premium' => 'Premium Service',
                        ]),
                ])
                ->columns(2),

            Section::make('Location Details')
                ->schema([
                    TextInput::make('street')
                        ->label('Street Address')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->label('City')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('lat')
                        ->label('Latitude')
                        ->numeric()
                        ->step(0.00000001),
                    TextInput::make('lng')
                        ->label('Longitude')
                        ->numeric()
                        ->step(0.00000001),
                ])
                ->columns(2),

            Section::make('Property Description')
                ->schema([
                    Textarea::make('desc')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Pricing Information')
                ->schema([
                    TextInput::make('price')
                        ->label('Main Price')
                        ->numeric()
                        ->prefix('LKR'),
                    TextInput::make('alt_price')
                        ->label('Alternative Price')
                        ->numeric(),
                    Select::make('alt_currency')
                        ->label('Alternative Currency')
                        ->options([
                            'LKR' => 'LKR',
                            'USD' => 'USD',
                            'EUR' => 'EUR',
                            'GBP' => 'GBP',
                        ]),
                    Select::make('price_type')
                        ->label('Price Type')
                        ->options([
                            'total' => 'Total Price',
                            'per_sq_ft' => 'Per Square Foot',
                            'per_month' => 'Per Month',
                            'negotiable' => 'Negotiable',
                        ]),
                    TextInput::make('price_monthly')
                        ->label('Monthly Price')
                        ->numeric()
                        ->prefix('LKR'),
                    TextInput::make('price_land_pp')
                        ->label('Land Price per Perch')
                        ->numeric()
                        ->prefix('LKR'),
                    TextInput::make('price_land_total')
                        ->label('Total Land Price')
                        ->numeric()
                        ->prefix('LKR'),
                ])
                ->columns(2),

            Section::make('Contact Information')
                ->schema([
                    Select::make('contact_type')
                        ->label('Contact Type')
                        ->options([
                            'owner' => 'Property Owner',
                            'agent' => 'Real Estate Agent',
                            'developer' => 'Developer',
                        ]),
                    TextInput::make('contact_name')
                        ->label('Contact Name')
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('Media & Links')
                ->schema([
                    Toggle::make('pic')
                        ->label('Has Pictures'),
                    TextInput::make('pic_count')
                        ->label('Picture Count')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('youtube_link')
                        ->label('YouTube Link')
                        ->url()
                        ->maxLength(255),
                    TextInput::make('video_link')
                        ->label('Video Link')
                        ->url()
                        ->maxLength(255),
                    TextInput::make('image_360')
                        ->label('360° Image Link')
                        ->url()
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('Status & Settings')
                ->schema([
                    Select::make('status')
                        ->label('Lead Status')
                        ->options([
                            'new' => 'New',
                            'follow_up' => 'Follow Up',
                            'system' => 'System',
                            'to_be_expired' => 'To Be Expired',
                            'expired' => 'Expired',
                        ])
                        ->default('new')
                        ->required(),
                    Select::make('source')
                        ->label('Lead Source')
                        ->options([
                            'pending_payments' => 'Pending Payments',
                            'ikman' => 'IKMAN',
                            'facebook' => 'Facebook',
                            'other' => 'Other',
                        ])
                        ->searchable(),

                    TextInput::make('weight')
                        ->label('Priority Weight')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(1000)
                        ->helperText('Auto-calculated based on keywords and criteria. Higher = Higher Priority')
                        ->disabled()
                        ->dehydrated(false),

                    Select::make('is_active')
                        ->label('Active Status')
                        ->options([
                            0 => 'Inactive',
                            1 => 'Active',
                            2 => 'Special',
                            3 => 'Pending'
                        ])
                        ->default(1)
                        ->native(false)
                        ->required(),
                    Toggle::make('is_trending')
                        ->label('Trending'),
                    Toggle::make('blocked')
                        ->label('Blocked')
                        ->helperText('Block this listing from public view'),
                ])
                ->columns(3),

            Section::make('System Fields')
                ->schema([
                    TextInput::make('ad_id')
                        ->label('Advertisement ID')
                        ->numeric()
                        ->required(),
                    TextInput::make('cust_id')
                        ->label('Customer ID')
                        ->numeric()
                        ->required(),
                    TextInput::make('user_id')
                        ->label('User ID')
                        ->numeric(),
                ])
                ->columns(3)
                ->collapsed(),
        ];
    }
}