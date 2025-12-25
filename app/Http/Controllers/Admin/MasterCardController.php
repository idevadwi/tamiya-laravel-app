<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Racer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterCardController extends Controller
{
    /**
     * Display a listing of all cards (global, not tournament-scoped).
     */
    public function index(Request $request)
    {
        // Build query for all cards
        $query = Card::with('racer.team');

        // Filter by card code search
        if ($request->has('search') && $request->search) {
            $query->where('card_code', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by racer
        if ($request->has('racer_id') && $request->racer_id) {
            $query->where('racer_id', $request->racer_id);
        }

        $cards = $query->latest()->paginate(15);
        $cards->appends($request->query());

        // Get all racers for filter dropdown
        $racers = Racer::with('team')->orderBy('racer_name')->get();

        return view('admin.cards.index', compact('cards', 'racers'));
    }

    /**
     * Show the form for creating a new card.
     */
    public function create()
    {
        // Get all racers for selection
        $racers = Racer::with('team')->orderBy('racer_name')->get();

        return view('admin.cards.create', compact('racers'));
    }

    /**
     * Store a newly created card (global, can be assigned to any racer).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_code' => 'required|string|max:255|unique:cards,card_code',
            'racer_id' => 'nullable|exists:racers,id',
            'status' => 'required|in:ACTIVE,LOST,BANNED',
            'coupon' => 'nullable|integer|min:0',
        ]);

        // Create the card
        $card = Card::create([
            'id' => Str::uuid(),
            'card_code' => $validated['card_code'],
            'racer_id' => $validated['racer_id'] ?? null,
            'status' => $validated['status'],
            'coupon' => $validated['coupon'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.cards.index')
            ->with('success', 'Card created successfully in master data.');
    }

    /**
     * Display the specified card.
     */
    public function show(Card $card)
    {
        $card->load(['racer.team', 'couponHistory']);

        return view('admin.cards.show', compact('card'));
    }

    /**
     * Show the form for editing the specified card.
     */
    public function edit(Card $card)
    {
        // Get all racers for selection
        $racers = Racer::with('team')->orderBy('racer_name')->get();

        return view('admin.cards.edit', compact('card', 'racers'));
    }

    /**
     * Update the specified card.
     */
    public function update(Request $request, Card $card)
    {
        $validated = $request->validate([
            'card_code' => 'required|string|max:255|unique:cards,card_code,' . $card->id,
            'racer_id' => 'nullable|exists:racers,id',
            'status' => 'required|in:ACTIVE,LOST,BANNED',
            'coupon' => 'nullable|integer|min:0',
        ]);

        $validated['updated_by'] = auth()->id();
        $card->update($validated);

        return redirect()->route('admin.cards.index')
            ->with('success', 'Card updated successfully.');
    }

    /**
     * Remove the specified card from the system (global delete).
     */
    public function destroy(Card $card)
    {
        $cardCode = $card->card_code;
        $card->delete();

        return redirect()->route('admin.cards.index')
            ->with('success', "Card '{$cardCode}' deleted successfully.");
    }

    /**
     * Show the form for bulk creating cards.
     */
    public function bulkCreate()
    {
        return view('admin.cards.bulk-create');
    }

    /**
     * Store bulk created cards.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'card_codes' => 'required|string',
            'status' => 'required|in:ACTIVE,LOST,BANNED',
        ]);

        // Split card codes by new line or comma
        $cardCodes = preg_split('/[\r\n,]+/', $validated['card_codes']);
        $cardCodes = array_filter(array_map('trim', $cardCodes));

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($cardCodes as $cardCode) {
            // Check if card already exists
            if (Card::where('card_code', $cardCode)->exists()) {
                $skipped++;
                $errors[] = "Card '{$cardCode}' already exists";
                continue;
            }

            Card::create([
                'id' => Str::uuid(),
                'card_code' => $cardCode,
                'racer_id' => null,
                'status' => $validated['status'],
                'coupon' => 0,
                'created_by' => auth()->id(),
            ]);

            $created++;
        }

        $message = "Created {$created} card(s).";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} duplicate(s).";
        }

        return redirect()->route('admin.cards.index')
            ->with('success', $message)
            ->with('errors', $errors);
    }
}
