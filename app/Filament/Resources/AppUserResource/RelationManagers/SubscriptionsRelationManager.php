<?php

namespace App\Filament\Resources\AppUserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Form $form): Form
    {
         return $form
        ->schema([
            Forms\Components\Card::make()
                ->schema([
                     
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->required(),

                    Forms\Components\DateTimePicker::make('start_date')
                        ->required()
                        ->default(now()),

                    Forms\Components\DateTimePicker::make('end_date')
                        ->required(),
                    
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'expired' => 'Expired',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('active')
                        ->required(),
                ])
                ->columns(2), // Organizes the form into two columns
        ]);
    }

    public static function table(Table $table): Table
    {
         return $table
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->sortable(),

            // This displays the user's name from the related table
            Tables\Columns\TextColumn::make('user.name')
                ->label('Full name')
                ->searchable()
                ->sortable(),
            
            Tables\Columns\TextColumn::make('amount')
                
                ->sortable(),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'active',
                    'danger' => 'expired',
                    'warning' => 'cancelled',
                ])
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('start_date')
                ->dateTime()
                ->sortable(),

            Tables\Columns\TextColumn::make('end_date')
                ->dateTime()
                ->sortable(),
            
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true), // Hidden by default
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
                ])
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->defaultSort('id', 'desc');
    }    
}
