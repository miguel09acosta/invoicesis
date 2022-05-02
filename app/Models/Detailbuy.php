<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detailbuy extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getUpdatedAtAttribute( $value ) {
        return $value? (new Carbon($value))->format('Y-m-d H:i:s') : null;
    }

    public function getCreatedAtAttribute( $value ) {
        return $value? (new Carbon($value))->format('Y-m-d H:i:s') : null;
    }

    public function adminBook()
    {
        return $this->belongsTo(AdminBook::class,'id_books');
    }

}
