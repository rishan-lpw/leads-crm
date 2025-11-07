<?php

namespace App\Filament\Resources\HuntersResource\Components;

use App\Filament\Resources\HuntersResource\Components\Actions\RecordActions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn as TablesTextColumn;
use Filament\Tables\Columns\BadgeColumn as TablesBadge;
use App\Models\PaymentStatus;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\TextColumn as ColumnText;
use Filament\Tables\Columns\IconColumn as ColumnIcon;
use App\Filament\Resources\HuntersResource\Components\Support\LpwData;

class TableColumns
{
    public static function getColumns(): array
    {
        return [
            
            // ColumnIcon::make('is_pin')
            //     ->label('')
            //     ->boolean()
            //     ->trueIcon('bi-pin-fill')
            //     ->falseIcon('bi-pin')
            //     ->trueColor('primary')
            //     ->falseColor('gray')
            //     ->action(function ($record) {
            //         $record->is_pin = ! $record->is_pin;
            //         $record->save();
            //     })
            //     ->tooltip(fn($state): string => $state ? 'Unpin' : 'Pin')
            //     ->sortable()
            //     // Should be only visible to the user_level_id 2, 4, 5.
            //     ->visible(fn() => auth()->user()->user_level_id == 2 || auth()->user()->user_level_id == 4 || auth()->user()->user_level_id == 5)
            //     ->toggleable(),

            ColumnIcon::make('is_favourite')
                ->label('')
                ->boolean()
                ->trueIcon('heroicon-s-star')
                ->falseIcon('heroicon-o-star')
                ->trueColor('warning')
                ->falseColor('gray')
                ->sortable()
                // if is_favourite is true, then update the is_favourite to 0. Otherwise, update the is_favourite to 1.
                ->action(function ($record) {
                    $record->is_favourite = ! $record->is_favourite;
                    $record->save();
                })
                ->tooltip(fn($state): string => $state ? 'Unfavourite' : 'Favourite')
                ->toggleable(),

            ColumnText::make('customer.firstname')
                ->label('Customer')
                ->limit(24)
                ->formatStateUsing(function ($state, $record) {
                    // Add 📌 emoji before the name if is_pin == 1
                    return $record->is_pin ? "📌 {$state}" : $state;
                })
                ->tooltip(fn($record) => $record->customer->email)
                ->description(fn($record) => Str::limit($record->customer->email ?? '', 22))
                ->searchable()
                ->sortable(),

            ColumnText::make('posted_date')
                ->label('Posted Date')
                // Add 2nd column for updated_at as '3 days ago' by using the date difference
                ->description(function ($record) {
                    return $record->updated_at->diffForHumans();
                })
                ->dateTime('M d, Y')
                ->sortable(),

            // Latest activity payment status with color from payment_status.color
            ColumnText::make('latest_payment_status')
                ->label('Status')
                ->badge()
                // limit to 10 characters
                ->limit(10)
                ->action(RecordActions::getAddActivityAction())
                ->getStateUsing(function ($record) {
                    $activities = $record->activities ?? collect();
                    $latest = $activities->sortByDesc('created_at')->first();
                    return $latest?->paymentStatus?->payment_status ?? 'Unknown';
                })
                // ->tooltip(function ($state, $record) {
                //     $activities = $record->activities ?? collect();
                //     $latest = $activities->sortByDesc('created_at')->first();
                //     return $latest?->paymentStatus?->sub_status ?? 'Unknown';
                // })
                ->description(function ($record) {
                    $activities = $record->activities ?? collect();
                    $latest = $activities->sortByDesc('created_at')->first();
                    return $latest?->paymentStatus?->sub_status ?? 'Unknown';
                })
                ->color(function ($state, $record) {
                    $activities = $record->activities ?? collect();
                    $latest = $activities->sortByDesc('created_at')->first();
                    $color = strtolower((string) ($latest?->paymentStatus?->color ?? 'secondary'));

                    return match ($color) {
                        'red' => 'danger',
                        'orange' => 'warning',
                        'yellow' => 'info',
                        'green' => 'success',
                        'black' => 'gray',
                        'grey', 'gray' => 'gray',
                        'primary', 'success', 'warning', 'danger', 'info', 'secondary' => $color,
                        default => 'secondary',
                    };
                })
                ->toggleable()
                ->sortable(),

            // ColumnText::make('status')
            //     ->label('Status')
            //     ->badge()
            //     ->colors([
            //         'primary' => 'new',
            //         'secondary' => 'follow_up',
            //         'success' => 'system',
            //         'warning' => 'to_be_expired',
            //         'danger' => 'expired',
            //     ])
            //     ->toggleable()
            //     ->sortable(),

            ColumnText::make('source')
                ->label('Source')
                ->badge()
                ->colors([
                    'primary' => 'pending_payments',
                    'success' => 'ikman',
                    'warning' => 'facebook',
                    'secondary' => 'other',
                ])
                ->sortable(),

            ColumnText::make('property_summary')
                ->label('Property Data Summary')
                // Display data not exceeding 2 lines
                ->getStateUsing(function ($record) {
                    $type = ucfirst($record->type);
                    $propertyType = ucfirst($record->propty_type);

                    // Add ⬆️ for high weight equal to 'high' and ⬇️ for low weight equal to 'low'.
                    // $weight = $record->weight;
                    // if ($weight == 'high') {
                    //     $type = "⬆️ $type";
                    // } elseif ($weight == 'low') {
                    //     $type = "⬇️ $type";
                    // } elseif ($weight == 'medium') {
                    //     $type = "↔️ $type";
                    // }

                    $typeBadge = "<span class='badge badge-type'>{$type}</span>";
                    $propertyTypeBadge = "<span class='badge badge-prop'>{$propertyType}</span>";

                    return "$typeBadge - $propertyTypeBadge ";
                })
                ->html()
                // Add badge for property type and type. Only apply for the fields type and propty_type.
                // Add badge color info.
                // ->badge()
                // ->colors([
                //     // Apply info color as the default color.
                //     'info' => 'default',
                // ])
                ->description(function ($record) {
                    $priceInMillions = $record->price / 1_000_000;
                    $priceInBillions = $record->price / 1_000_000_000;
                    $priceInThousands = $record->price / 1_000;
                    
                    if ($priceInBillions >= 1) {
                        $priceFormatted = number_format($priceInBillions) . ' B';
                    } elseif ($priceInMillions >= 1) {
                        $priceFormatted = number_format($priceInMillions) . ' M';
                    } elseif ($priceInThousands >= 1) {
                        $priceFormatted = number_format($priceInThousands) . ' K';
                    } else {
                        $priceFormatted = number_format($record->price);
                    }

                    // Add Bootstrap Icons for weight indicators
                    $weight = $record->weight;
                    $iconHtml = '';
                    if ($weight == 'high') {
                        $iconHtml = '<i class="bi bi-arrow-up-circle" style="color: #d80000;"></i> ';
                    } elseif ($weight == 'low') {
                        $iconHtml = '<i class="bi bi-arrow-down-circle" style="color: #00bf00;"></i> ';
                    } elseif ($weight == 'medium') {
                        $iconHtml = '<i class="bi bi-dash-circle" style="color: #2b00ed;"></i> ';
                    }
                    
                    $city = ucfirst($record->city);
                    return new HtmlString($iconHtml . $priceFormatted . " | " . $city);
                })
                ->searchable(['type', 'propty_type', 'city', 'price', 'weight'])
                ->sortable()
                ->wrap(),

            ColumnText::make('latest_comment')
                ->label('Latest Comment')
                ->getStateUsing(function ($record) {
                    // Use the preloaded activities collection instead of querying per record
                    $activities = $record->activities ?? collect();
                    $latest = $activities->filter(fn($a) => !empty($a->comments))
                                         ->sortByDesc('created_at')
                                         ->first();

                    return $latest->comments ?? 'No Comment';
                })
                ->description(function ($record) {
                    try {
                        // Get call logs from CallLog API
                        $callLogs = LpwData::getCallLogsForRecord($record);
                        
                        if (empty($callLogs)) {
                            return null;
                        }
                        
                        // Get the latest call log entry
                        $latestCallLog = collect($callLogs)->first();
                        
                        $parts = [];
                        
                        if (isset($latestCallLog['call_summary_stats']) && $latestCallLog['call_summary_stats'] !== null && $latestCallLog['call_summary_stats'] !== '') {
                            $stats = $latestCallLog['call_summary_stats'];
                            if (strtolower($stats) === 'yes') {
                                $parts[] = '<i class="bi bi-check-circle" style="color: #10b981; font-size: 16px;"></i>';
                            } elseif (strtolower($stats) === 'no') {
                                $parts[] = '<i class="bi bi-x-circle" style="color: #ef4444; font-size: 16px;"></i>';
                            } else {
                                $parts[] = $stats;
                            }
                        }
                        // Add qa_final_percentage if available
                        if (isset($latestCallLog['qa_final_percentage']) && $latestCallLog['qa_final_percentage'] !== null && $latestCallLog['qa_final_percentage'] !== '') {
                            $parts[] = $latestCallLog['qa_final_percentage'] . '%';
                        }
                        
                        // Add qa_scorecard if available
                        // if (isset($latestCallLog['qa_scorecard']) && $latestCallLog['qa_scorecard'] !== null && $latestCallLog['qa_scorecard'] !== '') {
                        //     $parts[] = 'Score: ' . $latestCallLog['qa_scorecard'];
                        // }

                        // Add call_summary_category, call_summary_tag and "call_summary_stats": "No",
                        if (isset($latestCallLog['call_summary_category']) && $latestCallLog['call_summary_category'] !== null && $latestCallLog['call_summary_category'] !== '') {
                            $parts[] = $latestCallLog['call_summary_category'];
                        }
                        if (isset($latestCallLog['call_summary_tag']) && $latestCallLog['call_summary_tag'] !== null && $latestCallLog['call_summary_tag'] !== '') {
                            $parts[] = $latestCallLog['call_summary_tag'];
                        }
                        
                        if (empty($parts)) {
                            return null;
                        }
                        
                        // Check if any part contains HTML (icons)
                        $hasHtml = false;
                        foreach ($parts as $part) {
                            if (strip_tags($part) !== $part) {
                                $hasHtml = true;
                                break;
                            }
                        }
                        
                        if ($hasHtml) {
                            return new HtmlString(implode(' | ', $parts));
                        }
                        
                        return implode(' | ', $parts);
                    } catch (\Throwable $e) {
                        return null;
                    }
                })
                ->limit(30)
                // Add tooltip for the latest comment as $latest->comments
                ->tooltip(fn($record) => $record->activities->sortByDesc('created_at')->first()->comments ?? 'No Comment')
                ->toggleable()
                ->action(RecordActions::getAddActivityAction())
                ->sortable(),

            ColumnText::make('progress_icons')
                ->label('Progress')
                ->html()
                ->getStateUsing(function ($record) {
                    // Use preloaded activities collection to avoid N+1 queries.
                    $activities = $record->activities ?? collect();
                    // sort desc and take latest 4 then show oldest->newest (reverse)
                    $latest = $activities->sortByDesc('created_at')->take(4)->values()->reverse();

                    if ($latest->isEmpty()) {
                        return '<i class="bi bi-circle text-gray-400" title="No activities"></i>';
                    }

                    $colorMap = [
                        'red'    => '#dc2626', // red-600
                        'orange' => '#eab308', // orange-600
                        'green'  => '#16a34a', // green-600
                        'black'  => '#374151', // gray-700
                        'blue'   => '#2563eb', // blue-600
                        'yellow' => '#eab308', // yellow-500
                    ];

                    $result = [];
                    foreach ($latest as $activity) {
                        $color = strtolower(trim((string) ($activity->paymentStatus?->color ?? '')));
                        $paymentStatus = $activity->paymentStatus?->payment_status ?? 'Unknown';
                        $hexColor = $colorMap[$color] ?? '#6b7280'; // default gray-500
                        
                        $result[] = sprintf(
                            '<i class="bi bi-circle-fill" style="color: %s;" title="%s"></i>',
                            $hexColor,
                            htmlspecialchars($paymentStatus)
                        );
                    }

                    return '<span class="flex gap-1">' . implode(' ', $result) . '</span>';
                })
                // Add last activity date as the description
                ->description(function ($record) {
                    $activities = $record->activities ?? collect();
                    $latest = $activities->sortByDesc('created_at')->first();

                    if (! $latest?->created_at) {
                        return null;
                    }

                    return $latest->created_at->diffForHumans();
                })
                ->toggleable()
                ->sortable(),

            ColumnText::make('user.username')
                ->label('AM')
                ->searchable()
                ->sortable(),

            // ColumnText::make('activity_icons')->label('Funnel Stage')->html()->getStateUsing(
            //     function ($record) {
            //         $activities = $record->activities ?? collect();
            //         $activitiesSorted = $activities->sortBy('created_at');
            //         if ($activitiesSorted->isEmpty()) {
            //             return new HtmlString('<span class="text-gray-400">No-Activity</span>');
            //         }
            //         $emojiSets = ['contacted' => ['🔴', '🟠', '🟡', '🟤', '🔵', '🟣', '🟢'], 'rna' => ['🟥', '🟧', '🟨'], 'not_interested' => ['🔶', '🔷'],];
            //         $icons = [];
            //         foreach ($activitiesSorted as $activity) {
            //             $category = strtolower($activity->funnel?->category ?? '');
            //             $stage = (int) ($activity->funnel?->stage ?? 0);
            //             if ($category && isset($emojiSets[$category])) {
            //                 $emojis = $emojiSets[$category];
            //                 $index = max(0, min($stage - 1, count($emojis) - 1));
            //                 $icons[] = sprintf('<span class="inline-flex items-center justify-center w-6 h-6 text-sm rounded-full bg-gray-100 shadow-sm" title="%s - Stage %d">%s</span>', ucfirst(str_replace('_', ' ', $category)), $stage, $emojis[$index]);
            //             }
            //         }
            //     }),

            ColumnText::make('activity_icons')
                ->label('Funnel Stage')
                ->html()
                ->getStateUsing(function ($record) {
                    // Load activities with funnel relationship only when needed
                    $activities = $record->activities()->with('funnel')->get();

                    // Track the latest completed stage per category
                    $latestStages = [
                        'contacted' => 0,
                        'rna' => 0,
                        'not_interested' => 0,
                    ];

                    foreach ($activities as $activity) {
                        $category = strtolower($activity->funnel?->category ?? '');
                        $stage = (int) ($activity->funnel?->stage ?? 0);

                        if (array_key_exists($category, $latestStages) && $stage > $latestStages[$category]) {
                            $latestStages[$category] = $stage;
                        }
                    }

                    // Define total stages per category
                    $stagesCount = [
                        'contacted' => 7,
                        'rna' => 3,
                        'not_interested' => 2,
                    ];

                    // Build the HTML lines for each category
                    $lines = [];

                    foreach ($stagesCount as $category => $count) {
                        $completed = $latestStages[$category];
                        $iconsHtml = [];

                        for ($i = 1; $i <= $count; $i++) {
                            if ($i <= $completed) {
                                // Completed stage - filled circle with color
                                $color = match($category) {
                                    'contacted' => 'color: #10b981;',
                                    'rna' => 'color: #f59e0b;',
                                    'not_interested' => 'color: #ef4444;',
                                    default => 'color: #6b7280;'
                                };
                                $iconsHtml[] = sprintf(
                                    '<i class="bi bi-%d-circle-fill w-4 h-4 inline-block mx-0.5" style="%s" title="%s - Stage %d"></i>',
                                    $i, $color, ucfirst(str_replace('_', ' ', $category)), $i
                                );
                            } else {
                                // Empty stage - outline circle
                                $iconsHtml[] = sprintf(
                                    '<i class="bi bi-%d-circle w-4 h-4 inline-block mx-0.5" style="color: #d1d5db;" title="%s - Stage %d"></i>',
                                    $i, ucfirst(str_replace('_', ' ', $category)), $i
                                );
                            }
                        }

                        $label = ucfirst(str_replace('_', ' ', $category));
                        $lines[] = sprintf(
                            '<div class="leading-tight text-sm flex items-center space-x-1" title="%s">%s</div>',
                            $label,
                            implode(' ', $iconsHtml)
                        );
                    }

                    // If no activities, show default empty state
                    if ($activities->isEmpty()) {
                        $lines = [];
                        foreach ($stagesCount as $category => $count) {
                            $iconsHtml = [];
                            for ($i = 1; $i <= $count; $i++) {
                                $iconsHtml[] = sprintf(
                                    '<i class="bi bi-%d-circle w-4 h-4 inline-block mx-0.5" style="color: #d1d5db;" title="%s - Stage %d"></i>',
                                    $i, ucfirst(str_replace('_', ' ', $category)), $i
                                );
                            }
                            $label = ucfirst(str_replace('_', ' ', $category));
                            $lines[] = sprintf(
                                '<div class="leading-tight text-sm flex items-center space-x-1" title="%s">%s</div>',
                                $label,
                                implode(' ', $iconsHtml)
                            );
                        }
                    }

                    return new HtmlString(implode('', $lines));
                })
                ->sortable(),

            // BadgeColumn::make('weight')
            //     ->label('Weight')
            //     ->getStateUsing(function ($record) {
            //         return $record->weight;
            //     })
            //     ->colors([
            //         'primary' => fn($state) => $state >= 700,
            //         'warning' => fn($state) => $state >= 300 && $state < 700,
            //         'secondary' => fn($state) => $state < 300,
            //     ])
            //     ->toggleable()
            //     ->sortable(),

            // Add column to display the score
            ColumnText::make('score')
                ->label('Score')
                // Display score category based on the score value.
                // Add the badge color based on the score category.
                ->badge()
                ->color('warning')
                ->getStateUsing(function ($record) {
                    if ($record->score < 1.0) {
                        return 'Low';
                    } elseif ($record->score >= 1.0 && $record->score < 7.0) {
                        return 'Medium';
                    } elseif ($record->score >= 7.0) {
                        return 'High';
                    }
                })
                ->description(function ($record) {
                    return $record->score;
                })
                ->sortable()
                ->toggleable(),

            ColumnText::make('street')
                ->label('Street')
                ->searchable()
                ->sortable()
                // limit column width size to 100px
                ->limit(20)
                ->width('20px')
                ->tooltip(fn($record) => $record->street)
                ->toggleable(),

            // ColumnText::make('service_type')
            //     ->label('Service Type')
            //     ->searchable()
            //     ->sortable()
            //     ->toggleable(),

            ColumnIcon::make('is_trending')
                ->label('Trending')
                ->boolean()
                ->trueIcon('heroicon-s-fire')
                ->falseIcon('heroicon-s-minus')
                ->trueColor('warning')
                ->falseColor('secondary')
                ->toggleable()
                ->sortable(),
        ];
    }
}