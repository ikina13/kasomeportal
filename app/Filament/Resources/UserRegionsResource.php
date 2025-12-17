<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRegionsResource\Pages;
use App\Filament\Resources\UserRegionsResource\RelationManagers;
use App\Models\user_regions as UserRegions;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserRegionsResource extends Resource
{
    protected static ?string $model = UserRegions::class;

    protected static bool $shouldRegisterNavigation = false;


    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserRegions::route('/'),
            'create' => Pages\CreateUserRegions::route('/create'),
            'edit' => Pages\EditUserRegions::route('/{record}/edit'),
        ];
    }
}
