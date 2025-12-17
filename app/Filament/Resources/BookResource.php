<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use App\Models\author_model as Author;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Book Management';

    public static ?string $label = 'Book';

    protected static ?int $sort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),

                        Forms\Components\Select::make('author_id')
                            ->label('Author')
                            ->relationship('authorModel', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('biography')
                                    ->rows(3),
                                Forms\Components\FileUpload::make('thumbnail')
                                    ->image()
                                    ->directory('authors')
                                    ->visibility('public'),
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('author')
                            ->label('Author Name (Legacy)')
                            ->maxLength(255)
                            ->helperText('Legacy field - use Author dropdown above'),

                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpan('full'),

                        Forms\Components\Select::make('language')
                            ->options([
                                'english' => 'English',
                                'swahili' => 'Swahili',
                            ])
                            ->required()
                            ->default('english'),

                        Forms\Components\TextInput::make('level')
                            ->maxLength(255)
                            ->helperText('e.g., Primary, Secondary, Intermediate'),

                        Forms\Components\TextInput::make('price')
                            ->label('Price (TZS)')
                            ->numeric()
                            ->required()
                            ->default(0),

                        Forms\Components\TextInput::make('original_price')
                            ->label('Original Price (TZS)')
                            ->numeric()
                            ->helperText('For showing discounts'),

                        Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1),

                        Forms\Components\TextInput::make('review_count')
                            ->label('Review Count')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),

                        Forms\Components\Section::make('Donation Settings')
                            ->description('Enable this book for donations. When enabled, users can donate by purchasing this book.')
                            ->schema([
                                Forms\Components\Toggle::make('is_donation_enabled')
                                    ->label('Enable Donation')
                                    ->helperText('When enabled, users will see a "Donate" button and can contribute by purchasing this book. The purchase will be marked as a donation.')
                                    ->default(false)
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->reactive()
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('donation_min_amount')
                                    ->label('Minimum Donation Amount (TZS)')
                                    ->helperText('Minimum amount users can donate. If empty or 0, users can donate any amount equal to or above the book price.')
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn ($get) => $get('is_donation_enabled'))
                                    ->columnSpan('full'),
                            ])
                            ->collapsible()
                            ->collapsed(false)
                            ->columnSpan('full'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Book Cover')
                            ->schema([
                                Forms\Components\FileUpload::make('image_url')
                                    ->label('Cover Image')
                                    ->image()
                                    ->directory('books/covers')
                                    ->visibility('public')
                                    ->imagePreviewHeight('300')
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])
                                    ->helperText('Upload book cover image (Max 5MB)')
                                    ->columnSpan('full'),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Forms\Components\Section::make('Book File')
                            ->description('Upload the book file. File type, size, and download URL will be automatically set.')
                            ->schema([
                                Forms\Components\FileUpload::make('file_name')
                                    ->label('Book File (PDF/EPUB)')
                                    ->directory('books/files')
                                    ->visibility('private')
                                    ->acceptedFileTypes(['application/pdf', 'application/epub+zip'])
                                    ->maxSize(102400) // 100MB
                                    ->helperText('Upload the book file (PDF or EPUB, Max 100MB). File type, size, and download URL will be automatically detected.')
                                    ->columnSpan('full')
                                    ->required(),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Forms\Components\Section::make('Download Settings')
                            ->schema([
                                Forms\Components\TextInput::make('max_downloads_default')
                                    ->label('Default Max Downloads')
                                    ->numeric()
                                    ->default(5)
                                    ->helperText('Default max downloads per purchase (can be overridden per purchase)')
                                    ->columnSpan('full'),
                            ])
                            ->collapsible()
                            ->collapsed(true),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Cover')
                    ->size(50)
                    ->defaultImageUrl(url('/images/default-book.png')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('authorModel.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),

                Tables\Columns\BadgeColumn::make('language')
                    ->colors([
                        'primary' => 'english',
                        'success' => 'swahili',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('TZS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchases_count')
                    ->label('Sales')
                    ->counts('purchases')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('TZS')
                    ->sortable()
                    ->getStateUsing(function (Book $record): float {
                        return $record->total_revenue ?? 0;
                    }),

                BadgeColumn::make('is_donation_enabled')
                    ->label('Donation')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Enabled' : 'Disabled'),

                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('language')
                    ->options([
                        'english' => 'English',
                        'swahili' => 'Swahili',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\TernaryFilter::make('is_donation_enabled')
                    ->label('Donation Enabled')
                    ->placeholder('All')
                    ->trueLabel('Donation enabled')
                    ->falseLabel('Donation disabled'),

                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Author')
                    ->relationship('authorModel', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        $relations = [
            RelationManagers\PurchasesRelationManager::class,
        ];
        
        // Only add DownloadsRelationManager if the table exists
        if (Schema::hasTable('tbl_book_downloads')) {
            $relations[] = RelationManagers\DownloadsRelationManager::class;
        }
        
        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('purchases');
    }
}

