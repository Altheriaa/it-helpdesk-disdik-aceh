<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class Division extends Model
{
    use HasFactory;

    /**
     * Get all clients (pegawai) in this division.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get all supports in this division.
     */
    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }
}
