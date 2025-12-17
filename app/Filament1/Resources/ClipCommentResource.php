<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClipCommentResource\Pages;
use App\Filament\Resources\ClipCommentResource\RelationManagers;
use App\Filament\Resources\ClipCommentResource\RelationManagers\ClipReplyRelationManager;
use App\Models\clip_comments_model as ClipComment;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClipCommentResource extends Resource
{
    protected static ?string $model = ClipComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static ?string $label = 'Comments';

    protected static ?int $sort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')->required(),
                Forms\Components\Textarea::make('content')->required(),
                Forms\Components\Toggle::make('visibility'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
           ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Student Name')->searchable(),
                Tables\Columns\TextColumn::make('video.name')->label('Course Name')->searchable(),    
                Tables\Columns\TextColumn::make('content')->limit(50)->searchable(),
                Tables\Columns\BooleanColumn::make('visibility')->label('Visible'),
                Tables\Columns\TextColumn::make('user.region')->label('Location')->searchable(),      
                Tables\Columns\TextColumn::make('created_at')->label('Viewed At')->searchable()
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
            ClipReplyRelationManager::class,
        ];
    }

      protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('user','video');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClipComments::route('/'),
            'create' => Pages\CreateClipComment::route('/create'),
            'edit' => Pages\EditClipComment::route('/{record}/edit'),
        ];
    }    
}
