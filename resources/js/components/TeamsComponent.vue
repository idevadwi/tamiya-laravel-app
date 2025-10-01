<template>
  <div>
    <!-- Teams Header -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-white">Teams</h1>
          <p class="text-gray-400">Manage your racing teams</p>
        </div>
        <div class="flex gap-4">
          <a
            href="/teams/register"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 text-sm font-medium"
          >
            Register New Team
          </a>
          <a
            href="/teams"
            target="_blank"
            class="text-indigo-400 hover:text-indigo-300 px-4 py-2 text-sm font-medium"
          >
            Full Teams Page →
          </a>
        </div>
      </div>
    </div>

    <!-- Teams Data Container -->
    <div>
      <!-- Loading state -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-4 text-gray-400">Loading teams...</p>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="bg-red-900 border border-red-700 rounded-lg p-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-200">Error Loading Teams</h3>
            <p class="mt-2 text-sm text-red-300">{{ errorMessage }}</p>
            <div class="mt-4">
              <a href="/teams" class="text-red-200 hover:text-red-100 underline">
                Go to Teams Page →
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="teams.length === 0" class="text-center py-12 bg-gray-800 rounded-lg">
        <div class="bg-gray-700 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
            ></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No Teams Found</h3>
        <p class="text-gray-400 mb-6">Be the first to register a team and join the racing community!</p>
        <a
          href="/teams/register"
          class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors duration-200 font-medium"
        >
          Register First Team
        </a>
      </div>

      <!-- Teams grid -->
      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
          <!-- Team cards -->
          <div
            v-for="team in teams"
            :key="team.id"
            class="bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="bg-indigo-900 w-12 h-12 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  ></path>
                </svg>
              </div>
              <div class="text-xs text-gray-400">
                ID: {{ team.id.substring(0, 8) }}...
              </div>
            </div>

            <h3 class="text-lg font-semibold text-white mb-2">{{ team.team_name }}</h3>

            <div class="space-y-2 text-sm text-gray-400">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0v1h16v-1a4 4 0 11-8 0z"
                  ></path>
                </svg>
                Created: {{ formatDate(team.created_at) }}
              </div>
              <div v-if="team.updated_at !== team.created_at" class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  ></path>
                </svg>
                Updated: {{ formatDate(team.updated_at) }}
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-700">
              <div class="flex justify-between items-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                  Active
                </span>
                <button
                  @click="viewTeamDetails(team)"
                  class="text-indigo-400 hover:text-indigo-300 text-sm font-medium transition-colors duration-200"
                >
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Teams Stats -->
        <div class="bg-gray-800 rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-white mb-4">Teams Statistics</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
              <div class="text-2xl font-bold text-indigo-400">{{ teamsStats.total }}</div>
              <div class="text-sm text-gray-400">Total Teams</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-400">{{ teamsStats.recent }}</div>
              <div class="text-sm text-gray-400">Recent Teams (7 days)</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-400">{{ teamsStats.active }}</div>
              <div class="text-sm text-gray-400">Active Teams</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TeamsComponent',
  data() {
    return {
      teams: [],
      loading: false,
      error: false,
      errorMessage: 'Network error. Please check your connection and try again.'
    }
  },
  computed: {
    teamsStats() {
      const total = this.teams.length;
      const active = this.teams.length; // Assuming all teams are active for now

      // Calculate recent teams (last 7 days)
      const sevenDaysAgo = new Date();
      sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);

      const recent = this.teams.filter(team =>
        new Date(team.created_at) >= sevenDaysAgo
      ).length;

      return {
        total,
        recent,
        active
      };
    }
  },
  async mounted() {
    await this.loadTeams();
  },
  methods: {
    async loadTeams() {
      this.loading = true;
      this.error = false;

      try {
        const response = await fetch('/api/teams');

        if (!response.ok) {
          throw new Error('Failed to fetch teams data');
        }

        const result = await response.json();
        this.teams = result.data || [];

      } catch (error) {
        console.error('Error fetching teams:', error);
        this.error = true;
        this.errorMessage = error.message;
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },
    viewTeamDetails(team) {
      // Handle team details view
      console.log('View team details:', team);
      // You can emit an event or navigate to team details page
      this.$emit('view-team', team);
    }
  }
}
</script>

<style scoped>
/* Component-specific styles can be added here if needed */
</style>
