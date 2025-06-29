<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom'
    ];

    public function conducteurs () {
        return $this->hasMany(Conducteur::class);
    }
    public $timestamps = false ;
}
