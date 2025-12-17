<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PracticleVideoResource\Pages;
use App\Filament\Resources\PracticleVideoClipsResource\Pages\ListPracticleVideoClips;
use App\Filament\Resources\PracticleVideoResource\RelationManagers;
use App\Filament\Resources\PracticleVideoResource\RelationManagers\PracticleVideoClipsRelationManager;
use App\Models\practical_video_model as PracticleVideo;
use App\Models\course_model ;
use App\Models\class_model as ClassModel;
use App\Models\subject_model as Subject;
use App\Models\author_model as Author;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ImageColumn;

class PracticleVideoResource extends Resource
{
    protected static ?string $model = PracticleVideo::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationGroup = 'Course Management';

    public static ?string $label = 'Course Video';

    public static function form(Form $form): Form
    {
        
       return $form
        ->schema([
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('author')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('class_id')
    ->label('Class Name')
    ->options(ClassModel::all()->pluck('name', 'id'))
    ->searchable(),
    Forms\Components\Select::make('subject_id')
    ->label('Enter Subject')
    ->options(Subject::all()->pluck('name', 'id'))
    ->searchable()
   /* ->createOptionForm([
        Forms\Components\TextInput::make('name')
            ->required()
    ])*/        
                    
                     
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
                ImageColumn::make('thumbnail')->size(50),
                Tables\Columns\TextColumn::make('name')->label("Course Name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make('author')->label("Course Author")->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price')->label("Course Price")->searchable()->sortable(),
               Tables\Columns\TextColumn::make('created_at')->label("Created Date")->searchable()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->after(function () {

                    $video = PracticleVideo::latest('id')->first();
 
                    //Change picture location
            
                      $insert_image = PracticleVideo::where("id",$video->id)->update([
                          "image_name"=>$video->thumbnail,
                          "thumbnail"=>env('APP_URL')."/storage/".$video->thumbnail,
                          "videourl"=>env('APP_URL')."/storage/".$video->videourl, 
                      ]);
            
                    
                })
                 
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PracticleVideoClipsRelationManager::class
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('course');
    }
     

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPracticleVideos::route('/'),
            'create' => Pages\CreatePracticleVideo::route('/create'),
            'practical-video-clips-models' => PracticleVideoClipsResource\Pages\ListPracticleVideoClips::route('/{record}'),
            'practical-video-clips-models.create' => PracticleVideoClipsResource\Pages\ListPracticleVideoClips::route('/{record}/create'),
            'edit' => Pages\EditPracticleVideo::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
       return true;
    }
}
