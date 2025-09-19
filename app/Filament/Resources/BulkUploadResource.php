<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\BulkUploadResource\Pages\ListBulkUploads;
use App\Filament\Resources\BulkUploadResource\Pages\CreateBulkUpload;
use App\Filament\Resources\BulkUploadResource\Pages\EditBulkUpload;
use App\Filament\Resources\BulkUploadResource\Pages;
use App\Filament\Resources\BulkUploadResource\RelationManagers;
use App\Models\BulkUpload;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BulkUploadResource extends Resource
{
    protected static ?string $model = BulkUpload::class;

    protected static string | \BackedEnum | null $navigationIcon = 'ri-upload-cloud-2-fill';

    protected static ?string $navigationLabel = 'Bulk Upload';

    protected static string | \UnitEnum | null $navigationGroup = 'Customers';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBulkUploads::route('/'),
            'create' => CreateBulkUpload::route('/create'),
            'edit' => EditBulkUpload::route('/{record}/edit'),
        ];
    }
}
