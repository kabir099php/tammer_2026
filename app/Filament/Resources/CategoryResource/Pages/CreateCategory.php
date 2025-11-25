<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $user = auth()->user();

    // Assign logged-in user's store_id and user_id
    $data['user_id'] = $user->id;
    $store = \App\Models\Store::where('user_id', $user->id)->first();

    $data['store_id'] = $store?->id; 

    return $data;
}
}
