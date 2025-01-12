<?php

namespace App\Http\Controllers;


use App\Models\Expertise;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ExpertiseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'expertise_area' => 'required|string|max:255',
        ]);

        Expertise::create([
            'supervisor_id' => 1,
            'expertise_area' => $request->expertise_area,
        ]);

        return response()->json(['message' => 'Expertise added successfully.']);
    }

    public function index(Request $request)
    {
        $expertise = Expertise::where('supervisor_id', 1)->get();
        return response()->json($expertise);
    }

    public function destroy($id)
    {
        $expertise = Expertise::findOrFail($id);
        $expertise->delete();

        return response()->json(['message' => 'Expertise deleted successfully.']);
    }
}

