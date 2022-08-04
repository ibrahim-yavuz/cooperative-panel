<?php

namespace App\Filament\Resources\PayRecordResource\Pages;

use App\Filament\Resources\PayRecordResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayRecords extends ListRecords
{
    protected static string $resource = PayRecordResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
