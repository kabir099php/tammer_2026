<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoleResource extends Resource
{
    // Point the resource to the Spatie Role model
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    // Optional: Group the navigation item under a header
    protected static ?string $navigationGroup = 'Settings & Permissions';

    // Optional: Set a specific sort order
    protected static ?int $navigationSort = 1;

    /**
     * Define the form structure for creating and editing roles.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        // 1. Role Name (Required)
                        Forms\Components\TextInput::make('name')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        
                        // 2. Guard Name (Default to 'web')
                        Forms\Components\Select::make('guard_name')
                            ->options([
                                'web' => 'Web',
                                'api' => 'API',
                            ])
                            ->default('web')
                            ->required(),

                        // 3. Permissions Selector (Multi-select)
                        // Fetching all permissions from the database
                        Forms\Components\Select::make('permissions')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Permissions'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Define the table columns and actions.
     */
    public static function table(Table $table): Table
    {
        $currentUserId = auth()->id();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions Count')
                    ->color('success'),
            ])
            ->filters([
                // Filter by guard name
                Tables\Filters\SelectFilter::make('guard_name')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        

        if ($user && method_exists($user, 'hasRole') && !$user->hasRole('super_admin')) {
        
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * Define the relationships for the resource.
     */
    public static function getRelations(): array
    {
        return [
            // Add a relation manager if you want to view/edit users associated with this role
        ];
    }

    /**
     * Define the pages used by the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    /**
     * Optional: Restrict visibility based on user role (e.g., only Super Admins see this)
     */
        public static function canViewAny(): bool
    {
        return auth()->user()->can('roles');
    }
}