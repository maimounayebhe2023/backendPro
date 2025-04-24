<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conducteur extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'categorie_id', 'prenom', 'tel'];

    public function categorie() {
        return $this->belongsTo(Categorie::class);
    } 

    public function enregistrement () {
        return $this->hasMany(Enregistrement::class);
    }
    public $timestamps = false ;
}
