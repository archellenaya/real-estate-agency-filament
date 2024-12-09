<?php

namespace App\Filament\Resources\BuyerTypeResource\Pages;

use App\Filament\Resources\BuyerTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuyerType extends EditRecord
{
    protected static string $resource = BuyerTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
