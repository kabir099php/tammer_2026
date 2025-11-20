<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Response; 
use App\Filament\Exports\ProductExporter; 
use Filament\Tables\Actions\Action; 
use Illuminate\Database\Eloquent\Builder; 
use Filament\Forms\Get; 
use Filament\Tables\Contracts\HasTable; 

use Rap2hpoutre\FastExcel\FastExcel; 

use App\Models\Store;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        // ... (existing form method remains unchanged)
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label("English Name")
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('name_ar')
                    ->maxLength(255)
                    ->label("Arabic Name")
                    ->default(null),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
               Forms\Components\Select::make('store_id')
                ->relationship(
                    'store', 
                    'name',   
                )
                ->label('Store')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function (Forms\Set $set) {
                    $set('branch_id', null); 
                }), 
            Forms\Components\Select::make('branch_id')
                ->relationship(
                    'branch', 
                    'name',
                    
                    fn (Builder $query, Forms\Get $get) => $query->when(
                        $get('store_id'),
                        fn (Builder $query, $storeId) => $query->where('store_id', $storeId)
                    )
                )
                ->label('Branch')
                ->searchable()
                ->preload()
                ->required()
                ->nullable(),
                Forms\Components\Select::make('categroy_id')
                ->relationship(
                    'category', 
                    'name',   
                    
                )
                ->label('Category')
                ->searchable()
                ->preload()
                ->required(),
                Forms\Components\TextInput::make('barcode')
                    ->maxLength(225)
                    ->default(null),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.00)
                    ->prefix('SAR'),
                
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
               
                
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('maximum_cart_quantity')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('images')
                    ->columnSpanFull(),
                
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
            Tables\Columns\TextColumn::make('store.name')
                ->label('Store') 
                ->sortable()
                ->searchable(), 
            Tables\Columns\TextColumn::make('category.name')
                ->label('Category') 
                ->sortable()
                ->searchable(), 
                Tables\Columns\TextColumn::make('branch.name')
                ->label('Branch') 
                ->sortable()
                ->searchable(), 
                
                Tables\Columns\TextColumn::make('barcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('SAR', true)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
               
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
   
                ]),
            ])
            ->headerActions([
                // --- NEW IMPORT ACTION ---
                Action::make('import_products')
                    ->label('Import Products (Mass Add/Update)')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->modalIcon('heroicon-o-document-arrow-up')
                    ->modalHeading('Import Products from Spreadsheet')
                    ->form([
                        Forms\Components\FileUpload::make('import_file')
                            ->label('Upload .xlsx or .csv file')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                            ->required()
                            ->storeFiles(false), // Process the temporary file path directly
                    ])
                    ->action(function (array $data) {
                        
                        // FIX: Access the file object directly without [0]
                        $uploadedFile = $data['import_file']; 
                        $filePath = $uploadedFile->getRealPath();
                        
                        $importedCount = 0;
                        $updatedCount = 0;

                        DB::beginTransaction();

                        try {
                            // Read the Excel/CSV file using FastExcel
                            (new FastExcel())->import($filePath, function ($line) use (&$importedCount, &$updatedCount) {
                                
                                // Clean up the ID field
                                $productId = trim($line['ID'] ?? '');

                                // 1. Find or Create Related Models by Name
                                // This ensures the store, branch, and category exist before linking
                                $store = Store::firstOrCreate(['name' => $line['Store']]);
                                $branch = Branch::where('store_id', $store->id)->firstOrCreate(['name' => $line['Branch'], 'store_id' => $store->id]);
                                $category = Category::firstOrCreate(['name' => $line['Category']]);

                                // 2. Prepare Product Data (The rest of the fields to update/create)
                                $productData = [
                                    'name' => $line['English Name'] ?? null,
                                    'name_ar' => $line['Arabic Name'] ?? null,
                                    'store_id' => $store->id,
                                    'branch_id' => $branch->id,
                                    'category_id' => $category->id,
                                    'barcode' => $line['Barcode'] ?? null,
                                    'price' => floatval($line['Price'] ?? 0),
                                    'stock' => intval($line['Stock'] ?? 0),
                                    'status' => 1, // Default status
                                ];

                                // 3. Logic for Update or Create
                                if (!empty($productId)) {
                                    // Attempt to find and update by ID
                                    $product = Product::where('id', $productId)->first();
                                    if ($product) {
                                        $product->update($productData);
                                        $updatedCount++;
                                        return; // Move to the next row
                                    }
                                }
                                
                                // Create new product if ID was empty or product wasn't found
                                Product::create($productData);
                                $importedCount++;
                            });

                            DB::commit();

                            // Send success notification
                            Notification::make()
                                ->title('Products Imported Successfully')
                                ->body("Created **{$importedCount}** new products and updated **{$updatedCount}** existing products.")
                                ->success()
                                ->duration(10000)
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();

                            // Send error notification
                            Notification::make()
                                ->title('Import Failed')
                                ->body('An error occurred during import. Check the file format. Error: ' . $e->getMessage())
                                ->danger()
                                ->duration(10000)
                                ->send();
                        }
                    }),
                // --- END NEW IMPORT ACTION ---

                // Existing EXPORT ACTION
                Action::make('export_products')
                    ->label('Export Products (Direct XL)') 
                    ->icon('heroicon-o-document-arrow-down') 
                    ->action(function (HasTable $livewire) { 
                        
                        // 1. Get the filtered query
                        $query = $livewire->getFilteredTableQuery();

                        // 2. FIX: Use ->get() to return a standard Collection instead of ->cursor()
                        $data = $query->with(['store', 'branch', 'category'])->get();

                        $fileName = 'products-' . now()->format('Ymd_His') . '.xlsx';
                        
                        // 3. Use FastExcel to export the standard Collection
                        return (new FastExcel($data))->download(
                            $fileName, 
                            function ($product) {
                                // Define the column output mapping
                                return [
                                    'ID' => $product->id,
                                    'English Name' => $product->name,
                                    'Arabic Name' => $product->name_ar,
                                    'Store' => $product->store->name ?? '',
                                    'Branch' => $product->branch->name ?? '',
                                    'Category' => $product->category->name ?? '',
                                    'Barcode' => $product->barcode,
                                    'Price' => $product->price,
                                    'Stock' => $product->stock,
                                ];
                            }
                        );
                    })
                    ->color('primary') 
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}