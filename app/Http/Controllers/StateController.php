<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-states')->only(['index']);
        $this->middleware('permission:create-states')->only(['store']);
        $this->middleware('permission:edit-states')->only(['update']);
        $this->middleware('permission:delete-states')->only(['destroy']);
    }
    public function index()
    {
        // Get all states for table and modal dropdowns
        $states = State::with(['creator:id,name', 'updater:id,name'])
            ->withCount('cities')
            ->orderBy('name')
            ->get();
        return view('states.index', compact('states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name',
        ]);

        State::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'State created successfully.');
    }

    public function update(Request $request, State $state)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name,' . $state->id,
        ]);

        $state->update([
            'name' => $request->name,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'State updated successfully.');
    }

    public function destroy(State $state)
    {
        // Deleting a province cascades to its cities, which halls may reference.
        if ($state->halls()->exists()) {
            return back()->with('error',
                'This province is used by '.$state->halls()->count().' hall(s) and cannot be deleted.');
        }

        $state->delete();

        return back()->with('success', 'Province deleted successfully.');
    }
}
