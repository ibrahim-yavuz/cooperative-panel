<?php

namespace App\Filament\Resources\PayerResource\Pages;

use App\Filament\Resources\PayerResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayer extends EditRecord
{
    protected static string $resource = PayerResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
