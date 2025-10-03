<?php

namespace App\Filament\Resources\HuntersResource\Components;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn as TablesTextColumn;
use Filament\Tables\Columns\BadgeColumn as TablesBadge;
use App\Models\PaymentStatus;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\TextColumn as ColumnText;
use Filament\Tables\Columns\IconColumn as ColumnIcon;

class TableColumns
{
    public static function getColumns(): array
    {
        return [
            ColumnIcon::make('is_active')
                ->label('')
                ->boolean()
                ->trueIcon('bi-pin-fill')
                ->falseIcon('bi-pin')
                ->trueColor('primary')
                ->falseColor('gray')
                ->action(function ($record) {
                    $record->is_active = ! $record->is_active;
                    $record->save();
                })
                ->tooltip(fn($state): string => $state ? 'Unpin' : 'Pin')
                ->sortable(),

            ColumnText::make('customer.firstname')
                ->label('Customer')
                ->description(fn($record) => $record->customer->email)
                ->searchable()
                ->sortable(),

            ColumnText::make('posted_date')
                ->label('Posted Date')
                // Add 2nd column for updated_at
                ->description(function ($record) {
                    return $record->updated_at->format('M d, Y');
                })
                ->dateTime('M d, Y')
                ->sortable(),

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
                ->label('Property Summary Details')
                // Display data not exceeding 2 lines
                ->getStateUsing(function ($record) {
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
                    $price = 'LKR ' . $priceFormatted;
                    $type = ucfirst($record->type);
                    return "$price - $type";
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
                    $propertyType = ucfirst($record->propty_type);
                    $city = ucfirst($record->city);
                    return "$propertyType | $city";
                })
                ->searchable(['type', 'propty_type', 'city', 'price'])
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
                ->toggleable()
                ->sortable(),

            ColumnText::make('progress_icons')
                ->label('Progress')
                ->html()
                ->getStateUsing(function ($record) {
                    // Use preloaded activities collection to avoid N+1 queries.
                    $activities = $record->activities ?? collect();
                    // sort desc and take latest 3 then show oldest->newest (reverse)
                    $latest = $activities->sortByDesc('created_at')->take(3)->values()->reverse();

                    if ($latest->isEmpty()) {
                        return '🔘';
                    }

                    $lastActivityDate = $latest->last()->created_at->format('M d Y');

                    $map = [
                        'red'    => '🔴',
                        'orange' => '🟠',
                        'yellow' => '🟡',
                        'green'  => '🟢',
                        'black'  => '⚫',
                    ];

                    $result = [];
                    foreach ($latest as $activity) {
                        $color = strtolower(trim((string) ($activity->paymentStatus?->color ?? '')));
                        $result[] = $map[$color] ?? '🔘';
                    }

                    return "<span class='text-xs text-gray-500'>$lastActivityDate</span><br>" . implode('', $result);
                })
                ->toggleable()
                ->sortable(),

            ColumnText::make('user.username')
                ->label('AM')
                ->searchable()
                ->sortable(),

            ColumnText::make('activity_icons')
                ->label('Funnel Stage')
                ->html()
                ->getStateUsing(function ($record) {
                    $activities = $record->activities ?? collect();
                    $activitiesSorted = $activities->sortBy('created_at');

                    if ($activitiesSorted->isEmpty()) {
                        return new HtmlString('<span class="text-gray-400">No-Activity</span>');
                    }

                    $emojiSets = [
                        'contacted' => ['🔴', '🟠', '🟡', '🟤', '🔵', '🟣', '🟢'],
                        'rna' => ['🟥', '🟧', '🟨'],
                        'not_interested' => ['🔶', '🔷'],
                    ];

                    $icons = [];

                    foreach ($activitiesSorted as $activity) {
                        $category = strtolower($activity->funnel?->category ?? '');
                        $stage    = (int) ($activity->funnel?->stage ?? 0);

                        if ($category && isset($emojiSets[$category])) {
                            $emojis = $emojiSets[$category];
                            $index = max(0, min($stage - 1, count($emojis) - 1));

                            $icons[] = sprintf(
                                '<span class="inline-flex items-center justify-center w-6 h-6 text-sm rounded-full bg-gray-100 shadow-sm" title="%s - Stage %d">%s</span>',
                                ucfirst(str_replace('_', ' ', $category)),
                                $stage,
                                $emojis[$index]
                            );
                        }
                    }

                    return new HtmlString(
                        $icons
                            ? '<div class="flex flex-wrap gap-1 mt-1">' . implode('', $icons) . '</div>'
                            : '<span class="text-gray-400">No Activity</span>'
                    );
                })
                ->toggleable()
                ->sortable(),

            ColumnText::make('status')
                ->label('Status')
                ->badge()
                ->colors([
                    'primary' => 'new',
                    'secondary' => 'follow_up',
                    'success' => 'system',
                    'warning' => 'to_be_expired',
                    'danger' => 'expired',
                ])
                ->toggleable()
                ->sortable(),

            BadgeColumn::make('weight')
                ->label('Weight')
                ->getStateUsing(function ($record) {
                    return $record->weight;
                })
                ->colors([
                    'primary' => fn($state) => $state >= 700,
                    'warning' => fn($state) => $state >= 300 && $state < 700,
                    'secondary' => fn($state) => $state < 300,
                ])
                ->toggleable()
                ->sortable(),

            ColumnText::make('street')
                ->label('Street')
                ->searchable()
                ->sortable()
                ->toggleable(),

            ColumnText::make('service_type')
                ->label('Service Type')
                ->searchable()
                ->sortable()
                ->toggleable(),

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