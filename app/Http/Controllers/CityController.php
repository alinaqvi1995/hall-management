<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-cities')->only(['index']);
        $this->middleware('permission:create-cities')->only(['store']);
        $this->middleware('permission:edit-cities')->only(['update']);
        $this->middleware('permission:delete-cities')->only(['destroy']);
    }
    public function index()
    {
        // Eager load state for all cities
        $cities = City::with('state')->orderBy('name', 'asc')->get();
        $states = State::orderBy('name', 'asc')->get(); // for modal dropdown
        return view('cities.index', compact('cities', 'states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
        ]);

        City::create([
            'state_id' => $request->state_id,
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'City added successfully.');
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
        ]);

        $city->update([
            'state_id' => $request->state_id,
            'name' => $request->name,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return back()->with('success', 'City deleted successfully.');
    }
}
