<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettledBillsResource\Pages;
use App\Filament\Resources\SettledBillsResource\RelationManagers;
use App\Models\payments_model as SettledBills;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettledBillsResource extends Resource
{
    protected static ?string $model = SettledBills::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Course Management';

    public static ?string $label = 'Payments';

    protected static ?int $sort = 5;

    
   public function __construct(string $state)
    {
        // Constructor logic...
    }

public static function form(Form $form): Form
    {
        
        return $form
        ->schema([
        Forms\Components\Card::make()
        ->schema([
        Forms\Components\TextInput::make('id')
                ->label('Id')
                ->required(),
        Forms\Components\TextInput::make('transactiontoken')
                ->required()
                ->label('Transaction Token'),

       Forms\Components\TextInput::make('pnrid')
                ->label('pnrid'),

       Forms\Components\TextInput::make('ccdapproval'),
       Forms\Components\TextInput::make('transid')
               ->label('transid'),
        Forms\Components\TextInput::make('amount')
                ->required(),
        Forms\Components\TextInput::make('status')
                ->required()
                
        ])
      ]);  
            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Id')->searchable(),
                Tables\Columns\TextColumn::make('users.name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('users.phone')->label('Phone')->searchable(),
                Tables\Columns\TextColumn::make('video.name')->label('Course')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Amount')->searchable(),
                //Tables\Columns\TextColumn::make('transactiontoken')->label('Token')->sortable(),
               
                Tables\Columns\TextColumn::make('created_at')->label('Created Date')->sortable(),
                Tables\Columns\TextColumn::make('status')->searchable()
                    
                    ->color(fn($record) => $record->status ==='settled' ? 'success' : 'warning')    
            ])
            ->filters([
                //
            ])
            ->actions([
               // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
               // Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

   public function actions()
    {
        $actions = parent::actions();

        // Remove the edit and delete actions for the 'status' column
        unset($actions['edit']);
        unset($actions['delete']);

        return $actions;
    }

    public static function canCreate(): bool
    {
       return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettledBills::route('/'),
            'create' => Pages\CreateSettledBills::route('/create'),
            'edit' => Pages\EditSettledBills::route('/{record}/edit'),
        ];
    }
}
