<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\Booking;

class SlotController extends Controller
{
    public function getSlots($questId, $date)
    {
        $quest = Quest::find($questId);

        if (!$quest) {
            return response()->json([
                'slots' => [],
                'taken' => []
            ]);
        }

        // Длительность квеста
        $duration = (int) $quest->duration;

        // Интервал между стартами слотов
        $interval = $duration < 75 ? 90 : 120;

        $openTime  = strtotime("$date 10:00");
        $closeTime = strtotime("$date 23:59");

        $slots = [];
        $current = $openTime;

        // Генерация доступных слотов
        while ($current + ($duration * 60) <= $closeTime) {
            $slots[] = date('H:i', $current);
            $current += $interval * 60;
        }

        // Занятые слоты
        $taken = Booking::where('quest_id', $quest->id)
            ->where('date', $date)
            ->where('status', '!=', 'canceled')
            ->pluck('time')
            ->toArray();

        return response()->json([
            'slots' => $slots,
            'taken' => $taken,
        ]);
    }
}
