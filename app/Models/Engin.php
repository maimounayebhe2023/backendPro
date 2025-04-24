<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engin extends Model
{
    use HasFactory;

    protected $fillable = [
        'plaque_immatricu' ,
        'type_engin'
    ];

    public function enregistrement () {
        return $this->hasMany(Enregistrement::class);
    }
    
    public $timestamps = false ;
}
