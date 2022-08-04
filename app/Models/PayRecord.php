<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayRecord extends Model
{
    use HasFactory;

    protected $hidden = ['payer_id'];
    protected $fillable = ['payer_id', 'paid_date'];

    public function payer(){
        return $this->belongsTo(Payer::class);
    }
}
