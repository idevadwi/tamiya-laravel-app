<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentResult;
use App\Models\TournamentParticipant;
use App\Models\Team;
use App\Models\Race;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TournamentResultController extends Controller
{
    /**
     * Display the tournament results management page.
     */
    public function index()
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get all teams participating in this tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Generate all categories based on tournament settings
        $categories = $this->generateCategories($tournament);

        // Get existing results
        $existingResults = TournamentResult::where('tournament_id', $tournament->id)
            ->with('team')
            ->get()
            ->groupBy('category');

        // Calculate best race champions if enabled
        $bestRaceData = [];
        if ($tournament->best_race_enabled) {
            $bestRaceData = $this->calculateBestRaceChampions($tournament);
        }

        return view('tournament_results.index', compact(
            'tournament',
            'teams',
            'categories',
            'existingResults',
            'bestRaceData'
        ));
    }

    /**
     * Save tournament results.
     */
    public function store(Request $request)
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.category' => 'required|string',
            'results.*.team_id' => 'nullable|uuid|exists:teams,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['results'] as $resultData) {
                // Skip if team_id is empty
                if (empty($resultData['team_id'])) {
                    // Check if there's an existing result and delete it
                    TournamentResult::where('tournament_id', $tournament->id)
                        ->where('category', $resultData['category'])
                        ->delete();
                    continue;
                }

                // Check if result already exists
                $result = TournamentResult::where('tournament_id', $tournament->id)
                    ->where('category', $resultData['category'])
                    ->first();

                if ($result) {
                    // Update existing result
                    $result->update([
                        'team_id' => $resultData['team_id'],
                        'updated_by' => auth()->id(),
                    ]);
                } else {
                    // Create new result
                    TournamentResult::create([
                        'id' => Str::uuid(),
                        'tournament_id' => $tournament->id,
                        'category' => $resultData['category'],
                        'rank' => $this->extractRankFromCategory($resultData['category']),
                        'team_id' => $resultData['team_id'],
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('tournament_results.index')
                ->with('success', 'Tournament results saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save tournament results: ' . $e->getMessage());
        }
    }

    /**
     * Delete a tournament result.
     */
    public function destroy($id)
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $result = TournamentResult::where('tournament_id', $tournament->id)
            ->where('id', $id)
            ->firstOrFail();

        $result->delete();

        return redirect()->route('tournament_results.index')
            ->with('success', 'Tournament result deleted successfully!');
    }

    /**
     * Generate all possible categories based on tournament settings.
     */
    private function generateCategories($tournament)
    {
        $categories = [];

        // Main champions (e.g., champions_1, champions_2, champions_3)
        for ($i = 1; $i <= $tournament->champion_number; $i++) {
            $categories[] = [
                'key' => "champions_{$i}",
                'label' => $this->getChampionLabel($i),
                'type' => 'champion'
            ];
        }

        // BTO champions per track
        for ($bto = 1; $bto <= $tournament->bto_number; $bto++) {
            for ($track = 1; $track <= $tournament->track_number; $track++) {
                $categories[] = [
                    'key' => "bto_champions_{$bto}_track_{$track}",
                    'label' => "BTO Champions {$bto} - Track {$track}",
                    'type' => 'bto'
                ];
            }
        }

        // Best race champions (if enabled)
        if ($tournament->best_race_enabled) {
            for ($i = 1; $i <= $tournament->best_race_number; $i++) {
                $categories[] = [
                    'key' => "best_race_champions_{$i}",
                    'label' => "Best Race Champions " . $this->getChampionLabel($i),
                    'type' => 'best_race'
                ];
            }
        }

        return $categories;
    }

    /**
     * Calculate best race champions based on races with stage = 2.
     */
    private function calculateBestRaceChampions($tournament)
    {
        // Count wins (races) for each team where stage = 2
        $teamWins = Race::where('tournament_id', $tournament->id)
            ->where('stage', 2)
            ->select('team_id', DB::raw('COUNT(*) as win_count'))
            ->groupBy('team_id')
            ->orderByDesc('win_count')
            ->with('team')
            ->take($tournament->best_race_number)
            ->get();

        return $teamWins;
    }

    /**
     * Get champion label (1st, 2nd, 3rd, etc.)
     */
    private function getChampionLabel($number)
    {
        $suffix = 'th';
        if ($number == 1) {
            $suffix = 'st';
        } elseif ($number == 2) {
            $suffix = 'nd';
        } elseif ($number == 3) {
            $suffix = 'rd';
        }
        return "{$number}{$suffix} Place";
    }

    /**
     * Extract rank number from category string.
     */
    private function extractRankFromCategory($category)
    {
        // Extract number after 'champions_' or 'best_race_champions_' or 'bto_champions_'
        if (preg_match('/champions_(\d+)/', $category, $matches)) {
            return (int) $matches[1];
        }
        return 1;
    }
}
