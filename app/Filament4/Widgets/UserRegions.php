<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentStartsResource;
use App\Filament\Resources\UserRegionsResource;
use App\Models\user_regions as Regions;
use Illuminate\Database\Capsule\Manager as RawQuery;
use App\Models\app_user as User;
use Closure;
use Filament\Tables;

use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UserRegions extends BaseWidget

{
    protected static ?int $sort = 5;
    protected function getTableQuery(): Builder
    {
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
            Tables\Columns\BadgeColumn::make('value')
                ->default(0)
                ->colors([
                    'warning'

                ])


        ];
    }
}
