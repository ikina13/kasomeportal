<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppUserResource\Pages;
use App\Filament\Resources\AppUserResource\RelationManagers;
use App\Models\app_user as AppUser;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppUserResource extends Resource
{
    protected static ?string $model = AppUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $sort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Forms\Components\Card::make()
        ->schema([
            Forms\Components\TextInput::make('status')
                ->required()       
        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable()->sortable()->default(0)->size('sm'),
                Tables\Columns\TextColumn::make('sex')->sortable()->default(0)->size('sm'),
                Tables\Columns\TextColumn::make('region')->sortable()->default(0)->size('sm'),
                Tables\Columns\TextColumn::make('district')->sortable()->default(0)->size('sm'),
                Tables\Columns\TextColumn::make('status')->sortable()->default(0)->size('sm'),
                Tables\Columns\TextColumn::make('created_at')->sortable()->default(0)->size('sm'),
            ])
             ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('user_region');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppUsers::route('/'),
            'create' => Pages\CreateAppUser::route('/create'),
            'edit' => Pages\EditAppUser::route('/{record}/edit'),
        ];
    }
}
