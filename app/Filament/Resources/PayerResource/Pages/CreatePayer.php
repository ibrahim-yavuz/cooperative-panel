<?php

namespace App\Filament\Resources\PayerResource\Pages;

use App\Filament\Resources\PayerResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayer extends CreateRecord
{
    protected static string $resource = PayerResource::class;
}
