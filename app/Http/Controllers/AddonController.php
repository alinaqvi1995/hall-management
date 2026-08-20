<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddonRequest;
use App\Models\Addon;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddonController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct()
    {
        $this->middleware('permission:view-addons')->only(['index']);
        $this->middleware('permission:create-addons')->only(['store']);
        $this->middleware('permission:edit-addons')->only(['update']);
        $this->middleware('permission:delete-addons')->only(['destroy']);
    }

    public function index()
    {
        return view('addons.index', [
            'addons' => Addon::visibleTo()->with('hall:id,name')->orderBy('name')->get(),
            'halls' => $this->selectableHalls(),
        ]);
    }

    public function store(AddonRequest $request)
    {
        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active', true);

        Addon::create($data);

        return back()->with('success', 'Service added successfully.');
    }

    public function update(AddonRequest $request, Addon $addon)
    {
        $this->authorizeHallAccess($addon);

        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active');

        $addon->update($data);

        return back()->with('success', 'Service updated successfully.');
    }

    public function destroy(Addon $addon)
    {
        $this->authorizeHallAccess($addon);

        // Priced lines already attached to a booking are preserved by the pivot,
        // so a soft delete here does not rewrite any existing bill.
        $addon->delete();

        return back()->with('success', 'Service removed successfully.');
    }

    /** Add-on catalogue for a hall, used by the booking form. */
    public function forHall(Request $request)
    {
        $hallId = $request->integer('hall_id');

        abort_unless($hallId, 422);

        $user = Auth::user();
        abort_unless($user->isSuperAdmin() || (int) $user->hall_id === $hallId, 403);

        return response()->json(
            Addon::where('hall_id', $hallId)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'price', 'pricing_mode'])
        );
    }
}
