<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentStartsResource\Pages;
use App\Filament\Resources\PaymentStartsResource\RelationManagers;
use App\Models\payment_stats_model as PaymentStarts;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentStartsResource extends Resource
{
    protected static ?string $model = PaymentStarts::class;

    protected static bool $shouldRegisterNavigation = false;


    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentStarts::route('/'),
            'create' => Pages\CreatePaymentStarts::route('/create'),
            'edit' => Pages\EditPaymentStarts::route('/{record}/edit'),
        ];
    }
}
