<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'causer_type',
        'causer_id',
        'attribute_changes',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'attribute_changes' => 'json',
            'properties' => 'json',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    public function causer(): MorphTo
    {
        return $this->morphTo('causer');
    }
}
