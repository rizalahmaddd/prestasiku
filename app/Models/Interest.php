<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\Orderable;
use Database\Factories\InterestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use Auditable, Orderable;

    /** @use HasFactory<InterestFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'icon', 'tone', 'tags', 'sort_order'];

    protected function casts(): array
    {
        return ['tags' => 'array'];
    }
}
