<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; 

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; 

    protected static ?string $navigationGroup = 'User Management'; 
    
    // =========================================================================
    // PERMISSION CHECKS: CONTROL RESOURCE VISIBILITY AND ACCESS
    // =========================================================================

    // 1. Check if the current user can see the User Resource in the navigation
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_users');
    }

    // 2. Check if the current user can create a user
    public static function canCreate(): bool
    {
        return auth()->user()->can('create_users');
    }

    // 3. Check if the current user can delete a single user
    // public static function canDelete(User $record): bool
    // {
    //     // Add additional logic here, e.g., prevent deleting super admins or self
    //     return auth()->user()->can('delete_users');
    // }

    // 4. Check if the current user can update a single user
    public static function canUpdate(User $record): bool
    {
        // You might want to prevent a user from editing themselves or a higher-level admin
        return auth()->user()->can('update_users');
    }

    // 5. Check if the current user can delete multiple users
    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete_bulk_users');
    }

    // =========================================================================
    // END PERMISSION CHECKS
    // =========================================================================

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true), 
                
                // Spatie Roles Selection Field
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name') 
                    ->multiple()
                    ->preload()
                    ->options(Role::pluck('name', 'id')->toArray())
                    ->required()
                    // IMPORTANT: Only allow users with update_users to modify roles
                    ->disabled(!auth()->user()->can('update_users')), 

                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->default(now())
                    ->hiddenOn('create'), 

                // Password Handling (hashes on create, only updates if field is filled on edit)
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)) 
                    ->dehydrated(fn (?string $state): bool => filled($state)) 
                    ->required(fn (string $operation): bool => $operation === 'create') 
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                // Display assigned roles
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->label('Roles')
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
                // Filter users by assigned role
                Tables\Filters\SelectFilter::make('Role')
                    ->relationship('roles', 'name')
                    ->preload(),
            ])
            ->actions([
                // The visibility of this action is handled by the static canUpdate method
                Tables\Actions\EditAction::make(), 
                
                // The visibility of this action is handled by the static canDelete method
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // The visibility of this action is handled by the static canDeleteAny method
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}