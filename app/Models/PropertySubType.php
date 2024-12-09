<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertySubType extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'propertytype_id',
        'old_id',
        'code',
        'description',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'propertytype_id', 'id');
    }
}
