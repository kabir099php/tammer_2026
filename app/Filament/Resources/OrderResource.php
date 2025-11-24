<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // Added for query scoping
use Illuminate\Support\Facades\Auth; // Added for checking the logged-in user
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Facades\DB;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    // --- FORM SCHEMA ---
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Left Column: Main Order Details
                Fieldset::make('Customer & Status')
                    ->columns(2)
                    ->schema([
                        // Customer Association
                       
                        
                      

                        // Payment Status
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'refunded' => 'Refunded',
                                'partial' => 'Partially Paid',
                            ])
                            ->default('unpaid')
                            ->required(),

                        // Generic Status FK
                        Select::make('status')
                            ->label('Generic Status FK')
                            ->relationship('generalStatus', 'name')
                            ->nullable(),
                    ]),

                // Right Column: Financial Details
                Fieldset::make('Financial Details')
                    ->columns(3)
                    ->schema([
                        // *** CURRENCY: SAR ***
                        TextInput::make('order_amount')
                            ->label('Subtotal Amount')
                            ->numeric()
                            ->prefix('SAR') 
                            ->required(),

                        // *** CURRENCY: SAR ***
                        TextInput::make('total_tax_amount')
                            ->label('Total Tax Amount')
                            ->numeric()
                            ->prefix('SAR')
                            ->default(0.00)
                            ->required(),
                            
                        // *** CURRENCY: SAR ***
                        TextInput::make('partially_paid_amount')
                            ->label('Partially Paid Amount')
                            ->numeric()
                            ->prefix('SAR')
                            ->default(0.00),

                        // VAT / Tax Details
                        TextInput::make('tax_percentage')
                            ->label('Tax %')
                            ->numeric()
                            ->suffix('%')
                            ->nullable(),

                        TextInput::make('vatpr')
                            ->label('VAT %')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),

                        // *** CURRENCY: SAR ***
                        TextInput::make('vatamt')
                            ->label('VAT Amount')
                            ->numeric()
                            ->prefix('SAR')
                            ->default(0),
                    ]),

                // Payment Details
                Fieldset::make('Payment Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->maxLength(30)
                            ->nullable(),
                            
                        TextInput::make('payment_type')
                            ->label('Payment Type')
                            ->maxLength(225)
                            ->nullable(),

                        TextInput::make('payement_gateway_status')
                            ->label('Gateway Status')
                            ->maxLength(225)
                            ->default('Pending'),

                        Textarea::make('payment_token')
                            ->label('Payment Token/Reference')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                
                // Meta Details
                Fieldset::make('Metadata')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_guest')
                            ->label('Is Guest Order?')
                            ->default(false),
                            
                        DateTimePicker::make('created_at')
                            ->label('Order Date')
                            ->readOnly(),
                            
                        DateTimePicker::make('updated_at')
                            ->label('Last Updated')
                            ->readOnly(),
                    ]),
            ]);
    }

    // --- TABLE SCHEMA ---
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('ID'),
                
                // Customer/Store
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Guest User'),
                
                TextColumn::make('store.name')
                    ->label('Store')
                    ->sortable()
                    ->searchable(), 
                
                // Amounts
                TextColumn::make('order_amount')
                    ->label('Subtotal')
                    ->money('SAR') 
                    ->sortable(),
                    
                TextColumn::make('total_tax_amount')
                    ->label('Tax')
                    ->money('SAR')
                    ->sortable(),

             
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        default => 'secondary',
                    })
                    ->sortable(),
                    
                // Payment Method
                TextColumn::make('payment_method')
                    ->searchable()
                    ->toggleable(),
                    
                // Dates
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),
                    
                IconColumn::make('is_guest')
                    ->label('Guest?')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_status')
                    ->options([
                        'pending' => 'Pending',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->label('Filter by Fulfillment Status'),

                SelectFilter::make('payment_status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                    ])
                    ->label('Filter by Payment Status'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // --- SCOPING LOGIC: Filter Orders by Vendor's Store ID ---
    public static function getEloquentQuery(): Builder
    {
        // Get the base query
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $query->where('id', null); // No data for unauthenticated users
        }
        
        // 1. Check if the logged-in user has the 'vendor' role
        // *** YOU MUST REPLACE 'vendor' WITH YOUR ACTUAL ROLE CHECK METHOD (e.g., $user->isVendor()) ***
        $isVendor = Auth::user()?->hasRole('vendor');

        if ($isVendor) {
            // 2. Get the IDs of all stores owned by the current user
            // This assumes the User model has a 'stores' relationship where User hasMany Store.
            // i.e., Store::where('user_id', $user->id)
            $vendorStoreIds = $user->stores()->pluck('id');
            
            if ($vendorStoreIds->isEmpty()) {
                 // If the user is a vendor but doesn't own any stores, show no orders.
                 return $query->where('id', null);
            }
            
            // 3. Filter the orders to include only those belonging to the vendor's stores
            return $query->whereIn('store_id', $vendorStoreIds);
        }
        
        // If the user is an Admin (or any non-vendor role allowed to see everything)
        // *** YOU MUST REPLACE 'admin' WITH YOUR ACTUAL ADMIN ROLE CHECK ***
        $isAdmin = $user->hasRole('admin'); 


        // Default: If they are logged in but not an Admin or a Vendor, show nothing
        return  $query;
    }

    // --- RELATIONSHIPS ---
    public static function getRelations(): array
    {
        return [
            // RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    // --- PAGES ---
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            //'create' => Pages\CreateOrder::route('/create'),
           // 'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}