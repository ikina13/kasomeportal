<?php

namespace App\Filament\Resources\ClipCommentResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClipReplyRelationManager extends RelationManager
{
    protected static string $relationship = 'ClipReply';

    protected static ?string $recordTitleAttribute = 'content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('user_id')->label('User ID')
                    ->required()
                    ->maxLength(255),
                 Forms\Components\TextInput::make('clip_id')->label('Clip ID')
                    ->required()
                    ->maxLength(255),    
                Forms\Components\TextInput::make('content')
                    ->required()
                    ->maxLength(255),
                 ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Student Name')->searchable(),               
                Tables\Columns\TextColumn::make('content')->limit(50)->searchable(),
                Tables\Columns\BooleanColumn::make('visibility')->label('Visible'),
                Tables\Columns\TextColumn::make('user.region')->label('Location')->searchable(),  
                Tables\Columns\TextColumn::make('created_at')->label('Viewed At')->searchable()
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
}
