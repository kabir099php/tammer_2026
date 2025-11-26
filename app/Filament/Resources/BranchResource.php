<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

        public static function canViewAny(): bool
    {
        return auth()->user()->can('branches');
    }

    public static function form(Form $form): Form
    {
        // 1. Check the logged-in user's ID
        $userId = Auth::id();
        // 2. Check if the logged-in user is a vendor (assuming you have a 'hasRole' method)
        $isVendor = Auth::user()?->hasRole('vendor');
        return $form
            ->schema([
            // Select to link to the parent store
            Select::make('store_id')
                ->relationship(
                    'store', 
                    'name', 
                    // 3. Conditionally modify the relationship query
                    fn (Builder $query) => $isVendor ? $query->where('user_id', $userId) : $query
                ) 
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('city')
                
                ->maxLength(100),

            Forms\Components\TextInput::make('address')
                
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    Tables\Columns\TextColumn::make('store.name') // Display the store's name
                        ->label(' Store')
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('name')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('city')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
