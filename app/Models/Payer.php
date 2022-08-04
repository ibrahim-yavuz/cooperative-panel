<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payer extends Model
{
    use HasFactory;

    protected $fillable = ['first_name', 'second_name', 'shares', 'phone_number'];

    public function pay_records(){
        return $this->hasMany(PayRecord::class);
    }
}
