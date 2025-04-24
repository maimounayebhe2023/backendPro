<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enregistrement extends Model
{
    use HasFactory;

    protected $fillable = ['conducteur_id', 'engin_id', 'code_pin', 'date_sortie'];

    public function conducteur()
    {
        return $this->belongsTo(Conducteur::class);
    }

    public function engin()
    {
        return $this->belongsTo(Engin::class);
    }


}
