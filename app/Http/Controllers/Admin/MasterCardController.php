<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterCardController extends Controller
{
    /**
     * Display a listing of all cards (global, not tournament-scoped).
     */
    public function index(Request $request)
    {
        $query = Card::withCount('tournamentAssignments')
            ->with('tournamentAssignments');

        // Filter by card code / card no search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('card_code', 'like', '%' . $request->search . '%')
                  ->orWhere('card_no', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allowedSorts = ['card_no', 'card_code', 'status', 'created_at'];
        $sort      = in_array($request->sort, $allowedSorts) ? $request->sort : 'card_no';
        $direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'asc';

        $cards = $query->orderBy($sort, $direction)->paginate(15);
        $cards->appends($request->query());

        return view('admin.cards.index', compact('cards'));
    }

    /**
     * Show the form for creating a new card.
     */
    public function create()
    {
        return view('admin.cards.create');
    }

    /**
     * Store a newly created card (global master data, not assigned to any racer).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_code' => 'required|string|max:255|unique:cards,card_code',
            'card_no'   => 'nullable|string|max:5|unique:cards,card_no',
            'status'    => 'required|in:ACTIVE,LOST,BANNED',
            'coupon'    => 'nullable|integer|min:0',
        ]);

        Card::create([
            'id'         => Str::uuid(),
            'card_code'  => $validated['card_code'],
            'card_no'    => $validated['card_no'] ?? null,
            'status'     => $validated['status'],
            'coupon'     => $validated['coupon'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.cards.index')
            ->with('success', 'Card created successfully in master data.');
    }

    /**
     * Display the specified card with its tournament usage history.
     */
    public function show(Card $card)
    {
        $card->load([
            'tournamentAssignments.tournament',
            'tournamentAssignments.racer.team',
        ]);

        return view('admin.cards.show', compact('card'));
    }

    /**
     * Show the form for editing the specified card.
     */
    public function edit(Card $card)
    {
        return view('admin.cards.edit', compact('card'));
    }

    /**
     * Update the specified card.
     */
    public function update(Request $request, Card $card)
    {
        $validated = $request->validate([
            'card_code' => 'required|string|max:255|unique:cards,card_code,' . $card->id,
            'card_no'   => 'nullable|string|max:5|unique:cards,card_no,' . $card->id,
            'status'    => 'required|in:ACTIVE,LOST,BANNED',
            'coupon'    => 'nullable|integer|min:0',
        ]);

        $card->update([
            'card_code'  => $validated['card_code'],
            'card_no'    => $validated['card_no'] ?? null,
            'status'     => $validated['status'],
            'coupon'     => $validated['coupon'] ?? $card->coupon,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.cards.index')
            ->with('success', 'Card updated successfully.');
    }

    /**
     * Bulk delete cards from master data.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'card_ids'   => 'required|array|min:1',
            'card_ids.*' => 'uuid|exists:cards,id',
        ]);

        $deleted = Card::whereIn('id', $validated['card_ids'])->delete();

        return redirect()->route('admin.cards.index')
            ->with('success', "{$deleted} card(s) deleted successfully.");
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
            'status'     => 'required|in:ACTIVE,LOST,BANNED',
        ]);

        $cardCodes = preg_split('/[\r\n,]+/', $validated['card_codes']);
        $cardCodes = array_filter(array_map('trim', $cardCodes));

        $created = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($cardCodes as $cardCode) {
            if (Card::where('card_code', $cardCode)->exists()) {
                $skipped++;
                $errors[] = "Card '{$cardCode}' already exists";
                continue;
            }

            Card::create([
                'id'         => Str::uuid(),
                'card_code'  => $cardCode,
                'status'     => $validated['status'],
                'coupon'     => 0,
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
