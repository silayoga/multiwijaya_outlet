<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingSpec extends Model
{
    use HasFactory;

    protected $fillable = ['listing_id', 'spec_key', 'spec_value', 'sort_order'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
