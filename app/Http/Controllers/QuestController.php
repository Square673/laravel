<?php

namespace App\Http\Controllers;

use App\Models\Quest;

class QuestController extends Controller
{
    // Страница со списком всех квестов
    public function index()
    {
        $quests = Quest::all();
        return view('quests.index', compact('quests'));
    }

    // Страница одного квеста
    public function show($id)
    {
        $quest = Quest::findOrFail($id); // выбросит 404 если нет
        return view('quests.show', compact('quest'));
    }
}
    