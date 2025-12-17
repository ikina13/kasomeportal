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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\class_model as ClassModel;
use App\Models\practical_video_model;
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
                    ->options(ClassModel::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->columnSpan('full')
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Clear course selection when class changes
                        $set('course_ids', []);
                    }),

                // Course Selection Section
                Section::make('Course Selection')
                    ->description('Select the courses from this class to include in the subscription')
                    ->schema([
                        // Bulk toggle checkbox
                        Checkbox::make('select_all_courses_toggle')
                            ->label(function ($get) {
                                $classId = $get('class_id');
                                if (!$classId) {
                                    return 'Select a class first to see courses';
                                }
                                
                                $courses = practical_video_model::where('class_id', $classId)
                                    ->where('status', 'active')
                                    ->get();
                                $selected = count($get('course_ids') ?? []);
                                $total = count($courses);
                                
                                if ($selected === $total && $total > 0) {
                                    return '✓ All courses selected - Uncheck to deselect all';
                                } else {
                                    return '☐ Select/Deselect All Courses (' . $selected . ' of ' . $total . ' selected)';
                                }
                            })
                            ->visible(fn ($get) => !empty($get('class_id')))
                            ->reactive()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $classId = $get('class_id');
                                if (!$classId) {
                                    return;
                                }
                                
                                $courses = practical_video_model::where('class_id', $classId)
                                    ->where('status', 'active')
                                    ->pluck('id')
                                    ->toArray();
                                    
                                if ($state) {
                                    // Select all
                                    $set('course_ids', $courses);
                                } else {
                                    // Deselect all
                                    $set('course_ids', []);
                                }
                            })
                            ->dehydrated(false)
                            ->extraAttributes(['class' => 'text-lg font-semibold'])
                            ->columnSpan('full'),

                        CheckboxList::make('course_ids')
                            ->label('Courses')
                            ->options(function ($get) {
                                $classId = $get('class_id');
                                if (!$classId) {
                                    return [];
                                }
                                
                                return practical_video_model::where('class_id', $classId)
                                    ->where('status', 'active')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->columns(2)
                            ->columnSpan('full')
                            ->required()
                            ->dehydrated(true)
                            ->reactive()
                            ->visible(fn ($get) => !empty($get('class_id')))
                            ->afterStateUpdated(function ($state, $get, $set) {
                                // Update the bulk toggle checkbox state based on selections
                                $classId = $get('class_id');
                                if (!$classId) {
                                    return;
                                }
                                
                                $courses = practical_video_model::where('class_id', $classId)
                                    ->where('status', 'active')
                                    ->pluck('id')
                                    ->toArray();
                                $allSelected = count($state ?? []) === count($courses) && count($courses) > 0;
                                $set('select_all_courses_toggle', $allSelected);
                            })
                            ->helperText(function ($get) {
                                $classId = $get('class_id');
                                if (!$classId) {
                                    return 'Please select a class first to see available courses.';
                                }
                                
                                $courses = practical_video_model::where('class_id', $classId)
                                    ->where('status', 'active')
                                    ->get();
                                $selected = count($get('course_ids') ?? []);
                                $total = count($courses);
                                return "Use the checkbox above to select/deselect all courses. Currently selected: {$selected} of {$total} courses.";
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->columnSpan('full'),
                
                DateTimePicker::make('start_date')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y H:i')
                    ->timezone('Africa/Dar_es_Salaam'),
                
                DateTimePicker::make('end_date')
                    ->required()
                    ->displayFormat('d/m/Y H:i')
                    ->timezone('Africa/Dar_es_Salaam'),

                TextInput::make('amount')
                    ->numeric()
                    ->prefix('Tsh')
                    ->default(0),
                    
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                    ])
                    ->default('active')
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table // <-- FIX: MUST be static
    {
        return $table
            ->columns([
                TextColumn::make('class.name') // Uses the 'class' relationship
                    ->label('Class Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('courses.name')
                    ->label('Courses')
                    ->limit(5),

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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set default values
                        $data['created_by'] = auth()->id();
                        $data['updated_by'] = auth()->id();
                        return $data;
                    })
                    ->using(function (array $data, $record, RelationManager $livewire): \Illuminate\Database\Eloquent\Model {
                        $courseIds = $data['course_ids'] ?? [];
                        unset($data['course_ids']);
                        
                        // Create class subscription
                        $classSubscription = $record->classSubscriptions()->create($data);
                        
                        // Sync courses
                        if (!empty($courseIds)) {
                            $classSubscription->courses()->sync($courseIds);
                        }
                        
                        return $classSubscription;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Load existing course IDs for editing
                        $data['course_ids'] = $record->courses()->pluck('id')->toArray();
                        return $data;
                    })
                    ->using(function (array $data, $record): \Illuminate\Database\Eloquent\Model {
                        $courseIds = $data['course_ids'] ?? [];
                        unset($data['course_ids']);
                        $data['updated_by'] = auth()->id();
                        
                        // Update class subscription
                        $record->update($data);
                        
                        // Sync courses
                        $record->courses()->sync($courseIds);
                        
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
           ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }
}
