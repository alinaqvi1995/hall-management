<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Models\Staff;
use App\Traits\ResolvesCurrentHall;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct()
    {
        $this->middleware('permission:view-staff')->only(['index', 'show']);
        $this->middleware('permission:create-staff')->only(['create', 'store']);
        $this->middleware('permission:edit-staff')->only(['edit', 'update']);
        $this->middleware('permission:delete-staff')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $staff = Staff::visibleTo()
            ->with('hall:id,name')
            ->when($request->filled('employment_type'),
                fn ($q) => $q->where('employment_type', $request->string('employment_type')))
            ->when($request->filled('status'),
                fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->orderBy('name')
            ->get();

        return view('staff.index', [
            'staff' => $staff,
            'monthlyPayroll' => $staff->where('is_active', true)->sum('monthly_salary'),
        ]);
    }

    public function create()
    {
        return view('staff.form', [
            'member' => new Staff(['is_active' => true, 'employment_type' => 'permanent']),
            'halls' => $this->selectableHalls(),
        ]);
    }

    public function store(StaffRequest $request)
    {
        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active', true);

        Staff::create($data);

        return redirect()->route('staff.index')->with('success', 'Staff member added successfully.');
    }

    public function show(Staff $member)
    {
        $this->authorizeHallAccess($member);

        $member->load(['hall:id,name']);

        return view('staff.show', [
            'member' => $member,
            'assignments' => $member->bookings()
                ->with(['customer:id,name', 'lawn:id,name'])
                ->orderByDesc('start_datetime')
                ->limit(20)
                ->get(),
        ]);
    }

    public function edit(Staff $member)
    {
        $this->authorizeHallAccess($member);

        return view('staff.form', [
            'member' => $member,
            'halls' => $this->selectableHalls(),
        ]);
    }

    public function update(StaffRequest $request, Staff $member)
    {
        $this->authorizeHallAccess($member);

        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active');

        $member->update($data);

        return redirect()->route('staff.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(Staff $member)
    {
        $this->authorizeHallAccess($member);

        $member->delete();

        return redirect()->route('staff.index')->with('success', 'Staff member removed successfully.');
    }
}
