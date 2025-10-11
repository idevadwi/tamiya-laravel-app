<template>
  <div class="bg-gray-800 rounded-lg shadow-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-white">Race Timer</h3>
      <div class="flex space-x-2">
        <button
          @click="startTimer"
          :disabled="isRunning"
          class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Start
        </button>
        <button
          @click="stopTimer"
          :disabled="!isRunning"
          class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Stop
        </button>
        <button
          @click="resetTimer"
          class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700"
        >
          Reset
        </button>
      </div>
    </div>

    <div class="text-center">
      <div class="text-4xl font-mono text-white mb-2">
        {{ formatTime(elapsedTime) }}
      </div>
      <p class="text-gray-400 text-sm">
        {{ isRunning ? 'Timer is running...' : 'Timer stopped' }}
      </p>

      <div v-if="bestTime" class="mt-4 p-3 bg-gray-700 rounded">
        <p class="text-yellow-400 text-sm font-semibold">Best Time</p>
        <p class="text-white text-xl font-mono">{{ formatTime(bestTime) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'RaceTimer',
  setup() {
    const elapsedTime = ref(0)
    const isRunning = ref(false)
    const bestTime = ref(null)
    let startTime = null
    let intervalId = null

    const startTimer = () => {
      if (isRunning.value) return

      startTime = Date.now() - elapsedTime.value
      isRunning.value = true

      intervalId = setInterval(() => {
        elapsedTime.value = Date.now() - startTime
      }, 10)
    }

    const stopTimer = () => {
      if (!isRunning.value) return

      isRunning.value = false
      clearInterval(intervalId)

      // Update best time if this is better
      if (!bestTime.value || elapsedTime.value < bestTime.value) {
        bestTime.value = elapsedTime.value
      }
    }

    const resetTimer = () => {
      isRunning.value = false
      elapsedTime.value = 0
      clearInterval(intervalId)
    }

    const formatTime = (time) => {
      if (!time) return '00:00.000'

      const minutes = Math.floor(time / 60000)
      const seconds = Math.floor((time % 60000) / 1000)
      const milliseconds = Math.floor((time % 1000) / 10)

      return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}.${milliseconds.toString().padStart(2, '0')}`
    }

    onUnmounted(() => {
      if (intervalId) {
        clearInterval(intervalId)
      }
    })

    return {
      elapsedTime,
      isRunning,
      bestTime,
      startTimer,
      stopTimer,
      resetTimer,
      formatTime
    }
  }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
