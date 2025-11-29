<?php
namespace App\Http\Controllers;

use App\Http\Requests\HallRequest;
use App\Models\Hall;
use App\Services\HallService;
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

        $this->service->create($request->validated());
        return redirect()->route('halls.index')->with('success', 'Hall created successfully.');
    }

    public function edit(Hall $hall)
    {
        $currentUser = Auth::user();

        // Hall admin can edit only their hall, super admin can edit all
        if ($currentUser->isHallAdmin() && $hall->id != $currentUser->hall_id) {
            abort(403, 'Unauthorized');
        }

        return view('halls.edit', compact('hall'));
    }

    public function update(HallRequest $request, Hall $hall)
    {
        $currentUser = Auth::user();

        if ($currentUser->isHallAdmin() && $hall->id != $currentUser->hall_id) {
            abort(403, 'Unauthorized');
        }

        $this->service->update($hall, $request->validated());

        if ($currentUser->isSuperAdmin()) {
            return redirect()->route('halls.index')->with('success', 'Hall updated successfully.');
        } else {
            return back()->with('success', 'Hall updated successfully.');
        }
    }

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
}
