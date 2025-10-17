<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Services\LpwApiService;
use App\Filament\Resources\HuntersResource;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Cache;

class CustomerAds extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static string $resource = HuntersResource::class;
    protected static ?string $title = 'Customer Ads';
    protected static ?string $navigationLabel = 'Customer Ads';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';
    protected static ?int $navigationSort = 30;
    
    // protected static string $view = 'filament.resources.hunters-resource.pages.customer-ads';

    public $customerId = '';
    public $adsData = [];

    public function mount($record = null): void
    {
        // Load from record if provided (from route parameter)
        if ($record) {
            // Extract cust_id from the record object
            $this->customerId = $record;
        } else {
            // Load from query param if provided
            $this->customerId = request()->query('user_id', '');
        }
        
        if ($this->customerId) {
            // dd($this->customerId);
            $this->loadAds();
        }
        // dd($this->adsData);
    }

    public function loadAds(): void
    {
        if (!$this->customerId) {
            $this->adsData = [];
            // dd($this->adsData);
            return;
        }
        // dd($this->customerId);
        try {
            $cacheKey = "customer_ads_{$this->customerId}";
            
            // Check cache first (5 minutes)
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                $this->adsData = $cached;
                return;
            }

            $service = app(LpwApiService::class);
            $result = $service->getUserAds($this->customerId, 2);
            // dd($result);
            // Normalize the response
            if (isset($result['results']) && is_array($result['results'])) {
                $this->adsData = $result['results'];
            } elseif (is_array($result)) {
                $this->adsData = $result;
            } else {
                $this->adsData = [];
            }
            // dd($this->adsData);
            // Cache for 5 minutes
            // Cache::put($cacheKey, $this->adsData, 300);
            
            // if (empty($this->adsData)) {
            //     Notification::make()
            //         ->title('No Ads Found')
            //         ->body('No ads data found for this customer.')
            //         ->warning()
            //         ->send();
            // } else {
            //     Notification::make()
            //         ->title('Ads Loaded')
            //         ->body('Loaded ' . count($this->adsData) . ' ads successfully.')
            //         ->success()
            //         ->send();
            // }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Loading Ads')
                ->body('Failed to load ads: ' . $e->getMessage())
                ->danger()
                ->send();
            
            $this->adsData = [];
        }
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->records(fn() => collect($this->adsData)->map(fn($item) => (object) $item))
            ->columns([
                TextColumn::make('ad_id')
                    ->label('Ad ID')
                    ->sortable()
                    ->copyable()
                    ->searchable(),

                TextColumn::make('heading')
                    ->label('Title')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->heading ?? null)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn($state) => match(strtolower($state ?? '')) {
                        'sale' => 'success',
                        'rent' => 'warning',
                        'land' => 'info',
                        default => 'gray'
                    }),

                TextColumn::make('propty_type')
                    ->label('Property Type')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('LKR')
                    ->sortable(),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable(),

                TextColumn::make('street')
                    ->label('Street')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->street ?? null)
                    ->toggleable(),

                TextColumn::make('posted_date')
                    ->label('Posted Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('contact_name')
                    ->label('Contact')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        '1' => 'Active',
                        '3' => 'Pending',
                        '0' => 'Inactive',
                        default => 'Unknown'
                    })
                    ->color(fn($state) => match($state) {
                        '1' => 'success',
                        '3' => 'warning',
                        '0' => 'danger',
                        default => 'gray'
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'sale' => 'Sale',
                        'rent' => 'Rent',
                        'lease' => 'Lease',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => $record->url ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn($record) => !empty($record->url)),

                Action::make('copy_id')
                    ->label('Copy ID')
                    ->icon('heroicon-o-clipboard')
                    ->action(function ($record) {
                        Notification::make()
                            ->title('Ad ID Copied')
                            ->body('Ad ID: ' . ($record->ad_id ?? 'N/A'))
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        Cache::forget("customer_ads_{$this->customerId}");
                        $this->loadAds();
                    })
                    ->visible(fn() => !empty($this->customerId)),

                Action::make('load_ads')
                    ->label('Load Ads')
                    ->icon('heroicon-o-magnifying-glass')
                    ->form([
                        TextInput::make('customer_id')
                            ->label('Customer ID')
                            ->required()
                            ->numeric()
                            ->default($this->customerId)
                    ])
                    ->action(function (array $data) {
                        $this->customerId = $data['customer_id'];
                        $this->loadAds();
                    }),
            ])
            ->emptyStateHeading('No Ads Data')
            ->emptyStateDescription('Click "Load Ads" to fetch customer ads data.')
            ->emptyStateIcon('heroicon-o-newspaper')
            ->defaultSort('posted_date', 'desc');
    }
}

