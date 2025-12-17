<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentStartsResource;
use Closure;
use Filament\Tables;
use App\Models\payment_stats_model as Payment;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;


class PaymentInfomation extends BaseWidget

{

    protected static ?int $sort = 3;



    protected function getTableQuery(): Builder
    {
      //  $value = Payment::Query()->latest();
      //  dd(  Payment::Query()->latest('id')->first());
       return  PaymentStartsResource::getEloquentQuery();


    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\BadgeColumn::make('payment_statistics')
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
                    'secondary'

                ])


        ];
    }
}
