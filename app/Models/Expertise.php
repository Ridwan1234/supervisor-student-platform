<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    protected $guarded = ['id', 'created_at'];

    public function supervisor()
    {
        return $this->belongsTo('supervisor_id');
    }
}
