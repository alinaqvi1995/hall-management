<?php
namespace App\Http\Controllers;

use App\Http\Requests\HallRequest;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\Lawn;
use App\Services\HallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HallController extends Controller
{
    protected $service;

    public function __construct(HallService $service)
    {
        $this->service = $service;

        // $permissions = [
        //     'index'   => 'view-halls',
        //     'store'   => 'create-halls',
        //     'update'  => 'edit-halls',
        //     'destroy' => 'delete-halls',
        // ];

        // foreach ($permissions as $method => $permission) {
        //     $this->middleware("permission:{$permission}")->only($method);
        // }
    }

    public function index()
    {
        $this->authorize('viewAny', Hall::class);

        if (auth()->user()->hasRole('hall_admin')) {
            // hall admin sees only their hall
            $halls = [Hall::find(auth()->user()->hall_id)];
        } else {
            // super admin sees all halls
            $halls = $this->service->list();
        }

        return view('halls.index', compact('halls'));
    }
    // public function index()
    // {
    //     $this->authorize('viewAny', Hall::class);
    //     $halls = $this->service->list();
    //     return view('halls.index', compact('halls'));
    // }

    public function show(int $id)
    {
        $currentUser = Auth::user();
        $hall        = $this->service->find($id);

        if (! $hall) {
            abort(404, 'Hall not found');
        }

        if ($currentUser->isHallAdmin() && $hall->id != $currentUser->hall_id) {
            abort(403, 'Unauthorized');
        }

        return view('halls.show', compact('hall'));
    }

    public function create()
    {
        $currentUser = Auth::user();

        // Only super admin can create
        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        return view('halls.create');
    }

    public function store(HallRequest $request)
    {
        $currentUser = Auth::user();

        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $this->service->createWithLawns($request->validated(), $request->lawns ?? []);

        return redirect()->route('halls.index')->with('success', 'Hall created successfully.');
    }

    // public function store(HallRequest $request)
    // {
    //     $currentUser = Auth::user();

    //     if (! $currentUser->isSuperAdmin()) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $this->service->create($request->validated());
    //     return redirect()->route('halls.index')->with('success', 'Hall created successfully.');
    // }

    public function edit(Hall $hall)
    {
        $currentUser = Auth::user();

        // Hall admin can edit only their hall, super admin can edit all
        if ($currentUser->isHallAdmin() && $hall->id != $currentUser->hall_id) {
            abort(403, 'Unauthorized');
        }

        $hall->load('lawns');
        $lawns = Lawn::where('hall_id', $hall->id)->get();

        return view('halls.edit', compact('hall', 'lawns'));
    }

    public function update(HallRequest $request, Hall $hall)
    {
        $currentUser = Auth::user();

        if ($currentUser->isHallAdmin() && $hall->id != $currentUser->hall_id) {
            abort(403, 'Unauthorized');
        }

        $data  = $request->validated();
        $lawns = $data['lawns'] ?? [];
        unset($data['lawns']); // remove lawns from hall update

        $this->service->updateWithLawns($hall, $data, $lawns);

        if ($currentUser->isSuperAdmin()) {
            return redirect()->route('halls.index')->with('success', 'Hall updated successfully.');
        } else {
            return back()->with('success', 'Hall updated successfully.');
        }
    }

    // public function update(HallRequest $request, Hall $hall)
    // {
    //     $currentUser = Auth::user();

    //     if ($currentUser->isHallAdmin() && $hall->id != $currentUser->hall_id) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $this->service->update($hall, $request->validated());

    //     if ($currentUser->isSuperAdmin()) {
    //         return redirect()->route('halls.index')->with('success', 'Hall updated successfully.');
    //     } else {
    //         return back()->with('success', 'Hall updated successfully.');
    //     }
    // }

    public function destroy(Hall $hall)
    {
        $currentUser = Auth::user();

        // Only super admin can delete
        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $this->service->delete($hall);
        return redirect()->route('halls.index')->with('success', 'Hall deleted successfully.');
    }

    public function lawns(Hall $hall, Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $lawns = $hall->lawns()->select('id', 'name', 'capacity')->get();

        if ($start && $end) {
            $startDate = \Carbon\Carbon::parse($start);
            $endDate   = \Carbon\Carbon::parse($end);

            $lawns->transform(function ($lawn) use ($startDate, $endDate) {
                // Check if any booking overlaps in time
                $booking = Booking::where('lawn_id', $lawn->id)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->where('start_datetime', '<', $endDate)
                            ->where('end_datetime', '>', $startDate);
                    })
                    ->first();

                if ($booking) {
                    $lawn->available   = false;
                    $lawn->booked_from = $booking->start_datetime->format('d M Y h:i A');
                    $lawn->booked_to   = $booking->end_datetime->format('d M Y h:i A');
                } else {
                    $lawn->available   = true;
                    $lawn->booked_from = null;
                    $lawn->booked_to   = null;
                }

                return $lawn;
            });
        } else {
            $lawns->transform(function ($lawn) {
                $lawn->available   = true;
                $lawn->booked_from = null;
                $lawn->booked_to   = null;
                return $lawn;
            });
        }

        return response()->json($lawns);
    }
}
