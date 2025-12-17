<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentStartsResource;
use Closure;
use Filament\Tables;
use App\Models\payment_stats_model as Payment;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


class PaymentInfomation extends BaseWidget

{

    protected static ?int $sort = 3;



    protected function getTableQuery(): Builder
    {
      //  $value = Payment::Query()->latest();
      //  dd(  Payment::Query()->latest('id')->first());
       return  PaymentStartsResource::getEloquentQuery()->orderBy('id', 'asc');


    }

    protected function getTableColumns(): array
    {
      

        $pendingCount = DB::table('tbl_payment')
        ->where('status', 'pending')
        ->count();

        $settledCount = DB::table('tbl_payment')
        ->where('status', 'settled')
        ->count();

        $pendingSum = DB::table('tbl_payment')
        ->where('status', 'pending')
        ->sum('amount');

        $settledSum = DB::table('tbl_payment')
        ->where('status', 'settled')
        ->sum('amount');



        DB::table('tbl_payment_starts')->where('id',1)->update([
            "value"=>$pendingCount,
            "amount"=>$pendingSum


        ]);

        DB::table('tbl_payment_starts')->where('id',2)->update([
            "value"=>$settledCount,
            "amount"=>$settledSum
        ]);

        

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
            Tables\Columns\BadgeColumn::make('value')->label("transactions")
                ->default(0)
                ->colors([
                    'secondary'

                ]),

             Tables\Columns\BadgeColumn::make('amount')->label("Amount Collected")
                ->default(0)
                 ->formatStateUsing(function ($state) {
                return number_format($state, 2); // Format each amount with commas
            })
                ->colors([
                    'secondary'

                ])        


        ];
    }
}
