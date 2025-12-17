<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionsResource\Pages;
use App\Filament\Resources\QuestionsResource\RelationManagers;
use App\Models\question_model as Questions;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class QuestionsResource extends Resource
{
    protected static ?string $model = Questions::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Course Management';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make('Multiple Choices')
                            ->schema([
                                Repeater::make('Add Multiple Choice')
                                    ->schema([
                                        TextInput::make('qn')
                                            ->label('Question')
                                            ->placeholder('Enter Question')
                                            ->required()
                                            ->columnSpan('full'),
                                        TextInput::make('a')
                                            ->label('')
                                            ->placeholder('a')
                                            ->columnSpan('full')
                                            ->required(),
                                        TextInput::make('b')
                                            ->label('')
                                            ->placeholder('b')
                                            ->columnSpan('full')
                                            ->required(),
                                        TextInput::make('c')
                                            ->label('')
                                            ->placeholder('c')
                                            ->columnSpan('full')
                                            ->required(),
                                        TextInput::make('d')
                                            ->label('')
                                            ->placeholder('d')
                                            ->columnSpan('full')
                                            ->required(),
                                        Select::make('Answer')
                                            ->options([
                                                'a' => 'a',
                                                'b' => 'b',
                                                'c' => 'c',
                                                'd' => 'd',
                                            ])

                                    ])
                                    ->createItemButtonLabel('Add multiple Choice')
                                    ->minItems(1)
                                    ->maxItems(5)
                                    ->columnSpan('full')
                                    ->columns(2)

                            ])
                            ->collapsible()
                            ->columns(2),
                        Forms\Components\Section::make('True and False')
                            ->schema([

                                Repeater::make('members2')
                                    ->schema([
                                        TextInput::make('qn')
                                            ->label('Question')
                                            ->placeholder('Enter Question')
                                            ->required()
                                            ->columnSpan('full'),
                                        TextInput::make('a')
                                            ->label('')
                                            ->columnSpan(1)
                                            ->default('True')
                                            ->required()
                                            ,
                                        TextInput::make('b')
                                            ->label('')
                                            ->default('False')
                                            ->required()
                                            ->columnSpan(1),
                                        Select::make('Answer')
                                            ->options([
                                                'a' => 'True',
                                                'b' => 'False',
                                            ])
                                    ])
                                    ->createItemButtonLabel('Add True or False')
                                    ->minItems(1)
                                    ->maxItems(5)
                                    ->columnSpan('full')

                            ])
                            ->collapsible()
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),


            ])
            ->columns(3);
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestions::route('/create'),
            'edit' => Pages\EditQuestions::route('/{record}/edit'),
        ];
    }
}
