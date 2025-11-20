<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;
    
    // The make() method is removed because Exporter is instantiated by the framework.

    /**
     * FIX: Temporarily define getColumns() as static to satisfy PHP inheritance 
     * based on your current environment's version of Filament\Actions\Exports\Exporter.
     */
    public static function getColumns(): array
    {
        
        return [
           ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('name_ar'),
            //ExportColumn::make('store.name'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}