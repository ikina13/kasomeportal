<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookPurchaseResource\Pages;
use App\Models\BookPurchase;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class BookPurchaseResource extends Resource
{
    protected static ?string $model = BookPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Book Management';

    public static ?string $label = 'Book Purchases';

    protected static ?int $sort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('book_id')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('payment_id')
                    ->relationship('payment', 'id')
                    ->searchable(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('completed')
                    ->required(),

                Forms\Components\Select::make('purchase_type')
                    ->label('Type')
                    ->options([
                        'purchase' => 'Purchase',
                        'donation' => 'Donation',
                    ])
                    ->default('purchase')
                    ->visible(fn () => Schema::hasColumn('tbl_book_purchases', 'purchase_type'))
                    ->required(fn () => Schema::hasColumn('tbl_book_purchases', 'purchase_type')),

                Forms\Components\TextInput::make('download_count')
                    ->label('Downloads Used')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('max_downloads')
                    ->label('Max Downloads')
                    ->numeric()
                    ->default(5)
                    ->required(),

                Forms\Components\DateTimePicker::make('purchased_at')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('book.image_url')
                    ->label('Book')
                    ->size(40)
                    ->defaultImageUrl(url('/images/default-book.png')),

                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book Title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment.amount')
                    ->label('Amount Paid')
                    ->money('TZS')
                    ->sortable()
                    ->default('N/A'),

                BadgeColumn::make('purchase_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'purchase',
                        'success' => 'donation',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable()
                    ->visible(fn () => Schema::hasColumn('tbl_book_purchases', 'purchase_type')),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('download_count')
                    ->label('Downloads')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_downloads')
                    ->label('Max')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_downloads')
                    ->label('Remaining')
                    ->getStateUsing(fn ($record) => $record->getRemainingDownloads())
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchased_at')
                    ->label('Purchased')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_downloaded_at')
                    ->label('Last Download')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('purchased_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('purchase_type')
                    ->label('Purchase Type')
                    ->options([
                        'purchase' => 'Purchase',
                        'donation' => 'Donation',
                    ])
                    ->visible(fn () => Schema::hasColumn('tbl_book_purchases', 'purchase_type')),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\Filter::make('purchased_at')
                    ->form([
                        Forms\Components\DatePicker::make('purchased_from')
                            ->label('Purchased From'),
                        Forms\Components\DatePicker::make('purchased_until')
                            ->label('Purchased Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['purchased_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchased_at', '>=', $date),
                            )
                            ->when(
                                $data['purchased_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchased_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reset_downloads')
                    ->label('Reset Downloads')
                    ->icon('heroicon-o-refresh')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'download_count' => 0,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookPurchases::route('/'),
            'create' => Pages\CreateBookPurchase::route('/create'),
            'edit' => Pages\EditBookPurchase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['book', 'user', 'payment']);
    }
}

