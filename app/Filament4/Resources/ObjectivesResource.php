<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObjectivesResource\Pages;
use App\Filament\Resources\ObjectivesResource\RelationManagers;
use App\Models\module_objective_model as Objectives;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Models\module_objective_model as Objective;

class ObjectivesResource extends Resource
{
    protected static ?string $model = Objectives::class;

    public static ?string $label = 'Objectives';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Course Management';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Objective Name')
                    ->required(),
                Forms\Components\TextInput::make('desc')
                    ->label('Objective Description')
                    ->required(),
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
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListObjectives::route('/'),
            'create' => Pages\CreateObjectives::route('/create'),
            'view' => Pages\ViewObjectives::route('/{record}'),
            'edit' => Pages\EditObjectives::route('/{record}/edit'),
        ];
    }
}
