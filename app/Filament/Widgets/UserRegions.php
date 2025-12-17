<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentStartsResource;
use App\Filament\Resources\UserRegionsResource;
use App\Models\user_regions as Regions;
use Illuminate\Database\Capsule\Manager as RawQuery;
use App\Models\app_user as User;
use Illuminate\Support\Facades\DB;
use Closure;
use Filament\Tables;

use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;

class UserRegions extends BaseWidget

{
    protected static ?int $sort = 5;
    protected static ?string $heading = 'Top 5 Regions by Registration';
    protected function getTableQuery(): Builder
    {
        // Step 1: Delete all existing data in tbl_user_regions
        DB::table('tbl_user_regions')->truncate();

        // Step 1: Get the top 5 regions
        $topRegions = DB::table('tbl_users')
            ->select('region', DB::raw('COUNT(*) as region_count'))
            ->groupBy('region')
            ->orderByDesc('region_count')
            ->limit(5)
            ->get();

        // Step 2: Insert the regions into tbl_user_regions
        foreach ($topRegions as $region) {
            DB::table('tbl_user_regions')->insert([
                'region' => $region->region,
                'value' => $region->region_count,
                'created_at' => now(),
                'created_by' => auth()->id() ?? 1, // Default user or logged-in user
            ]);
        }

        $results = User::query()
            ->select(User::raw('count(tbl_users.id) as Total'), 'tbl_regions.name as Region')
            ->join('tbl_regions', 'tbl_users.region_id', '=', 'tbl_regions.id')
            ->whereHas('role', function ($q) {
                $q->where('name', 'app-user');
            })
            ->groupBy('tbl_regions.name')
            ->orderBy('total', 'desc')
            ->take(4)
            ->get();
        $index = 1;
        foreach ($results as $region){

            $regions = Regions::where('id',$index)->update(["value"=>$region->total,"region"=>$region->Region]);

            $index++;
        }
        return  UserRegionsResource::getEloquentQuery();
    }



    protected function getTableColumns(): array
    {
        return [
             // Index Column
        TextColumn::make('index')
            ->label('No.') // Column label
            ->getStateUsing(function ($rowLoop, $livewire) {
                return ($livewire->tableRecordsPerPage * ($livewire->page - 1)) + $rowLoop->iteration;
            })
            ->sortable(false), // Optional: Disables sorting for this column

            Tables\Columns\BadgeColumn::make('region')
                ->icons([

                    'heroicon-o-inbox-in' => 'Pending Bills',
                    'heroicon-o-inbox' => 'Settled Bills',
                    'heroicon-o-x' => 'Cancelled Bills',
                ])
                ->colors([

                    'warning' => 'Pending Bills',
                    'success' => 'Settled Bills',
                    'danger' => 'Cancelled Bills',
                ])
                ->default(0) ,
            Tables\Columns\BadgeColumn::make('value')->label('Registered')
                ->default(0)
                ->colors([
                    'warning'

                ])


        ];
    }
}
