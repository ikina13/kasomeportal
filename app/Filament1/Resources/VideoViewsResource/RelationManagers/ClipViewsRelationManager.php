<?php

namespace App\Filament\Resources\VideoViewsResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClipViewsRelationManager extends RelationManager
{
    protected static string $relationship = 'ClipViews';

    protected static ?string $recordTitleAttribute = 'created_at';

    public static function form(Form $form): Form
    {
        return $form
              ->schema([
                Forms\Components\TextInput::make('user.name')->required(),
                Forms\Components\TextInput::make('video.name')->required(),
                Forms\Components\TextInput::make('user.region')->required(),
                Forms\Components\TextInput::make('created_at')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                 Tables\Columns\TextColumn::make('user.name')->label('Student Name')->searchable(),
                 Tables\Columns\TextColumn::make('video.name')->label('Course Name')->searchable(),
                 Tables\Columns\TextColumn::make('user.region')->label('Location')->searchable(),
                 Tables\Columns\TextColumn::make('created_at')->label('Viewed At')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]) ->defaultSort('created_at', 'desc');
    }  
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('user','video');
    }  
}
