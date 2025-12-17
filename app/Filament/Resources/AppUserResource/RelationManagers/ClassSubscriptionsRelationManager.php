<?php

namespace App\Filament\Resources\AppUserResource\RelationManagers;

// --- CORE V2 IMPORTS ---
use Filament\Resources\Form;    // <-- Correct v2 Import
use Filament\Resources\Table;   // <-- Correct v2 Import
use Filament\Resources\RelationManagers\RelationManager;
// ---

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\class_model as ClassModel;
use Filament\Tables; // For CreateAction, EditAction, etc.

class ClassSubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'classSubscriptions';

    // In v2, the record title is a property
    protected static ?string $recordTitleAttribute = 'class.name';

    public static function form(Form $form): Form // <-- FIX: MUST be static
    {
        return $form
            ->schema([
                Select::make('class_id')
                    ->label('Class')
                    ->options(ClassModel::all()->pluck('name', 'id')) // Gets all class names
                    ->searchable()
                    ->required(),
                
                DateTimePicker::make('start_date')
                    ->required(),
                
                DateTimePicker::make('end_date')
                    ->required(),

                TextInput::make('amount')
                    ->numeric()
                    ->prefix('Tsh'), // Or your currency
                    
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table // <-- FIX: MUST be static
    {
        return $table
            ->columns([
                TextColumn::make('class.name') // Uses the 'class' relationship
                    ->label('Class Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'expired',
                    ]),
                
                TextColumn::make('amount')
                 
                    ->sortable(),
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
            ]);
    }
}
