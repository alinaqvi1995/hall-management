<?php

namespace App\Http\Controllers;

use App\Http\Requests\HallRequest;
use App\Models\Hall;
use App\Models\State;
use App\Services\BookingService;
use App\Services\HallService;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;

class HallController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct(
        protected HallService $service,
        protected BookingService $bookings,
    ) {
        $this->middleware('permission:view-halls')->only(['index', 'show', 'lawns']);
        $this->middleware('permission:create-halls')->only(['create', 'store']);
        $this->middleware('permission:edit-halls')->only(['edit', 'update']);
        $this->middleware('permission:delete-halls')->only(['destroy']);
    }

    public function index()
    {
        $this->authorize('viewAny', Hall::class);

        // visibleTo() returns a single hall for a hall admin and every hall for
        // a super admin, so both roles use the same query.
        $halls = Hall::visibleTo()
            ->withCount(['lawns', 'users', 'bookings'])
            ->orderBy('name')
            ->get();

        return view('halls.index', compact('halls'));
    }

    public function show(Hall $hall)
    {
        $this->authorize('view', $hall);

        $hall->load(['lawns', 'users.roles', 'packages', 'addons', 'stateRelation', 'cityRelation'])
            ->loadCount(['bookings', 'lawns']);

        return view('halls.show', [
            'hall' => $hall,
            'upcoming' => $hall->bookings()
                ->with(['customer', 'lawn'])
                ->where('status', '!=', 'cancelled')
                ->where('start_datetime', '>=', now())
                ->orderBy('start_datetime')
                ->limit(5)
                ->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Hall::class);

        return view('halls.create', [
            'states' => State::orderBy('name')->get(),
            'hall' => null,
        ]);
    }

    public function store(HallRequest $request)
    {
        $this->authorize('create', Hall::class);

        $data = $request->hallData();
        $data['logo'] = $this->service->storeLogo($request->file('logo'));

        $hall = $this->service->createWithLawns($data, $request->lawnRows());

        return redirect()->route('halls.show', $hall)
            ->with('success', 'Hall created successfully.');
    }

    public function edit(Hall $hall)
    {
        $this->authorize('update', $hall);

        $hall->load('lawns');

        return view('halls.edit', [
            'hall' => $hall,
            'lawns' => $hall->lawns,
            'states' => State::orderBy('name')->get(),
        ]);
    }

    public function update(HallRequest $request, Hall $hall)
    {
        $this->authorize('update', $hall);

        $data = $request->hallData();

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->service->storeLogo($request->file('logo'), $hall->logo);
        }

        $this->service->updateWithLawns($hall, $data, $request->lawnRows());

        return redirect()->route('halls.show', $hall)
            ->with('success', 'Hall updated successfully.');
    }

    public function destroy(Hall $hall)
    {
        $this->authorize('delete', $hall);

        // Refuse to remove a venue that still has live events on the books.
        $live = $hall->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('end_datetime', '>=', now())
            ->count();

        if ($live > 0) {
            return back()->with('error',
                "This hall has {$live} upcoming booking(s). Cancel or complete them before deleting it.");
        }

        $this->service->delete($hall);

        return redirect()->route('halls.index')->with('success', 'Hall deleted successfully.');
    }

    /**
     * Lawn availability for the booking form's lawn picker.
     */
    public function lawns(Hall $hall, Request $request)
    {
        $this->authorize('view', $hall);

        return response()->json(
            $this->bookings->lawnAvailability(
                $hall,
                $request->query('start'),
                $request->query('end'),
                $request->integer('ignore') ?: null
            )->values()
        );
    }
}
