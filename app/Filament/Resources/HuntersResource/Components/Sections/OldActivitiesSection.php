<?php

namespace App\Filament\Resources\HuntersResource\Components\Sections;

use App\Filament\Resources\HuntersResource\Components\Support\LpwData;
use Filament\Infolists\Components\RepeatableEntry;
// use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Section;

class OldActivitiesSection
{
	public static function build(): array
	{
		return [
			RepeatableEntry::make('old_activities')
				->label('')
				->contained(false)
				->lazy()
				->getStateUsing(function ($record) {
					$items = LpwData::getOldActivitiesForRecord($record);
					if (empty($items)) return [];
					return collect($items)->sortByDesc('date_time')->values()->toArray();
				})
				->schema([
					Section::make()
						->collapsible()
						->collapsed()
						->heading(fn($item) => sprintf(
							'%s • %s%s',
							ucfirst($item['action'] ?? 'Activity'),
							$item['date_time'] ?? 'N/A',
							isset($item['by']) && $item['by'] ? " • by {$item['by']}" : ''
						))
						->description(fn($item) => collect([
							'Status' => $item['payment_status'] ?? 'N/A',
							'Comments' => $item['comments'] ?? 'N/A',
							'Converted' => ($item['is_converted'] ?? '') === 'Y' ? '✅ Yes' : '❌ No',
						])->map(fn($v, $k) => "{$k}: {$v}")->implode(' | '))
						->schema([
							ComponentsGrid::make(3)->schema([
								TextEntry::make('id')->label('Activity ID')->placeholder('N/A'),
								TextEntry::make('uid')->label('Customer ID')->placeholder('N/A'),
								TextEntry::make('by')->label('Done By')->placeholder('N/A'),
								TextEntry::make('action')->label('Action')->placeholder('N/A'),
								TextEntry::make('payment_status')->label('Payment Status')->badge()->color(fn($state) => match (strtolower($state)) {
									'paid', 'completed' => 'success',
									'pending' => 'warning',
									'expired', 'failed' => 'danger',
									default => 'gray',
								})->placeholder('N/A'),
								TextEntry::make('value')->label('Value')->placeholder('N/A'),
								TextEntry::make('date_time')->label('Date & Time')->placeholder('N/A'),
								TextEntry::make('old_am')->label('Old AM')->placeholder('N/A'),
								TextEntry::make('reminder')->label('Reminder')->placeholder('N/A'),
							]),
							TextEntry::make('comments')->label('Comments')->columnSpanFull()->default('No comments available'),
						]),
				]),
		];
	}
}
