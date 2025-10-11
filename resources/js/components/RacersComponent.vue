<template>
  <div class="container mx-auto px-4">
    <!-- Header -->
        <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-white">Racers</h1>
          <p class="text-gray-400">Manage your Racers</p>
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

    <!-- Action Buttons -->
    <!-- <div class="flex justify-between items-center mb-8">
      <div class="flex gap-4">
        <router-link to="/" class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors duration-200">
          ← Back to Home
        </router-link>
        <router-link to="/racers/register" class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors duration-200">
          Register New Racer
        </router-link>
        <router-link to="/teams" class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors duration-200">
          View Teams
        </router-link>
      </div>
    </div> -->

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Loading racers...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="max-w-2xl mx-auto bg-red-50 border border-red-200 rounded-lg p-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error Loading Racers</h3>
          <p class="mt-2 text-sm text-red-700">{{ errorMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && !error && racers.length === 0" class="text-center py-12">
      <div class="bg-gray-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No Racers Found</h3>
      <p class="text-gray-600 mb-6">Be the first to register a racer and join the racing community!</p>
      <router-link to="/racers/register" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
        Register First Racer
      </router-link>
    </div>

    <!-- Racers Grid -->
    <div v-if="!loading && !error && racers.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="racer in racers" :key="racer.id" class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="bg-green-50 w-12 h-12 rounded-full flex items-center justify-center">
            <img
              v-if="hasImage(racer)"
              :src="getImageUrl(racer)"
              :alt="racer.racer_name"
              class="w-10 h-10 rounded-full object-cover"
              @error="handleImageError"
            >
            <svg v-else class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <div class="text-xs text-gray-500">
            ID: {{ racer.id.substring(0, 8) }}...
          </div>
        </div>

        <div class="rounded-full object-cover flex items-center justify-center h-24 mb-4">
          <img
            v-if="racer.image_url"
            :src="racer.image_url"
            :alt="racer.racer_name"
            class="h-24 rounded-full object-cover"
          >
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ racer.racer_name }}</h3>

        <div class="space-y-2 text-sm text-gray-600">
          <div v-if="racer.team" class="flex items-center">
            Team: {{ racer.team.team_name }}
          </div>
          <div v-else class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Independent Racer
          </div>
          <div class="flex items-center">
            Registered: {{ formatDate(racer.created_at) }}
          </div>
          <div v-if="racer.updated_at !== racer.created_at" class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Updated: {{ formatDate(racer.updated_at) }}
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <span :class="getStatusBadgeClass(racer)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-center">
              {{ racer.team ? 'Team Member' : 'Independent' }}
            </span>
            <button @click="viewDetails(racer)" class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors duration-200">
              View Details
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Racers Stats -->
    <div v-if="!loading && !error && racers.length > 0" class="max-w-4xl mx-auto mt-12 bg-white rounded-xl shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Racers Statistics</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ totalRacers }}</div>
          <div class="text-sm text-gray-600">Total Racers</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ teamRacers }}</div>
          <div class="text-sm text-gray-600">Team Racers</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">{{ independentRacers }}</div>
          <div class="text-sm text-gray-600">Independent Racers</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'RacersComponent',
  setup() {
    const racers = ref([])
    const loading = ref(false)
    const error = ref(false)
    const errorMessage = ref('')

    // Computed properties for stats
    const totalRacers = computed(() => racers.value.length)
    const teamRacers = computed(() => racers.value.filter(racer => racer.team).length)
    const independentRacers = computed(() => totalRacers.value - teamRacers.value)

    // Methods
    const loadRacers = async () => {
      loading.value = true
      error.value = false
      errorMessage.value = ''

      try {
        const response = await fetch('/api/racers', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })

        const data = await response.json()

        if (data.success) {
          racers.value = data.data || []
        } else {
          error.value = true
          errorMessage.value = data.message || 'Failed to load racers'
        }
      } catch (err) {
        console.error('Error loading racers:', err)
        error.value = true
        errorMessage.value = 'Network error. Please check your connection and try again.'
      } finally {
        loading.value = false
      }
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString()
    }

    const hasImage = (racer) => {
      return racer.image_url && racer.image_url.trim() !== ''
    }

    const getImageUrl = (racer) => {
      // Using example image as in the original code
      return 'http://localhost:8002/storage/racers/maresh.jpg'
    }

    const handleImageError = (event) => {
      event.target.style.display = 'none'
      const fallbackSvg = event.target.nextElementSibling
      if (fallbackSvg) {
        fallbackSvg.style.display = 'flex'
      }
    }

    const getStatusBadgeClass = (racer) => {
      return racer.team
        ? 'bg-blue-100 text-blue-800'
        : 'bg-purple-100 text-purple-800'
    }

    const viewDetails = (racer) => {
      // Implement view details functionality
      console.log('View details for racer:', racer)
      // You can emit an event or navigate to a details page
    }

    // Lifecycle
    onMounted(() => {
      loadRacers()
    })

    return {
      racers,
      loading,
      error,
      errorMessage,
      totalRacers,
      teamRacers,
      independentRacers,
      loadRacers,
      formatDate,
      hasImage,
      getImageUrl,
      handleImageError,
      getStatusBadgeClass,
      viewDetails
    }
  }
}
</script>

<style scoped>
/* Add any component-specific styles here */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
