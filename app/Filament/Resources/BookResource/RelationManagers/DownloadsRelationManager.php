<?php

namespace App\Filament\Resources\BookResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BookDownload;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DownloadsRelationManager extends RelationManager
{
    // Dummy relationship - we override getTableQuery() to get downloads through purchases
    protected static string $relationship = 'purchases';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Download History';

    // Override to get downloads through purchases
    protected function getTableQuery(): Builder
    {
        $bookId = $this->ownerRecord->id;
        
        // Check if table exists before querying
        try {
            if (!Schema::hasTable('tbl_book_downloads')) {
                // Return empty query if table doesn't exist
                return BookDownload::query()->whereRaw('1 = 0');
            }
            
            return BookDownload::query()
                ->whereHas('purchase', function($query) use ($bookId) {
                    $query->where('book_id', $bookId);
                })
                ->with(['user', 'purchase', 'book']);
        } catch (\Exception $e) {
            // If table doesn't exist, return empty query
            return BookDownload::query()->whereRaw('1 = 0');
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Downloads are read-only, so no form needed
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('purchase.id')
                    ->label('Purchase ID'),

                Tables\Columns\TextColumn::make('downloaded_at')
                    ->label('Downloaded At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address'),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('File Size')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2) . ' KB' : 'N/A'),

                Tables\Columns\BadgeColumn::make('download_status')
                    ->colors([
                        'success' => 'success',
                        'danger' => 'failed',
                        'warning' => 'expired',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('download_status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                    ]),
            ])
            ->defaultSort('downloaded_at', 'desc')
            ->actions([
                // Downloads are read-only
            ])
            ->bulkActions([
                // No bulk actions
            ]);
    }
}

