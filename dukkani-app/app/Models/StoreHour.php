<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreHour extends Model
{
    protected $fillable = ['store_id', 'day_of_week', 'open_time', 'close_time', 'is_closed'];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    public const DAYS = [
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function dayName(): string
    {
        return self::DAYS[$this->day_of_week] ?? '';
    }

    public function label(): string
    {
        if ($this->is_closed || ! $this->open_time) {
            return 'مغلق';
        }

        return substr($this->open_time, 0, 5).' — '.substr($this->close_time, 0, 5);
    }
}
