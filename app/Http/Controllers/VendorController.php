<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorRequest;
use App\Models\Vendor;
use App\Traits\ResolvesCurrentHall;

class VendorController extends Controller
{
    use ResolvesCurrentHall;

    public function __construct()
    {
        $this->middleware('permission:view-vendors')->only(['index']);
        $this->middleware('permission:create-vendors')->only(['store']);
        $this->middleware('permission:edit-vendors')->only(['update']);
        $this->middleware('permission:delete-vendors')->only(['destroy']);
    }

    public function index()
    {
        return view('vendors.index', [
            'vendors' => Vendor::visibleTo()
                ->with('hall:id,name')
                ->withSum('expenses', 'amount')
                ->orderBy('name')
                ->get(),
            'halls' => $this->selectableHalls(),
        ]);
    }

    public function store(VendorRequest $request)
    {
        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active', true);

        Vendor::create($data);

        return back()->with('success', 'Vendor added successfully.');
    }

    public function update(VendorRequest $request, Vendor $vendor)
    {
        $this->authorizeHallAccess($vendor);

        $data = $request->validated();
        $data['hall_id'] = $this->hallIdForWrite($data['hall_id']);
        $data['is_active'] = $request->boolean('is_active');

        $vendor->update($data);

        return back()->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $this->authorizeHallAccess($vendor);

        // Expenses keep pointing at the soft-deleted vendor, so historic
        // spending reports stay intact.
        $vendor->delete();

        return back()->with('success', 'Vendor removed successfully.');
    }
}
