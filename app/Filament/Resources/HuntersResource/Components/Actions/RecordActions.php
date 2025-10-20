<?php

namespace App\Filament\Resources\HuntersResource\Components\Actions;

use App\Models\PaymentStatus;
use App\Models\Funnel;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecordActions
{
	public static function sendMessageAction(): Action
	{
		return Action::make('send_message')
			->label('Send Message')
			->icon('heroicon-s-chat-bubble-bottom-center-text')
			->color('primary')
			->modalWidth('xl')
			->schema([
				Select::make('message_template')->label('Message Template')->options(fn () => [])->searchable()->required(),
				Textarea::make('message')->label('Message')->rows(4)->required(),
			])
			->action(function (array $data, $record) {
				// Implement send logic
			});
	}

	public static function getAddActivityAction(): Action
	{
		return Action::make('add_activity')
			->label('Add Activity')
			->modalWidth('xl')
			->button()
			->icon('heroicon-o-plus')
			->color('primary')
			->form([
				Radio::make('activity_type')->label('Activity Type')->inline()->options([
					'email' => 'Email',
					'call' => 'Call',
					'meeting' => 'Meeting',
					'whatsapp' => 'WhatsApp',
				])->required()->reactive(),
				Select::make('payment_status_id')->label('Payment Status')->options(function () { return PaymentStatus::all()->pluck('payment_status', 'id')->toArray(); })->searchable()->required(),
				Radio::make('follow_up_type')->label('Option')->inline()->options([
					'follow_up' => 'Follow Up',
					'reminder' => 'Reminder',
				]),
				Select::make('funnel_id')->label('Funnel (category - stage)')->options(function (callable $get) {
					$paymentStatusId = (int) $get('payment_status_id');
					if (in_array($paymentStatusId, [2, 3])) {
						$allowedIds = [1, 2, 3, 4, 5, 6, 7];
					} elseif (in_array($paymentStatusId, [1, 15, 14])) {
						$allowedIds = [8, 9, 10];
					} elseif (in_array($paymentStatusId, [4, 9, 10])) {
						$allowedIds = [11, 12];
					} else {
						$allowedIds = [];
					}
					$query = Funnel::query()->orderBy('category')->orderBy('stage');
					if (!empty($allowedIds)) {
						$query->whereIn('id', $allowedIds);
					}
					return $query->get()->mapWithKeys(fn ($funnel) => [ $funnel->id => ucfirst($funnel->category) . ' - Stage ' . $funnel->stage ])->toArray();
				})->searchable()->required()->reactive()->hint('Filtered by payment status'),
				DateTimePicker::make('follow_up_date_time')->label('Follow Up Date & Time')->visible(fn ($get) => $get('follow_up_type') === 'follow_up')->required(fn ($get) => $get('follow_up_type') === 'follow_up')->reactive(),
				DatePicker::make('reminder_date')->label('Reminder Date')->visible(fn ($get) => $get('follow_up_type') === 'reminder')->required(fn ($get) => $get('follow_up_type') === 'reminder')->reactive(),
				Textarea::make('comments')->label('Comments')->rows(4),
			])
			->action(function (array $data, $record) {
				$activity = $record->activities()->create([
					'activity_type'     => $data['activity_type'] ?? null,
					'funnel_id'         => $data['funnel_id'] ?? null,
					'payment_status_id' => $data['payment_status_id'] ?? null,
					'comments'          => $data['comments'] ?? null,
					'assigned_by'       => Auth::id(),
				]);
				$followUpProvided = ! empty($data['follow_up_date_time']);
				$reminderProvided = ! empty($data['reminder_date']);
				if ($activity && ($followUpProvided || $reminderProvided)) {
					$payload = [
						'activity_id'    => $activity->id,
						'follow_up_time' => $followUpProvided ? $data['follow_up_date_time'] : null,
						'reminder_at'    => $reminderProvided ? $data['reminder_date'] : null,
						'created_at'     => now(),
						'updated_at'     => now(),
					];
					DB::table('activity_follow_up')->insert($payload);
				}
				Notification::make()->title('Activity added')->success()->send();
			});
	}
}
