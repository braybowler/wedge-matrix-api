<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WedgeMatrix extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'label',
        'number_of_rows',
        'number_of_columns',
        'column_headers',
        'club_labels',
        'selected_row_display_option',
        'yardage_values',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'column_headers' => 'array',
            'club_labels' => 'array',
            'yardage_values' => 'array',
        ];
    }
}
