<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreHour;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public const SLOT_MINUTES = 30;

    public function leadTimeHours(Store $store): int
    {
        $store->loadMissing('products');

        $max = (int) $store->products
            ->where('is_active', true)
            ->max(fn ($product) => $product->lead_time_hours ?: $store->lead_time_hours);

        return max(1, $max ?: $store->lead_time_hours);
    }

    public function earliestAt(Store $store): Carbon
    {
        return now()->addHours($this->leadTimeHours($store));
    }

    public function earliestDate(Store $store): Carbon
    {
        return $this->earliestAt($store)->copy()->startOfDay();
    }

    /** @return array<int, array{closed: bool, open: ?string, close: ?string}> */
    public function hours(Store $store): array
    {
        $store->loadMissing('hours');

        $hours = [];

        for ($day = 0; $day <= 6; $day++) {
            /** @var StoreHour|null $row */
            $row = $store->hours->firstWhere('day_of_week', $day);

            $hours[$day] = [
                'closed' => $row?->is_closed ?? true,
                'open' => $row?->open_time ? substr($row->open_time, 0, 5) : null,
                'close' => $row?->close_time ? substr($row->close_time, 0, 5) : null,
            ];
        }

        return $hours;
    }

    /** @return array<int, string> */
    public function blockedDates(Store $store): array
    {
        return $store->blockedDates()
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->pluck('date')
            ->map(fn (Carbon $date) => $date->toDateString())
            ->all();
    }

    /** @return array<int, string> */
    public function slots(Store $store): array
    {
        $hours = collect($this->hours($store))->where('closed', false);

        $opens = $hours->pluck('open')->filter();
        $closes = $hours->pluck('close')->filter();

        $open = $opens->isNotEmpty() ? $opens->map(fn ($time) => $this->minutes($time))->min() : 10 * 60;
        $close = $closes->isNotEmpty() ? $closes->map(fn ($time) => $this->minutes($time))->max() : 20 * 60;

        $slots = [];

        for ($minute = $open; $minute <= $close - self::SLOT_MINUTES; $minute += self::SLOT_MINUTES) {
            $slots[] = sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
        }

        return $slots;
    }

    /**
     * @return array{earliestDate: string, earliestTime: string, blocked: array<int, string>, hours: array<int, array{closed: bool, open: ?string, close: ?string}>, slots: array<int, string>, leadHours: int}
     */
    public function payload(Store $store): array
    {
        $earliest = $this->earliestAt($store);

        return [
            'earliestDate' => $earliest->toDateString(),
            'earliestTime' => $earliest->format('H:i'),
            'blocked' => $this->blockedDates($store),
            'hours' => $this->hours($store),
            'slots' => $this->slots($store),
            'leadHours' => $this->leadTimeHours($store),
        ];
    }

    /** @return array{0: string, 1: string} */
    public function defaults(Store $store): array
    {
        $earliest = $this->earliestAt($store);
        $blocked = $this->blockedDates($store);
        $hours = $this->hours($store);
        $slots = $this->slots($store);

        for ($offset = 0; $offset < 45; $offset++) {
            $day = $earliest->copy()->startOfDay()->addDays($offset);
            $dayHours = $hours[$day->dayOfWeek] ?? ['closed' => true];

            if ($dayHours['closed'] || in_array($day->toDateString(), $blocked, true)) {
                continue;
            }

            $minimum = $day->isSameDay($earliest) ? $earliest->format('H:i') : null;

            $slot = collect($slots)->first(fn ($time) => $time >= $dayHours['open']
                && $time <= $dayHours['close']
                && ($minimum === null || $time >= $minimum));

            if ($slot) {
                return [$day->toDateString(), $slot];
            }
        }

        return [$earliest->toDateString(), $slots[0] ?? '12:00'];
    }

    public function assertValid(Store $store, string $date, string $time): Carbon
    {
        $scheduled = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$time);

        if (! $scheduled) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'صيغة الموعد غير صحيحة.',
            ]);
        }

        $earliest = $this->earliestAt($store);

        if ($scheduled->lt($earliest)) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'أقل مدة تحضير هي '.$this->leadTimeHours($store).' ساعة. اختاري موعداً بعد '.$earliest->translatedFormat('j F، g:i A').'.',
            ]);
        }

        if (in_array($date, $this->blockedDates($store), true)) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'المتجر مغلق في هذا التاريخ. اختاري يوماً آخر.',
            ]);
        }

        $dayHours = $this->hours($store)[$scheduled->dayOfWeek] ?? ['closed' => true];

        if ($dayHours['closed']) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'المتجر مغلق في هذا اليوم من الأسبوع. اختاري يوماً آخر.',
            ]);
        }

        if (! in_array($time, $this->slots($store), true) || $time < $dayHours['open'] || $time > $dayHours['close']) {
            throw ValidationException::withMessages([
                'scheduled_time' => 'الوقت المختار خارج ساعات عمل المتجر ('.$dayHours['open'].' — '.$dayHours['close'].').',
            ]);
        }

        return $scheduled;
    }

    protected function minutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return $hour * 60 + $minute;
    }
}