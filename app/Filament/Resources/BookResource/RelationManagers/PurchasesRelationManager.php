<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BookPurchase;
use Filament\Tables\Columns\BadgeColumn;

class PurchasesRelationManager extends RelationManager
{
    protected static string $relationship = 'purchases';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('payment_id')
                    ->numeric(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('completed')
                    ->required(),

                Forms\Components\Select::make('purchase_type')
                    ->options([
                        'purchase' => 'Purchase',
                        'donation' => 'Donation',
                    ])
                    ->default('purchase'),

                Forms\Components\TextInput::make('download_count')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('max_downloads')
                    ->numeric()
                    ->default(5)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment.amount')
                    ->label('Amount')
                    ->money('TZS')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                BadgeColumn::make('purchase_type')
                    ->colors([
                        'primary' => 'purchase',
                        'success' => 'donation',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('download_count')
                    ->label('Downloads'),

                Tables\Columns\TextColumn::make('max_downloads')
                    ->label('Max Downloads'),

                Tables\Columns\TextColumn::make('remaining_downloads')
                    ->label('Remaining')
                    ->getStateUsing(fn ($record) => $record->getRemainingDownloads()),

                Tables\Columns\TextColumn::make('purchased_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_downloaded_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
                Tables\Actions\Action::make('regenerate_token')
                    ->label('Regenerate Token')
                    ->icon('heroicon-o-key')
                    ->color('primary')
                    ->action(function ($record) {
                        $record->regenerateToken();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}

