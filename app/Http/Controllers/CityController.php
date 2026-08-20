<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        // creator/updater are shown per row; without them each row queried users.
        $cities = City::with(['state', 'creator:id,name', 'updater:id,name'])
            ->orderBy('name')
            ->get();
        $states = State::orderBy('name', 'asc')->get(); // for modal dropdown
        return view('cities.index', compact('cities', 'states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            // A city name only has to be unique inside its own province.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('cities', 'name')->where(fn ($q) => $q->where('state_id', $request->state_id)),
            ],
        ]);

        City::create([
            'state_id' => $request->state_id,
            'name' => $request->name,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'City added successfully.');
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('cities', 'name')
                    ->where(fn ($q) => $q->where('state_id', $request->state_id))
                    ->ignore($city->id),
            ],
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
        // Halls point at this city, so removing it would blank their location.
        if ($city->halls()->exists()) {
            return back()->with('error',
                'This city is used by '.$city->halls()->count().' hall(s) and cannot be deleted.');
        }

        $city->delete();

        return back()->with('success', 'City deleted successfully.');
    }
}
