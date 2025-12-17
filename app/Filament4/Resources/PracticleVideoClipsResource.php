<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PracticleVideoClipsResource\Pages;

use App\Filament\Resources\PracticleVideoClipsResource\RelationManagers;
use App\Models\practical_video_clips_model as PracticleVideoClips;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PracticleVideoClipsResource extends Resource
{
    protected static ?string $model = PracticleVideoClips::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static bool $shouldRegisterNavigation = false;


    protected static ?string $navigationGroup = 'Course Management';

    public static ?string $label = 'Practicle Video Clips';

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
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('playbackInfo')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('payment_status')
                        ->options([
                           'free' => 'free',
                           'buy' => 'buy',
                           'paid' => 'paid',
                      ])
                     
                    
                     
                ])  ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                       /* Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label('Visible')
                                     ->onIcon('heroicon-s-eye')
                                    ->offIcon('heroicon-s-eye')
                                    ->onColor('success')
                                    ->offColor('warning')
                                    ->helperText('This product will be hidden from all applications.')
                                    ->default(false)
                            ]),*/
                        Forms\Components\Section::make('Course Thumbnail:')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    //->directory('app/public')
                                    ->image()

                            ])
                            ->collapsible()

                    ])

                    ->columnSpan(['lg' => 1]),
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
            'index' => Pages\ListPracticleVideoClips::route('/'),
            'create' => Pages\CreatePracticleVideoClips::route('/create'),
            
            'edit' => Pages\EditPracticleVideoClips::route('/{record}/edit'),
        ];
    }
}
