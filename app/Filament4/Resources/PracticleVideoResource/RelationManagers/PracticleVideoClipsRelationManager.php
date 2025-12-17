<?php

namespace App\Filament\Resources\PracticleVideoResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ImageColumn;

class PracticleVideoClipsRelationManager extends RelationManager
{
    protected static string $relationship = 'PracticleVideoClips';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                     Forms\Components\TextInput::make('video_id')
                        ->required()
                        ->maxLength(255),    
                    Forms\Components\TextInput::make('otp')
                        ->required(),
                    Forms\Components\TextInput::make('playbackInfo')
                        ->required(),
                   Forms\Components\TextInput::make('price')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('author')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('payment_status')
                        ->options([
                           'free' => 'free',
                           'buy' => 'buy',
                           'paid' => 'paid',
                      ]),    
                     Forms\Components\Section::make('Course Thumbnail:')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    //->directory('app/public')
                                    ->image()

                            ])
                            ->collapsible()        
                    
                     
                ])   
       ]);
                
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')->size(50),
                Tables\Columns\TextColumn::make('name')->label("Course Name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price')->label("Course Price")->searchable()->sortable(),
                Tables\Columns\TextColumn::make('author')->label("Course Author")->searchable()->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                     ->label("Payment Status")
                     ->searchable()
                     ->color('success')
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
