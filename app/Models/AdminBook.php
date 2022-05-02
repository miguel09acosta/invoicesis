<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminBook extends Model
{

    use HasFactory;

    protected $guarded = ['id'];


    public function getUpdatedAtAttribute( $value ) {
        return $value? (new Carbon($value))->format('Y-m-d H:i:s') : null;
    }

    public function getCreatedAtAttribute( $value ) {
        return $value? (new Carbon($value))->format('Y-m-d H:i:s') : null;
    }

    public function detailbuys()
    {
        return $this->hasMany(Detailbuy::class, 'id_books');
    }

}
