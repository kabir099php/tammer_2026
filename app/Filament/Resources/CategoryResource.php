<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Closure;
use Illuminate\Support\Str;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191)
                    ->reactive() 
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('slug', Str::slug($state)); 
                    }),
                Forms\Components\TextInput::make('slug')
                    ->hidden()
                    ->dehydrated(true) // <— REQUIRED TO SAVE
                    ->required()
                    ->maxLength(191)
                    ->unique(ignoreRecord: true)
                    ->default(fn (callable $get) => Str::slug($get('name'))),
                     Forms\Components\Select::make('branch_id')
                ->label('Branch')
                ->relationship('branch', 'name')
                ->searchable()
                ->preload()
                ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
              Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->label('Status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch') // Set a user-friendly label
                    ->searchable()   // Allow searching by branch name
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Active',
                        0 => 'Inactive',
                        default => 'Unknown',
                    })
                    ->colors([
                        'success' => 1, // Color green when status is 1
                        'danger' => 0,  // Color red when status is 0
                    ]),
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
            ]);
    }

    // --- ADD THIS METHOD ---
    public static function getEloquentQuery(): Builder
    {
        // Start with the base query
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->hasRole('vendor')) {
            
             $query->where('user_id', $user->id);
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
