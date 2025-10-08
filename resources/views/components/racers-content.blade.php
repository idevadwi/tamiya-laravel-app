<!-- Header -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">Racers</h1>
    <p class="text-gray-400">Manage your racing participants</p>
</div>

<!-- Loading State -->
<div id="loadingState" class="text-center py-12">
    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    <p class="mt-4 text-gray-400">Loading racers...</p>
</div>

<!-- Error State -->
<div id="errorState" class="hidden max-w-2xl mx-auto bg-red-900/20 border border-red-600/20 rounded-lg p-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-300">Error Loading Racers</h3>
            <p id="errorMessage" class="mt-2 text-sm text-red-400"></p>
        </div>
    </div>
</div>

<!-- Empty State -->
<div id="emptyState" class="hidden text-center py-12">
    <div class="bg-gray-700/50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-white mb-2">No Racers Found</h3>
    <p class="text-gray-400 mb-6">Be the first to register a racer and join the racing community!</p>
    <a href="/racers/register" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium">
        Register First Racer
    </a>
</div>

<!-- Racers Grid -->
<div id="racersGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <!-- Racer cards will be populated here -->
</div>

<!-- Racers Stats -->
<div id="racersStats" class="hidden max-w-4xl mx-auto mt-8 bg-gray-800/50 rounded-xl border border-gray-700/50 p-6">
    <h3 class="text-lg font-semibold text-white mb-4">Racers Statistics</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-400" id="totalRacers">0</div>
            <div class="text-sm text-gray-400">Total Racers</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-green-400" id="teamRacers">0</div>
            <div class="text-sm text-gray-400">Team Racers</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-purple-400" id="independentRacers">0</div>
            <div class="text-sm text-gray-400">Independent Racers</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="hidden" id="quickActions">
    <div class="flex justify-center gap-4 mt-8">
        <a href="/racers" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 font-medium">
            View All Racers
        </a>
        <a href="/racers/register" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 font-medium">
            Register New Racer
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're in the racers section and elements exist
    if (window.currentView !== 'racer') return;

    const loadingState = document.getElementById('loadingState');
    const errorState = document.getElementById('errorState');
    const emptyState = document.getElementById('emptyState');
    const racersGrid = document.getElementById('racersGrid');
    const racersStats = document.getElementById('racersStats');
    const quickActions = document.getElementById('quickActions');
    const errorMessage = document.getElementById('errorMessage');

    // Check if elements exist before proceeding
    if (!loadingState || !errorState || !emptyState || !racersGrid || !racersStats) {
        console.log('Racers component elements not found, skipping initialization');
        return;
    }

    // Stats elements
    const totalRacersEl = document.getElementById('totalRacers');
    const teamRacersEl = document.getElementById('teamRacers');
    const independentRacersEl = document.getElementById('independentRacers');

    function showLoading() {
        loadingState.classList.remove('hidden');
        errorState.classList.add('hidden');
        emptyState.classList.add('hidden');
        racersGrid.classList.add('hidden');
        racersStats.classList.add('hidden');
        quickActions.classList.add('hidden');
    }

    function showError(message) {
        loadingState.classList.add('hidden');
        errorState.classList.remove('hidden');
        emptyState.classList.add('hidden');
        racersGrid.classList.add('hidden');
        racersStats.classList.add('hidden');
        quickActions.classList.add('hidden');
        if (errorMessage) errorMessage.textContent = message;
    }

    function showEmpty() {
        loadingState.classList.add('hidden');
        errorState.classList.add('hidden');
        emptyState.classList.remove('hidden');
        racersGrid.classList.add('hidden');
        racersStats.classList.add('hidden');
        quickActions.classList.add('hidden');
    }

    function showRacers(racers) {
        loadingState.classList.add('hidden');
        errorState.classList.add('hidden');
        emptyState.classList.add('hidden');
        racersGrid.classList.remove('hidden');
        racersStats.classList.remove('hidden');
        quickActions.classList.remove('hidden');

        // Clear existing racers
        racersGrid.innerHTML = '';

        // Show limited racers (first 8 for dashboard view)
        const displayRacers = racers.slice(0, 8);

        // Populate racers
        displayRacers.forEach(racer => {
            const racerCard = createRacerCard(racer);
            racersGrid.appendChild(racerCard);
        });

        // Update stats
        updateStats(racers);
    }

    function createRacerCard(racer) {
        const card = document.createElement('div');
        card.className = 'bg-gray-800/50 border border-gray-700/50 rounded-xl hover:bg-gray-800/70 transition-all duration-300 p-4';

        const createdDate = new Date(racer.created_at).toLocaleDateString();

        card.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <div class="bg-green-500/20 w-10 h-10 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="text-xs text-gray-500">
                    ID: ${racer.id.substring(0, 8)}...
                </div>
            </div>

            <h3 class="text-sm font-semibold text-white mb-2 truncate">${racer.racer_name}</h3>

            <div class="space-y-1 text-xs text-gray-400">
                ${racer.team ? `
                <div class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Team: ${racer.team.team_name}
                </div>
                ` : `
                <div class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Independent Racer
                </div>
                `}
                <div class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v8a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
                    </svg>
                    Registered: ${createdDate}
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-700/50">
                <div class="flex justify-between items-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${racer.team ? 'bg-blue-500/20 text-blue-300' : 'bg-purple-500/20 text-purple-300'}">
                        ${racer.team ? 'Team Member' : 'Independent'}
                    </span>
                    <button class="text-blue-400 hover:text-blue-300 text-xs font-medium transition-colors duration-200">
                        View
                    </button>
                </div>
            </div>
        `;

        return card;
    }

    function updateStats(racers) {
        const total = racers.length;
        if (totalRacersEl) totalRacersEl.textContent = total;

        // Calculate team vs independent racers
        const teamRacers = racers.filter(racer => racer.team).length;
        const independentRacers = total - teamRacers;

        if (teamRacersEl) teamRacersEl.textContent = teamRacers;
        if (independentRacersEl) independentRacersEl.textContent = independentRacers;
    }

    async function loadRacers() {
        showLoading();

        try {
            const response = await fetch('/api/racers', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Racers data:', data);
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    showRacers(data.data);
                } else {
                    showEmpty();
                }
            } else {
                showError(data.message || 'Failed to load racers');
            }
        } catch (error) {
            console.error('Error loading racers:', error);
            showError('Network error. Please check your connection and try again.');
        }
    }

    // Initialize racers loading
    loadRacers();
});
</script>
