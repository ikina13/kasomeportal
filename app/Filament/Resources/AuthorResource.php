<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorResource\Pages;
use App\Filament\Resources\AuthorResource\RelationManagers;
use App\Models\author_model as Author;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Course Management';

    public static ?string $label = 'Author';

    protected static ?int $sort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Author Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),
                        
                        Forms\Components\Textarea::make('biography')
                            ->label('Profile/Description')
                            ->rows(6)
                            ->columnSpan('full')
                            ->helperText('Write a brief biography or description about the author. HTML tags will be displayed as text.'),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Author Photo')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('Author Photo')
                                    ->image()
                                    ->directory('authors')
                                    ->visibility('public')
                                    ->imagePreviewHeight('200')
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])
                                    ->helperText('Upload the author\'s profile photo (Max 5MB, JPG/PNG/GIF/WEBP)')
                                    ->columnSpan('full')
                            ])
                            ->collapsible()
                            ->collapsed(false),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Photo')
                    ->size(50)
                    ->circular()
                    ->defaultImageUrl(url('/images/default-author.png')),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Author Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('biography')
                    ->label('Biography')
                    ->limit(50)
                    ->wrap()
                    ->html(false)
                    ->formatStateUsing(fn ($state) => $state ? strip_tags($state) : 'No biography'),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label('Courses')
                    ->counts('courses')
                    ->sortable()
                    ->default(0),

                Tables\Columns\TextColumn::make('books_count')
                    ->label('Books')
                    ->counts('books')
                    ->sortable()
                    ->default(0)
                    ->visible(fn () => \Schema::hasColumn('tbl_books', 'author_id')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    protected static function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()
            ->withCount('courses');
        
        // Only count books if author_id column exists
        if (\Schema::hasColumn('tbl_books', 'author_id')) {
            $query->withCount('books');
        }
        
        return $query;
    }
    
    public static function getRelations(): array
    {
        $relations = [
            RelationManagers\CoursesRelationManager::class,
        ];
        
        // Only include BooksRelationManager if author_id column exists
        if (\Schema::hasColumn('tbl_books', 'author_id')) {
            $relations[] = RelationManagers\BooksRelationManager::class;
        }
        
        return $relations;
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }    
}
