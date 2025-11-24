<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\Action; // For the custom button
use Filament\Resources\Pages\ListRecords;
use Rap2hpoutre\FastExcel\FastExcel; // For the export logic
use Illuminate\Database\Eloquent\Builder; // For type hinting the query

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Your Create button is currently disabled in OrderResource's getPages, 
            // but if you want to show it, you'd add: Actions\CreateAction::make(),
            
            // ----------------------------------------
            // ⚡️ FASTEXCEL HEADER EXPORT ACTION
            // ----------------------------------------
            Action::make('export_direct_xl')
                ->label('Export Orders (Direct XL)')
                ->color('success') // Use a color, e.g., 'success' or 'warning'
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    /** @var Builder $query */
                    // Get the query, respecting all active filters and scopes (like the Vendor scope)
                    $query = $this->getFilteredTableQuery();

                    // Eager load relationships used in the export to prevent N+1 queries
                    $query->with(['user', 'store']);

                    // Create a PHP Generator to fetch records chunk by chunk for memory efficiency
                    $recordsGenerator = function () use ($query) {
                        // Use cursor() for efficient streaming of results
                        foreach ($query->cursor() as $order) {
                            yield [
                                'ID' => $order->id,
                                'Customer Name' => $order->user?->name ?? 'Guest User',
                                'Store Name' => $order->store?->name ?? 'N/A',
                                'Subtotal (SAR)' => $order->order_amount,
                                'Tax (SAR)' => $order->total_tax_amount,
                                'Payment Status' => ucfirst($order->payment_status),
                                // Use null-safe formatting for created_at
                                'Order Date' => $order->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                            ];
                        }
                    };

                    $fileName = 'orders-' . now()->format('Y-m-d-His') . '.xlsx';
                    
                    // Use FastExcel with the generator to download the file
                    return (new FastExcel($recordsGenerator()))->download($fileName);
                }),
        ];
    }
}