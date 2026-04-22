<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = [
        'patient_id',
        'medicines_binary',
        'issued_by',
        'expires_at',
    ];
    
    protected $appends = ['issued_by_name'];

    public function getIssuedByNameAttribute()
    {
        return $this->issuer?->name;
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
