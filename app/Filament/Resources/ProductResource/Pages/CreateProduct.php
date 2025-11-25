<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $isVendor = $user && method_exists($user, 'hasRole') && $user->hasRole('vendor');

        if ($isVendor) {
            // Check if the user has a store_id and inject it into the data array
            if (!isset($data['store_id']) ) {
                $store = \App\Models\Store::where('user_id', $user->id)->first();

                $data['store_id'] = $store?->id; 
            }
        }

        return $data;
    }
}
