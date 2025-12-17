<?php

namespace App\Filament\Resources\AppUserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\practical_video_model;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Form $form): Form
    {
        // Get all active courses for checkbox list
        $courses = practical_video_model::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        return $form
        ->schema([
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\Select::make('subscription_type')
                        ->label('Subscription Type')
                        ->options([
                            'all_courses' => 'All Courses',
                            'specific_courses' => 'Specific Courses',
                        ])
                        ->default('all_courses')
                        ->required()
                        ->reactive()
                        ->columnSpan('full')
                        ->afterStateUpdated(function ($state, callable $set) use ($courses) {
                            // When "All Courses" is selected, auto-select all course IDs
                            if ($state === 'all_courses') {
                                $set('course_ids', array_keys($courses));
                            } else {
                                // When "Specific Courses" is selected, clear selection
                                $set('course_ids', []);
                            }
                        }),

                    // Course Selection Section
                    Forms\Components\Section::make('Course Selection')
                        ->description(fn ($get) => $get('subscription_type') === 'all_courses' 
                            ? 'All courses are included. Use "Specific Courses" to select individual courses below.'
                            : 'Select the courses to include in this subscription.')
                        ->schema([
                            // Bulk toggle checkbox (only visible for specific_courses)
                            Forms\Components\Checkbox::make('select_all_courses_toggle')
                                ->label(function ($get) use ($courses) {
                                    $selected = count($get('course_ids') ?? []);
                                    $total = count($courses);
                                    if ($selected === $total) {
                                        return '✓ All courses selected - Uncheck to deselect all';
                                    } else {
                                        return '☐ Select/Deselect All Courses (' . $selected . ' of ' . $total . ' selected)';
                                    }
                                })
                                ->visible(fn ($get) => $get('subscription_type') === 'specific_courses')
                                ->reactive()
                                ->afterStateUpdated(function ($state, $get, $set) use ($courses) {
                                    $allCourseIds = array_keys($courses);
                                    if ($state) {
                                        // Select all
                                        $set('course_ids', $allCourseIds);
                                    } else {
                                        // Deselect all
                                        $set('course_ids', []);
                                    }
                                })
                                ->dehydrated(false)
                                ->extraAttributes(['class' => 'text-lg font-semibold'])
                                ->columnSpan('full'),

                            Forms\Components\CheckboxList::make('course_ids')
                                ->label('Courses')
                                ->options($courses)
                                ->columns(2)
                                ->columnSpan('full')
                                ->required(fn ($get) => $get('subscription_type') === 'specific_courses')
                                ->disabled(fn ($get) => $get('subscription_type') === 'all_courses')
                                ->dehydrated(true)
                                ->reactive()
                                ->afterStateHydrated(function ($component, $state, $get) use ($courses) {
                                    // If all_courses and no state, set all courses
                                    if ($get('subscription_type') === 'all_courses' && empty($state)) {
                                        $component->state(array_keys($courses));
                                    }
                                })
                                ->afterStateUpdated(function ($state, $get, $set) use ($courses) {
                                    // Update the bulk toggle checkbox state based on selections
                                    $allCourseIds = array_keys($courses);
                                    $allSelected = count($state ?? []) === count($allCourseIds);
                                    $set('select_all_courses_toggle', $allSelected);
                                })
                                ->helperText(function ($get) use ($courses) {
                                    if ($get('subscription_type') === 'all_courses') {
                                        return 'All courses are automatically selected (included in subscription)';
                                    }
                                    $selected = count($get('course_ids') ?? []);
                                    $total = count($courses);
                                    return "Use the checkbox above to select/deselect all courses. Currently selected: {$selected} of {$total} courses.";
                                }),
                        ])
                        ->collapsible()
                        ->collapsed(false)
                        ->columnSpan('full'),

                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->prefix('Tsh')
                        ->default(0),

                    Forms\Components\DateTimePicker::make('start_date')
                        ->required()
                        ->default(now())
                        ->displayFormat('d/m/Y H:i')
                        ->timezone('Africa/Dar_es_Salaam'),

                    Forms\Components\DateTimePicker::make('end_date')
                        ->required()
                        ->displayFormat('d/m/Y H:i')
                        ->timezone('Africa/Dar_es_Salaam'),
                    
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'expired' => 'Expired',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('active')
                        ->required(),
                ])
                ->columns(2),
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
            
            Tables\Columns\BadgeColumn::make('subscription_type')
                ->label('Type')
                ->colors([
                    'primary' => 'all_courses',
                    'success' => 'specific_courses',
                ])
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'all_courses' => 'All Courses',
                    'specific_courses' => 'Specific Courses',
                    default => $state,
                })
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

            Tables\Columns\TextColumn::make('courses.name')
                ->label('Courses')
                ->visible(fn ($record) => $record?->subscription_type === 'specific_courses')
                ->limit(3),
            
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
                ]),
            Tables\Filters\SelectFilter::make('subscription_type')
                ->label('Subscription Type')
                ->options([
                    'all_courses' => 'All Courses',
                    'specific_courses' => 'Specific Courses',
                ])
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->mutateFormDataUsing(function (array $data, $record): array {
                    // Load existing course IDs for editing
                    if ($record) {
                        if ($record->subscription_type === 'all_courses') {
                            // For all_courses, load all active course IDs to show all checked
                            $data['course_ids'] = practical_video_model::where('status', 'active')
                                ->pluck('id')
                                ->toArray();
                        } else {
                            // For specific_courses, load only associated courses
                            $data['course_ids'] = $record->courses()->pluck('id')->toArray();
                        }
                    }
                    return $data;
                })
                ->using(function (array $data, $record): \Illuminate\Database\Eloquent\Model {
                    $courseIds = $data['course_ids'] ?? [];
                    $subscriptionType = $data['subscription_type'] ?? 'all_courses';
                    unset($data['course_ids']);
                    $data['updated_by'] = auth()->id();
                    
                    // Update subscription
                    $record->update($data);
                    
                    // Handle course associations based on subscription type
                    if ($subscriptionType === 'specific_courses' && !empty($courseIds)) {
                        // Sync only selected courses for specific_courses
                        $record->courses()->sync($courseIds);
                    } elseif ($subscriptionType === 'all_courses') {
                        // For all_courses, sync all active courses for easier management
                        $allCourseIds = practical_video_model::where('status', 'active')
                            ->pluck('id')
                            ->toArray();
                        $record->courses()->sync($allCourseIds);
                    }
                    
                    return $record;
                }),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Set default values
                    $data['created_by'] = auth()->id();
                    $data['updated_by'] = auth()->id();
                    if (!isset($data['subscription_type'])) {
                        $data['subscription_type'] = 'all_courses';
                    }
                    return $data;
                })
                ->using(function (array $data, $record, RelationManager $livewire): \Illuminate\Database\Eloquent\Model {
                    $courseIds = $data['course_ids'] ?? [];
                    $subscriptionType = $data['subscription_type'] ?? 'all_courses';
                    unset($data['course_ids']);
                    
                    // Create subscription
                    $subscription = $record->subscriptions()->create($data);
                    
                    // Handle course associations based on subscription type
                    if ($subscriptionType === 'specific_courses' && !empty($courseIds)) {
                        // Sync only selected courses for specific_courses
                        $subscription->courses()->sync($courseIds);
                    } elseif ($subscriptionType === 'all_courses') {
                        // For all_courses, we don't need to store individual courses
                        // The subscription_type itself indicates access to all
                        // But we can optionally sync all courses for easier management
                        $allCourseIds = practical_video_model::where('status', 'active')
                            ->pluck('id')
                            ->toArray();
                        $subscription->courses()->sync($allCourseIds);
                    }
                    
                    return $subscription;
                }),
        ])
        ->defaultSort('id', 'desc');
    }    
}
