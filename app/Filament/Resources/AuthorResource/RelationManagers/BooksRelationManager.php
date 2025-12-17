<?php

namespace App\Filament\Resources\AuthorResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Book;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Books';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->rows(3),

                Forms\Components\Select::make('language')
                    ->options([
                        'english' => 'English',
                        'swahili' => 'Swahili',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Price (TZS)')
                    ->numeric()
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Cover')
                    ->size(40),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                BadgeColumn::make('language')
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
                    ->getStateUsing(function (Book $record): float {
                        return $record->total_revenue ?? 0;
                    }),

                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('language')
                    ->options([
                        'english' => 'English',
                        'swahili' => 'Swahili',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => '/admin/books/' . $record->id . '/edit'),
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => '/admin/books/' . $record->id . '/edit'),
            ])
            ->bulkActions([
                // No bulk actions
            ]);
    }
}

