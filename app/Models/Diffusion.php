<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diffusion extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'input' => AsArrayObject::class,
        'output' => AsArrayObject::class,
        'error' => AsArrayObject::class,
    ];

    protected $fillable = [
        'user_id',
        'style',
        'input'
    ];

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_STARTING = 'STARTING';
    public const STATUS_RUNNING = 'RUNNING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_FAILED = 'FAILED';

    public const PRIVACY_PRIVATE = 'PRIVATE';
    public const PRIVACY_UNLISTED = 'UNLISTED';
    public const PRIVACY_PUBLIC = 'PUBLIC';

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
