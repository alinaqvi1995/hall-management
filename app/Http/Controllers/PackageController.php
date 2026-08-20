<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageRequest;
use App\Models\Package;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct()
    {
        $this->middleware('permission:view-packages')->only(['index', 'show']);
        $this->middleware('permission:create-packages')->only(['create', 'store']);
        $this->middleware('permission:edit-packages')->only(['edit', 'update']);
        $this->middleware('permission:delete-packages')->only(['destroy']);
    }

    public function index()
    {
        return view('packages.index', [
            'packages' => Package::visibleTo()
                ->with('hall:id,name')
                ->withCount('bookings')
                ->orderBy('name')
                ->get(),
            'halls' => $this->selectableHalls(),
        ]);
    }

    public function create()
    {
        return view('packages.form', [
            'package' => new Package(['is_active' => true, 'type' => 'buffet']),
            'halls' => $this->selectableHalls(),
        ]);
    }

    public function store(PackageRequest $request)
    {
        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active');
        $data['items'] = $this->cleanItems($data['items'] ?? []);
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        Package::create($data);

        return redirect()->route('packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(Package $package)
    {
        $this->authorizeHallAccess($package);

        return view('packages.form', [
            'package' => $package,
            'halls' => $this->selectableHalls(),
        ]);
    }

    public function update(PackageRequest $request, Package $package)
    {
        $this->authorizeHallAccess($package);

        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active');
        $data['items'] = $this->cleanItems($data['items'] ?? []);
        $data['updated_by'] = Auth::id();

        $package->update($data);

        return redirect()->route('packages.index')->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        $this->authorizeHallAccess($package);

        // Soft-deleting keeps historic bookings readable, but an active package
        // still attached to upcoming events should be deactivated instead.
        $upcoming = $package->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_datetime', '>=', now())
            ->count();

        if ($upcoming > 0) {
            return back()->with('error',
                "This package is used by {$upcoming} upcoming booking(s). Mark it inactive instead of deleting it.");
        }

        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Package deleted successfully.');
    }

    /** Menu lines for the package picker, used by the booking form. */
    public function forHall(Request $request)
    {
        $hallId = $request->integer('hall_id');

        abort_unless($hallId, 422);

        $user = Auth::user();
        abort_unless($user->isSuperAdmin() || (int) $user->hall_id === $hallId, 403);

        return response()->json(
            Package::where('hall_id', $hallId)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'per_head_rate', 'min_guests'])
        );
    }

    /** Drop blank rows the repeater may submit. */
    private function cleanItems(array $items): array
    {
        return array_values(array_filter(array_map('trim', $items), fn ($i) => $i !== ''));
    }
}
