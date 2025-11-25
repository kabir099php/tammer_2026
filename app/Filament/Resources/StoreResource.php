<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Filament\Resources\StoreResource\RelationManagers;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;



class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('users');
    }

    public static function form(Form $form): Form
    {
        $isVendor = Auth::user()?->hasRole('vendor');
        return $form
            ->schema([
               Forms\Components\Select::make('user_id')
                ->relationship(
                    'user', 
                    'name',   
                    fn (Builder $query) => $query->role( 'vendor')
                )
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name}")
                ->label('Store Owner (Vendor)')
                ->searchable()
                ->preload()
                // 2. Hide this field if the user is a vendor
                
                  // HIDE FOR VENDOR
  // hide for vendors
    ->hidden(fn () => Auth::user()?->hasRole('vendor'))

    // VERY IMPORTANT: always generate initial state
    ->default(fn () =>
        Auth::user()?->hasRole('vendor')
            ? Auth::id()
            : null
    )

    // force saving even if hidden
    ->dehydrated(true)

    // set the actual value that will be saved
    ->dehydrateStateUsing(fn ($state) =>
        Auth::user()?->hasRole('vendor')
            ? Auth::id()
            : $state
    )


                ->required(fn () => !$isVendor),
                
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->reactive() 
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('slug', Str::slug($state)); 
                    }),
                
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(100)
                    ->default(null),
                
                // Status Dropdown
                Forms\Components\Select::make('status')
                    ->options([
                        1 => 'Active',
                        0 => 'Not Active',
                    ])
                    ->label('Status')
                    ->required()
                    ->default(1),

                // Slug Hidden
                Forms\Components\TextInput::make('slug')
                    ->maxLength(255)
                    ->default(null)
                    ->hidden(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isVendor = Auth::user()?->hasRole('vendor');
        return $table
            ->columns([
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                
                
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
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
            ->modifyQueryUsing(function (Builder $query) use ($isVendor) {
                if ($isVendor) {
                    $userId = Auth::id();
                    $query->where('user_id', $userId);
                }
            });
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
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}