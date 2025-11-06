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
				
				// Radio button to select the status - get distinct statuses (only 5 stages)
				Radio::make('status')
					->label('Status')
					->inline()
					->options(function () {
						// Get distinct statuses from payment_status table
						$statuses = PaymentStatus::distinct()
							->whereNotNull('status')
							->where('status', '!=', '')
							->pluck('status', 'status')
							->toArray();
						
						// Limit to 5 stages as mentioned
						return array_slice($statuses, 0, 5, true);
					})
					->required()
					->reactive(),
				
				// Sub Status - reactive to status selection
				Select::make('sub_status')
					->label('Sub Status')
					->options(function (callable $get) {
						$selectedStatus = $get('status');
						
						if (!$selectedStatus) {
							return [];
						}
						
						// Get sub_statuses for the selected status
						$subStatuses = PaymentStatus::where('status', $selectedStatus)
							->whereNotNull('sub_status')
							->where('sub_status', '!=', '')
							->distinct()
							->pluck('sub_status', 'sub_status')
							->toArray();
						
						return $subStatuses;
					})
					->searchable()
					->reactive()
					->visible(fn (callable $get) => !empty($get('status')))
					->required(fn (callable $get) => !empty($get('status'))),
				
				Radio::make('follow_up_type')->label('Option')->inline()->options([
					'follow_up' => 'Follow Up',
					'reminder' => 'Reminder',
				]),
				Select::make('funnel_id')
					->label('Funnel (Category/Stage)')
					->options(function (callable $get, $record) {
						$selectedStatus = $get('status');
						
						if (!$selectedStatus) {
							return [];
						}
						
						// Normalize status to lowercase and trim for comparison
						$statusLower = strtolower(trim($selectedStatus));
						
						// Map status to funnel IDs based on rules
						$funnelIds = [];
						
						// Check for "follow up" or "paid" status
						if (stripos($statusLower, 'follow') !== false || stripos($statusLower, 'paid') !== false) {
							// Follow up or Paid -> funnel ids 1-7
							$funnelIds = [1, 2, 3, 4, 5, 6, 7];
						} 
						// Check for "RNA" status
						elseif (stripos($statusLower, 'rna') !== false) {
							// RNA -> funnel ids 8, 9, 10
							$funnelIds = [8, 9, 10];
						} 
						// Check for "not interested" status
						elseif ((stripos($statusLower, 'not') !== false && stripos($statusLower, 'interest') !== false) || stripos($statusLower, 'not_interested') !== false) {
							// Not Interested -> funnel ids 11, 12
							$funnelIds = [11, 12];
						}
						
						if (empty($funnelIds)) {
							return [];
						}
						
						// Get all funnels matching the IDs
						$allFunnels = Funnel::whereIn('id', $funnelIds)
							->orderBy('category')
							->orderByRaw('CAST(stage AS UNSIGNED)')
							->get();
						
						if ($allFunnels->isEmpty()) {
							return [];
						}
						
						// Get completed funnel IDs for this record
						$completedFunnelIds = [];
						if ($record && $record->exists) {
							$completedFunnelIds = $record->activities()
								->whereNotNull('funnel_id')
								->pluck('funnel_id')
								->unique()
								->values()
								->toArray();
						}
						
						// Filter out completed funnels and build options
						$availableFunnels = [];
						foreach ($allFunnels as $funnel) {
							// Only show if not already completed
							if (!in_array($funnel->id, $completedFunnelIds)) {
								$category = ucfirst(str_replace('_', ' ', $funnel->category ?? 'Unknown'));
								$stage = $funnel->stage ?? 0;
								$availableFunnels[$funnel->id] = $category . ' - Stage ' . $stage;
							}
						}
						
						return $availableFunnels;
					})
					->searchable()
					->required()
					->reactive()
					->visible(fn (callable $get) => !empty($get('status')))
					->hint('Next stages to be completed.'),
				DateTimePicker::make('follow_up_date_time')->label('Follow Up Date & Time')->visible(fn ($get) => $get('follow_up_type') === 'follow_up')->required(fn ($get) => $get('follow_up_type') === 'follow_up')->reactive(),
				DatePicker::make('reminder_date')->label('Reminder Date')->visible(fn ($get) => $get('follow_up_type') === 'reminder')->required(fn ($get) => $get('follow_up_type') === 'reminder')->reactive(),
				Textarea::make('comments')->label('Comments')->rows(4),
			])
			->action(function (array $data, $record) {
				// Find payment_status_id based on status and sub_status from payment_status table
				$paymentStatusId = null;
				
				if (!empty($data['status']) && !empty($data['sub_status'])) {
					// Try to find exact match with both status and sub_status
					$paymentStatus = PaymentStatus::where('status', $data['status'])
						->where('sub_status', $data['sub_status'])
						->first();
					
					if ($paymentStatus) {
						$paymentStatusId = $paymentStatus->id;
					}
				}
				
				// If no match found with both, try with just status
				if (!$paymentStatusId && !empty($data['status'])) {
					$paymentStatus = PaymentStatus::where('status', $data['status'])
						->whereNotNull('sub_status')
						->where('sub_status', '!=', '')
						->first();
					
					if ($paymentStatus) {
						$paymentStatusId = $paymentStatus->id;
					}
				}
				
				// Last fallback: get any payment_status with matching status
				if (!$paymentStatusId && !empty($data['status'])) {
					$paymentStatus = PaymentStatus::where('status', $data['status'])
						->first();
					
					if ($paymentStatus) {
						$paymentStatusId = $paymentStatus->id;
					}
				}
				
				$activity = $record->activities()->create([
					'activity_type'     => $data['activity_type'] ?? null,
					'funnel_id'         => $data['funnel_id'] ?? null,
					'status'            => $data['status'] ?? null,
					'payment_status_id' => $paymentStatusId,
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
