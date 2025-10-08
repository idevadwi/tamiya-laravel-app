<template>
  <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
    <div class="md:flex">
      <div class="p-8">
        <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">
          Vue Component
        </div>
        <h2 class="block mt-1 text-lg leading-tight font-medium text-black">
          {{ title }}
        </h2>
        <p class="mt-2 text-gray-500">
          {{ message }}
        </p>
        <button
          @click="updateMessage"
          class="mt-4 px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600 transition-colors"
        >
          {{ buttonText }}
        </button>
        <p v-if="clickCount > 0" class="mt-2 text-sm text-gray-600">
          Button clicked {{ clickCount }} time(s)
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'

export default {
  name: 'ExampleComponent',
  setup() {
    const title = ref('Hello from Vue!')
    const message = ref('This is an example Vue component integrated with Laravel and Tailwind CSS.')
    const buttonText = ref('Click me!')
    const clickCount = ref(0)

    const updateMessage = () => {
      clickCount.value++
      message.value = `You've clicked the button ${clickCount.value} time(s). Vue is working perfectly!`
    }

    return {
      title,
      message,
      buttonText,
      clickCount,
      updateMessage
    }
  }
}
</script>

<style scoped>
/* Component-specific styles can go here */
</style>
