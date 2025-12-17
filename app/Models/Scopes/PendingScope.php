<?php
 
namespace App\Models\Scopes;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Scope;
 
class PendingScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('cancel_accepted', false)->where('is_bill_settled', false);
    }
}