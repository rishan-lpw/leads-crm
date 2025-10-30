<?php

namespace App\Filament\Resources\HuntersResource\Components\Sections;

use App\Filament\Resources\HuntersResource\Components\Support\LpwData;
use Filament\Infolists\Components\RepeatableEntry;
// use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class CallLogSection
{
	public static function build(): array
	{
		return [
			RepeatableEntry::make('call_logs')
				->lazy()
				->label('')
				->contained(false)
				->getStateUsing(function ($record) {
					$logs = LpwData::getCallLogsForRecord($record);
					if (empty($logs)) return [];
					return collect($logs)->take(4)->sortByDesc('datetime')->values()->toArray();
				})
				->schema([
					Section::make('')
						->schema([
							ComponentsGrid::make(5)->lazy()->schema([
								TextEntry::make('am')->label('AM')->default(fn($log) => $log['am'] ?? 'Unknown'),
								TextEntry::make('datetime')->label('Date & Time')->default(fn($log) => $log['datetime'] ?? 'N/A'),
								TextEntry::make('talktime')->label('Duration')->default(fn($log) => ($log['talktime'] ?? '0') . ' sec'),
								TextEntry::make('sentiment')->label('Sentiment')->badge()->color(fn($log) => match (strtolower((string)($log['sentiment'] ?? ''))) {
									'positive' => 'success',
									'negative' => 'danger',
									'neutral' => 'gray',
									'mixed' => 'warning',
									default => 'info',
								})->default(fn($log) => $log['sentiment'] ?? 'N/A'),
								TextEntry::make('event')->label('Event')->default(fn($log) => $log['event'] ?? 'N/A'),
								TextEntry::make('summary_en')->label('Summary (English)')->default(fn($log) => $log['summary_en'] ?? 'N/A')->columnSpanFull(),
							]),
							Section::make('More Details')
								->collapsible()
								->collapsed()
								->lazy()
								->schema([
									ComponentsGrid::make(3)->schema([
										TextEntry::make('summary_si')->label('Summary (සිංහල)')->default(fn($log) => substr($log['summary_si'] ?? 'N/A', 0, 50))->columnSpanFull(),
										TextEntry::make('summary_en')->label('Summary (English)')->default(fn($log) => $log['summary_en'] ?? 'N/A')->columnSpanFull(),
										TextEntry::make('summary_ta')->label('Summary (தமிழ்)')->default(fn($log) => $log['summary_ta'] ?? 'N/A')->columnSpanFull(),
										TextEntry::make('agent')->label('Agent')->default(fn($log) => $log['agent'] ?? 'Unknown'),
										TextEntry::make('language')->label('Language')->default(fn($log) => $log['language'] ?? 'Unknown'),
										TextEntry::make('status')->label('Status')->badge()->color('primary')->default(fn($log) => $log['status'] ?? 'Unknown'),
									]),
									// Recording URL should be a clickable link to open in a new tab.
									TextEntry::make('recording_url')->openUrlInNewTab(true)->label('Recording URL')->default(fn($log) => $log['recording_url'] ?? 'N/A')->formatStateUsing(function ($state) { return new HtmlString("<a href='{$state}' target='_blank'>{$state}</a>"); }),
									TextEntry::make('transcript')->label('Transcript')->default(fn($log) => $log['transcript'] ?? 'No transcript available')->formatStateUsing(function ($state) { return new HtmlString($state); })->columnSpanFull(),
								])
								->columnSpanFull(),
						])
						->columnSpanFull(),
				])
		];
	}
}
