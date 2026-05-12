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
        'claimed_at',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    protected $appends = ['created_by_name', 'updated_by_name'];

    public function getCreatedByNameAttribute()
    {
        return $this->creator?->name;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function getUpdatedByNameAttribute()
    {
        return $this->updater?->name;
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
