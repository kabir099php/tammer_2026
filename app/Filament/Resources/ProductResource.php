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
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    /**
     * Define the form structure for creating and editing products.
     */
    public static function form(Form $form): Form
    {
        $user = Auth::user();
        // Check if the user is a vendor (adjust 'vendor' string as per your roles setup)
        $isVendor = $user && method_exists($user, 'hasRole') && $user->hasRole('vendor');
        
        // Assuming the vendor is linked to one store via a 'store_id' column on the User model
        $vendorStoreId = $isVendor ? $user->store_id : null; 

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
               
                // --- VENDOR LOGIC: Store ID Hidden and Default ---
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
                    }) 
                    ->default($vendorStoreId) // Set the default value for vendors
                    ->hidden($isVendor)       // Hide the field for vendors
                    // Ensure the value is saved even when hidden
                    ->dehydrated($isVendor || ! $isVendor),
            
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
                
                // --- VENDOR LOGIC: Category Filtering ---
                Forms\Components\Select::make('category_id')
                    ->relationship(
                        'category', 
                        'name',
                        // Filter categories to only those created by the logged-in user if they are a vendor
                        fn (Builder $query) => $query->when(
                            $isVendor,
                            fn (Builder $q) => $q->where('user_id', $user->id)
                        )
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
                
                // --- MODIFIED STATUS FIELD: Select Dropdown ---
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->label('Status'),
               
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

    /**
     * Define the table structure.
     */
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
                // --- MASS IMPORT ACTION ---
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
                            ->storeFiles(false),
                    ])
                    ->action(function (array $data) {
                        
                        $uploadedFile = $data['import_file']; 
                        $filePath = $uploadedFile->getRealPath();
                        
                        $importedCount = 0;
                        $updatedCount = 0;

                        DB::beginTransaction();

                        try {
                            (new FastExcel())->import($filePath, function ($line) use (&$importedCount, &$updatedCount) {
                                
                                $productId = trim($line['ID'] ?? '');

                                // 1. Find or Create Related Models by Name
                                $store = Store::firstOrCreate(['name' => $line['Store']]);
                                $branch = Branch::where('store_id', $store->id)->firstOrCreate(['name' => $line['Branch'], 'store_id' => $store->id]);
                                $category = Category::firstOrCreate(['name' => $line['Category']]);

                                // 2. Prepare Product Data
                                $productData = [
                                    'name' => $line['English Name'] ?? null,
                                    'name_ar' => $line['Arabic Name'] ?? null,
                                    'store_id' => $store->id,
                                    'branch_id' => $branch->id,
                                    'category_id' => $category->id,
                                    'barcode' => $line['Barcode'] ?? null,
                                    'price' => floatval($line['Price'] ?? 0),
                                    'stock' => intval($line['Stock'] ?? 0),
                                    'status' => 1,
                                ];

                                // 3. Logic for Update or Create
                                if (!empty($productId)) {
                                    $product = Product::where('id', $productId)->first();
                                    if ($product) {
                                        $product->update($productData);
                                        $updatedCount++;
                                        return;
                                    }
                                }
                                
                                Product::create($productData);
                                $importedCount++;
                            });

                            DB::commit();

                            Notification::make()
                                ->title('Products Imported Successfully')
                                ->body("Created **{$importedCount}** new products and updated **{$updatedCount}** existing products.")
                                ->success()
                                ->duration(10000)
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Import Failed')
                                ->body('An error occurred during import. Error: ' . $e->getMessage())
                                ->danger()
                                ->duration(10000)
                                ->send();
                        }
                    }),
                // --- END IMPORT ACTION ---

                // Existing EXPORT ACTION
                Action::make('export_products')
                    ->label('Export Products (Direct XL)') 
                    ->icon('heroicon-o-document-arrow-down') 
                    ->action(function (HasTable $livewire) { 
                        
                        $query = $livewire->getFilteredTableQuery();

                        $data = $query->with(['store', 'branch', 'category'])->get();

                        $fileName = 'products-' . now()->format('Ymd_His') . '.xlsx';
                        
                        return (new FastExcel($data))->download(
                            $fileName, 
                            function ($product) {
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

    /**
     * Filters the base query for the table based on the vendor role.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Check if the currently authenticated user has the 'vendor' role
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('vendor')) {
            // Apply a constraint to only show products whose category belongs to this user
            $query->whereHas('category', function (Builder $categoryQuery) use ($user) {
                $categoryQuery->where('user_id', $user->id);
            });
        }

        return $query;
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