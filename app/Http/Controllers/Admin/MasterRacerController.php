<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Racer;
use App\Models\Team;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MasterRacerController extends Controller
{
    /**
     * Display a listing of all racers (global, not tournament-scoped).
     */
    public function index(Request $request)
    {
        // Build query for all racers
        $query = Racer::with('team')->withCount(['cards', 'tournamentRacerParticipants']);

        // Filter by racer name search
        if ($request->has('search') && $request->search) {
            $query->where('racer_name', 'like', '%' . $request->search . '%');
        }

        // Filter by team
        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        $allowedSorts = ['racer_name', 'cards_count', 'tournament_racer_participants_count', 'created_at'];
        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $racers = $query->orderBy($sort, $direction)->paginate(15);
        $racers->appends($request->query());

        // Get all teams for filter dropdown
        $teams = Team::orderBy('team_name')->get();

        return view('admin.racers.index', compact('racers', 'teams'));
    }

    /**
     * Show the form for creating a new racer.
     */
    public function create()
    {
        // Get all teams for selection
        $teams = Team::orderBy('team_name')->get();

        // Get unassigned cards for selection (ordered by card_no)
        $cards = Card::whereNull('racer_id')
            ->whereNotNull('card_no')
            ->orderBy('card_no')
            ->get();

        return view('admin.racers.create', compact('teams', 'cards'));
    }

    /**
     * Store a newly created racer (global, can be assigned to any team).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'racer_name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'card_id' => 'nullable|exists:cards,id',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('racers', 'public');
        }

        // Create the racer
        $racer = Racer::create([
            'id' => Str::uuid(),
            'racer_name' => $validated['racer_name'],
            'team_id' => $validated['team_id'],
            'image' => $imagePath,
            'created_by' => auth()->id(),
        ]);

        // Assign selected card to racer
        if (!empty($validated['card_id'])) {
            Card::where('id', $validated['card_id'])
                ->whereNull('racer_id')
                ->update([
                    'racer_id' => $racer->id,
                    'updated_by' => auth()->id(),
                ]);
        }

        return redirect()->route('admin.racers.show', $racer->id)
            ->with('success', 'Racer created successfully in master data.');
    }

    /**
     * Display the specified racer with tournament participation history.
     */
    public function show(Racer $racer)
    {
        $racer->load(['team', 'cards', 'tournamentRacerParticipants.tournament']);

        return view('admin.racers.show', compact('racer'));
    }

    /**
     * Show the form for editing the specified racer.
     */
    public function edit(Racer $racer)
    {
        // Get all teams for selection
        $teams = Team::orderBy('team_name')->get();

        return view('admin.racers.edit', compact('racer', 'teams'));
    }

    /**
     * Update the specified racer.
     */
    public function update(Request $request, Racer $racer)
    {
        $validated = $request->validate([
            'racer_name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($racer->image) {
                Storage::disk('public')->delete($racer->image);
            }
            $validated['image'] = $request->file('image')->store('racers', 'public');
        }

        $validated['updated_by'] = auth()->id();
        $racer->update($validated);

        return redirect()->route('admin.racers.index')
            ->with('success', 'Racer updated successfully.');
    }

    /**
     * Remove the specified racer from the system (global delete).
     */
    public function destroy(Racer $racer)
    {
        // Check if racer is in any tournaments
        $tournamentCount = $racer->tournamentRacerParticipants()->count();

        if ($tournamentCount > 0) {
            return redirect()->route('admin.racers.index')
                ->with('error', "Cannot delete racer. They are participating in {$tournamentCount} tournament(s). Remove from tournaments first.");
        }

        $racerName = $racer->racer_name;

        // Delete image if exists
        if ($racer->image) {
            Storage::disk('public')->delete($racer->image);
        }

        $racer->delete();

        return redirect()->route('admin.racers.index')
            ->with('success', "Racer '{$racerName}' deleted successfully.");
    }
}
