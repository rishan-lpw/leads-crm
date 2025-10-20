<?php

namespace App\Filament\Resources\HuntersResource\Components\Tabs;

use App\Models\PaymentStatus;
use App\Models\Funnel;
use App\Models\User;
// use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;

class ActivityTabs
{
	public static function getActivityListSchema(): array
	{
		return [
			Section::make('Activity List')
				->collapsible()
				->heading(function ($record) {
					$activityType = ucfirst($record->activity_type ?? 'Activity');
					return match ($record->activity_type) {
						'email' => '✉️ Email Activity',
						'meeting' => '📅 Meeting',
						'site_visit' => '🏠 Site Visit',
						'message' => '💬 Message',
						'follow_up' => '🔄 Follow Up',
						'call' => '📞 Call',
						default => "📋 {$activityType}",
					};
				})
				->description(function ($record) {
					$date = $record->created_at ? $record->created_at->format('M d, Y H:i') : 'No date';
					$user = User::find($record->assigned_by)?->username ?? 'Unknown';
					$paymentStatus = PaymentStatus::find($record->payment_status_id)?->payment_status ?? 'Not found';
					$funnel = Funnel::find($record->funnel_id)?->category ?? 'Not found';
					return "{$date} | By: {$user} | Payment Status: {$paymentStatus} | Funnel: {$funnel}";
				})
				->schema([
					TextEntry::make('activity_type')->label('Activity Type')->weight('bold'),
					TextEntry::make('comments')->label('Comments')->placeholder('No comments'),
					TextEntry::make('created_at')->label('Date')->dateTime('M d, Y H:i'),
					TextEntry::make('user.username')->label('Done By')->icon('heroicon-o-user'),
					TextEntry::make('paymentStatus.payment_status')->label('Payment Status')->badge(),
				])
				->columns(3)
				->collapsed(),
		];
	}

	public static function createActivityTab(string $label, string $icon, callable $queryModifier): Tab
	{
		return Tab::make($label)
			->icon($icon)
			->schema([
				\Filament\Infolists\Components\RepeatableEntry::make('activities')
					->label($label)
					->getStateUsing(function ($record) use ($queryModifier) {
						$query = $record->activities()->with('user', 'paymentStatus');
						$queryModifier($query);
						return $query->latest()->limit(5)->get();
					})
					->schema(self::getActivityListSchema()),
			]);
	}
}
