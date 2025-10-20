<?php

namespace App\Filament\Resources\HuntersResource\Components\Sections;

use App\Filament\Resources\HuntersResource\Components\Support\LpwData;
// use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class ContactDetailsSections
{
	public static function forOverview(): Section
	{
		return Section::make(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['firstname'] ?? ($record->customer->firstname ?? 'Contact Details'))
			// ->icon('iconsax-bul-profile-circle')
			->columns(4)
			->lazy()
			->schema([
				TextEntry::make('lpw_email')
					->label('Email')
					->copyable()
					->copyMessage('Email copied')
					->icon('heroicon-s-envelope')
					->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['email'] ?? ($record->customer->email ?? 'N/A')),
				TextEntry::make('lpw_mobile')->label('Mobile')->icon('heroicon-s-phone')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['mobile'] ?? ($record->customer->mobile ?? 'N/A')),
				TextEntry::make('lpw_id')->label('ID')->icon('heroicon-s-identification')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['id'] ?? ($record->customer->id ?? 'N/A')),
				TextEntry::make('lpw_address')->label('Address')->icon('heroicon-s-map-pin')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['address'] ?? ($record->customer->address ?? 'N/A')),
				TextEntry::make('lpw_reg_date')->label('Registration Date')->icon('heroicon-s-calendar')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['reg_date'] ?? ($record->customer->reg_date ?? 'N/A')),
				TextEntry::make('lpw_source')->label('Source')->icon('heroicon-s-arrow-path-rounded-square')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['source'] ?? ($record->customer->source ?? 'N/A')),
				TextEntry::make('lpw_category')->label('Category')->icon('heroicon-s-tag')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['category'] ?? ($record->customer->category ?? 'N/A')),
				TextEntry::make('lpw_payment_status')->label('Payment Status')->badge()->icon('heroicon-s-credit-card')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['payment_status'] ?? ($record->customer->payment_status ?? 'N/A')),
				TextEntry::make('lpw_payment_exp_date')->label('Payment Expiry Date')->icon('heroicon-s-calendar')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['payment_exp_date'] ?? ($record->customer->payment_exp_date ?? 'N/A')),
				TextEntry::make('lpw_payment')->label('Payment')->icon('heroicon-s-credit-card')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['payment'] ?? ($record->customer->payment ?? 'N/A')),
				TextEntry::make('lpw_latest_action')->label('Latest Action')->icon('heroicon-s-clock')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['latest_action'] ?? ($record->customer->latest_action ?? 'N/A')),
				TextEntry::make('lpw_latest_comment')->label('Latest Comment')->icon('heroicon-s-chat-bubble-bottom-center-text')->columnSpan(2)->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['latest_comment'] ?? ($record->customer->latest_comment ?? 'N/A')),
				TextEntry::make('lpw_latest_commented_at')->label('Latest Commented At')->icon('heroicon-s-calendar')->columnSpan(2)->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['latest_commented_at'] ?? ($record->customer->latest_commented_at ?? 'N/A')),
				TextEntry::make('lpw_company_name')->label('Company Name')->icon('heroicon-s-building-office')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['company_name'] ?? ($record->customer->company_name ?? 'N/A')),
				TextEntry::make('lpw_customer_remarks')->label('Customer Remarks')->icon('heroicon-s-chat-bubble-bottom-center-text')->columnSpan(2)->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['customer_remarks'] ?? ($record->customer->customer_remarks ?? 'N/A')),
			]);
	}

	public static function forSidePanel(): Section
	{
		return Section::make(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['firstname'] ?? ($record->customer->firstname ?? 'Contact Details'))
			->icon('iconsax-bul-profile-circle')
			->lazy()
			->schema([
				TextEntry::make('lpw_email')->label('Email')->copyable()->copyMessage('Email copied')->icon('heroicon-s-envelope')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['email'] ?? ($record->customer->email ?? 'N/A')),
				TextEntry::make('lpw_mobile')->label('Mobile')->icon('heroicon-s-phone')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['mobile'] ?? ($record->customer->mobile ?? 'N/A')),
				TextEntry::make('lpw_address')->label('Address')->icon('heroicon-s-map-pin')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['address'] ?? ($record->customer->address ?? 'N/A')),
				TextEntry::make('lpw_id')->label('ID')->icon('heroicon-s-identification')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['id'] ?? ($record->customer->id ?? 'N/A')),
				TextEntry::make('lpw_reg_date')->label('Registration Date')->icon('heroicon-s-calendar')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['reg_date'] ?? ($record->customer->reg_date ?? 'N/A')),
				TextEntry::make('lpw_source')->label('Source')->icon('heroicon-s-arrow-path-rounded-square')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['source'] ?? ($record->customer->source ?? 'N/A')),
				TextEntry::make('lpw_category')->label('Category')->icon('heroicon-s-tag')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['category'] ?? ($record->customer->category ?? 'N/A')),
				TextEntry::make('lpw_payment_status')->label('Payment Status')->badge()->icon('heroicon-s-credit-card')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['payment_status'] ?? ($record->customer->payment_status ?? 'N/A')),
				TextEntry::make('lpw_payment_exp_date')->label('Payment Expiry Date')->icon('heroicon-s-calendar')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['payment_exp_date'] ?? ($record->customer->payment_exp_date ?? 'N/A')),
				TextEntry::make('lpw_latest_commented_at')->label('Latest Commented At')->icon('heroicon-s-calendar')->getStateUsing(fn($record) => LpwData::getLpwUserDetailsForRecord($record)['latest_commented_at'] ?? ($record->customer->latest_commented_at ?? 'N/A')),
			]);
	}
}
