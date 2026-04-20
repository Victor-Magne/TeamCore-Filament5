<?php

namespace App\Models;

use App\Casts\ValidateUtf8String;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Country extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'code', 'phonecode'];

    protected $casts = [
        'name' => ValidateUtf8String::class,
        'code' => ValidateUtf8String::class,
        'phonecode' => ValidateUtf8String::class,
    ];

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
