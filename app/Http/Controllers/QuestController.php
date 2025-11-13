<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class QuestController extends Controller
{
    public function index() {
        $quests = DB::table('quests')->get();
        return view('quests.index', compact('quests'));
    }

    public function show($id) {
        $quest = DB::table('quests')->where('id', $id)->first();
        if (!$quest) abort(404);
        return view('quests.show', compact('quest'));
    }
}
