<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScannerDevice;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScannerDeviceController extends Controller
{
    /**
     * Display a listing of all scanner devices.
     */
    public function index(Request $request)
    {
        $query = ScannerDevice::with('tournament');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('device_code', 'like', '%' . $request->search . '%')
                  ->orWhere('device_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->linked === 'yes') {
            $query->whereNotNull('tournament_id');
        } elseif ($request->linked === 'no') {
            $query->whereNull('tournament_id');
        }

        $devices = $query->latest()->paginate(15);
        $devices->appends($request->query());

        return view('admin.scanner-devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new scanner device.
     */
    public function create()
    {
        $tournaments = Tournament::where('status', 'ACTIVE')->orderBy('tournament_name')->get();
        return view('admin.scanner-devices.create', compact('tournaments'));
    }

    /**
     * Store a newly created scanner device.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_code' => 'required|string|max:255|unique:scanner_devices,device_code',
            'device_name' => 'required|string|max:255',
            'tournament_id' => 'nullable|uuid|exists:tournaments,id',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE',
            'firmware_version' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $device = ScannerDevice::create([
            'id' => Str::uuid(),
            'device_code' => $validated['device_code'],
            'device_name' => $validated['device_name'],
            'tournament_id' => $validated['tournament_id'],
            'status' => $validated['status'],
            'firmware_version' => $validated['firmware_version'],
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.scanner-devices.show', $device->id)
            ->with('success', 'Scanner device registered successfully.');
    }

    /**
     * Display the specified scanner device.
     */
    public function show(ScannerDevice $scannerDevice)
    {
        $scannerDevice->load('tournament');
        $tournaments = Tournament::where('status', 'ACTIVE')->orderBy('tournament_name')->get();
        return view('admin.scanner-devices.show', compact('scannerDevice', 'tournaments'));
    }

    /**
     * Show the form for editing the specified scanner device.
     */
    public function edit(ScannerDevice $scannerDevice)
    {
        $tournaments = Tournament::where('status', 'ACTIVE')->orderBy('tournament_name')->get();
        return view('admin.scanner-devices.edit', compact('scannerDevice', 'tournaments'));
    }

    /**
     * Update the specified scanner device.
     */
    public function update(Request $request, ScannerDevice $scannerDevice)
    {
        $validated = $request->validate([
            'device_code' => 'required|string|max:255|unique:scanner_devices,device_code,' . $scannerDevice->id,
            'device_name' => 'required|string|max:255',
            'tournament_id' => 'nullable|uuid|exists:tournaments,id',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE',
            'firmware_version' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $scannerDevice->update([
            'device_code' => $validated['device_code'],
            'device_name' => $validated['device_name'],
            'tournament_id' => $validated['tournament_id'],
            'status' => $validated['status'],
            'firmware_version' => $validated['firmware_version'],
            'notes' => $validated['notes'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.scanner-devices.index')
            ->with('success', 'Scanner device updated successfully.');
    }

    /**
     * Remove the specified scanner device.
     */
    public function destroy(ScannerDevice $scannerDevice)
    {
        $deviceName = $scannerDevice->device_name;
        $scannerDevice->delete();

        return redirect()->route('admin.scanner-devices.index')
            ->with('success', "Scanner device '{$deviceName}' deleted successfully.");
    }

    /**
     * Quick action: Link device to tournament.
     */
    public function link(Request $request, ScannerDevice $scannerDevice)
    {
        $validated = $request->validate([
            'tournament_id' => 'required|uuid|exists:tournaments,id',
        ]);

        $scannerDevice->update([
            'tournament_id' => $validated['tournament_id'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Device linked to tournament.');
    }

    /**
     * Quick action: Unlink device from tournament.
     */
    public function unlink(ScannerDevice $scannerDevice)
    {
        $scannerDevice->update([
            'tournament_id' => null,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Device unlinked from tournament.');
    }
}
