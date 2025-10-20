<?php

namespace App\Filament\Resources\HuntersResource\Components\Sections;

use App\Filament\Resources\HuntersResource\Components\Support\LpwData;
use Filament\Infolists\Components\RepeatableEntry;
// use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class CallScriptSection
{
	public static function build(): array
	{
		return [
			RepeatableEntry::make('call_scripts')
				->label('')
				->contained(false)
				->lazy()
				->getStateUsing(function ($record) {
					return LpwData::getCallScriptForRecord($record);
				})
				->schema([
					Section::make()
						->heading(fn($state) => ($state['category'] ?? 'Script') . ' • ' . ($state['title'] ?? ''))
						->collapsible()
						->schema([
							TextEntry::make('content')
								->label('')
								->columnSpanFull()
								->formatStateUsing(function ($state, $record) {
									$content = is_array($record) ? ($record['content'] ?? '') : ($state ?? '');
									if (empty($content)) {
										return new HtmlString('<span class="text-gray-500 italic">No content available</span>');
									}
									$formatted = nl2br(e($content));
									return new HtmlString("<div class='bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm leading-relaxed whitespace-pre-wrap'>{$formatted}</div>");
								})
								->html(),
						])
						->columnSpanFull(),
				])
				->columnSpanFull(),
		];
	}
}
