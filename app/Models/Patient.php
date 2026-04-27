<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    public function sex()
    {
        return $this->belongsTo(Sex::class);
    }

        /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    protected $fillable = [
        'scan_id',
        'last_name',
        'first_name',
        'middle_name',
        'birthdate',
        'barangay',
        'sex_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];
}
