<?php
namespace Services;

use DateTime;
use DateTimeZone;
use DateInterval;

class DeliveryEstimationService {
    private DateTimeZone $tz;

    public function __construct() {
        $this->tz = new DateTimeZone('Asia/Kolkata');
    }

    /**
     * Calculate estimated delivery dates for a pincode or default days
     */
    public function estimate(int $deliveryDays = 0, ?string $pincode = null): array {
        $now = new DateTime('now', $this->tz);
        
        $minDays = (int)get_setting('delivery_default_min_days', 3);
        $maxDays = (int)get_setting('delivery_default_max_days', 5);
        $skipWeekends = (bool)get_setting('delivery_skip_weekends', 1);
        $holidaysStr = get_setting('delivery_holiday_dates', '');
        $holidays = array_filter(array_map('trim', explode(',', $holidaysStr)));

        if ($deliveryDays > 0) {
            $minDays = max(1, $deliveryDays - 1);
            $maxDays = $deliveryDays + 1;
        }

        $minDate = $this->addBusinessDays(clone $now, $minDays, $skipWeekends, $holidays);
        $maxDate = $this->addBusinessDays(clone $now, $maxDays, $skipWeekends, $holidays);

        $sameMonth = ($minDate->format('M') === $maxDate->format('M'));
        $formatted = $sameMonth
            ? $minDate->format('j') . '–' . $maxDate->format('j M')
            : $minDate->format('j M') . ' – ' . $maxDate->format('j M');

        return [
            'min_days' => $minDays,
            'max_days' => $maxDays,
            'min_date' => $minDate->format('Y-m-d'),
            'max_date' => $maxDate->format('Y-m-d'),
            'formatted_dates' => $formatted,
            'days_string' => "{$minDays}–{$maxDays} business days",
            'pincode' => $pincode
        ];
    }

    private function addBusinessDays(DateTime $date, int $days, bool $skipWeekends, array $holidays): DateTime {
        $added = 0;
        while ($added < $days) {
            $date->add(new DateInterval('P1D'));
            $dayOfWeek = (int)$date->format('N'); // 1 (Mon) to 7 (Sun)
            $dateYmd = $date->format('Y-m-d');

            if ($skipWeekends && $dayOfWeek === 7) {
                continue; // Skip Sunday
            }
            if (in_array($dateYmd, $holidays)) {
                continue; // Skip Holiday
            }
            $added++;
        }
        return $date;
    }
}
