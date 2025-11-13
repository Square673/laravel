<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SlotController extends Controller
{
    public function getSlots($questId, $date)
    {
        $quest = DB::table('quests')->where('id', $questId)->first();
        if (!$quest) {
            return response()->json(['slots' => [], 'taken' => []]);
        }

        // Определение шага между слотами
        $duration = (int) $quest->duration;
        $interval = $duration < 75 ? 90 : 120; // шаг между слотами
        $openTime = strtotime("$date 10:00");
        $closeTime = strtotime("$date 23:59");

        $slots = [];
        $current = $openTime;

        // Генерация списка слотов
        while ($current + ($duration * 60) <= $closeTime) {
            $slots[] = date('H:i', $current);
            $current += $interval * 60;
        }

        // Определение занятых слотов
        $bookings = DB::table('bookings')
            ->where('quest_id', $quest->id)
            ->where('date', $date)
            ->where('status', '!=', 'canceled')
            ->pluck('time')
            ->toArray();

        return response()->json([
            'slots' => $slots,
            'taken' => $bookings,
        ]);
    }
}
