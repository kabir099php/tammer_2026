<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    if (auth()->user()->hasRole('vendor')) {
        $data['user_id'] = auth()->id();
    }

    return $data;
}
}
