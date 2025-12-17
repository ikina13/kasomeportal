<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoViewsResource\Pages;
use App\Filament\Resources\VideoViewsResource\RelationManagers;
use App\Filament\Resources\VideoViewsResource\RelationManagers\ClipViewsRelationManager;
use App\Models\video_views_model as VideoViews;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VideoViewsResource extends Resource
{
    protected static ?string $model = VideoViews::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static ?string $label = 'Views';

     protected static ?int $sort = 3;

    public static function form(Form $form): Form
    {
        return $form
             ->schema([
                Forms\Components\TextInput::make('user_id')->required(),
                Forms\Components\TextInput::make('video_id')->required(),
                Forms\Components\TextInput::make('created_at')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                 Tables\Columns\TextColumn::make('user.name')->label('Student Name')->searchable(),
                 Tables\Columns\TextColumn::make('video.name')->label('Course Name')->searchable(),
                 Tables\Columns\TextColumn::make('user.region')->label('Location')->searchable(),
                 Tables\Columns\TextColumn::make('created_at')->label('Viewed At')->searchable(),
            ])
            ->filters([
                //
                Tables\Filters\Filter::make('Date Range')
                ->form([
                    Forms\Components\DatePicker::make('start_date')->label('Start Date'),
                    Forms\Components\DatePicker::make('end_date')->label('End Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    if ($data['start_date'] ?? null) {
                        $query->where('created_at', '>=', $data['start_date']);
                    }
                    if ($data['end_date'] ?? null) {
                        $query->where('created_at', '<=', $data['end_date']);
                    }
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
             ->defaultSort('created_at', 'desc');
    }
    
     public static function getRelations(): array
    {
        return [
            ClipViewsRelationManager::class,
        ];
    }

     protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('user','video');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoViews::route('/'),
            'create' => Pages\CreateVideoViews::route('/create'),
            'edit' => Pages\EditVideoViews::route('/{record}/edit'),
        ];
    }    
}
