<template>
  <!--
    This example requires updating your template:

    ```
    <html class="h-full bg-gray-900">
    <body class="h-full">
    ```
  -->
  <div>
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog class="relative z-50 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="" leave="transition-opacity ease-linear duration-300" leave-from="" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-900/80" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
              <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="" leave="ease-in-out duration-300" leave-from="" leave-to="opacity-0">
                <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                  <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>

              <!-- Sidebar component, swap this element with another sidebar if you like -->
              <div class="relative flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4 ring ring-white/10 before:pointer-events-none before:absolute before:inset-0 before:bg-black/10">
                <div class="relative flex h-16 shrink-0 items-center">
                  <img class="h-8 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company" />
                </div>
                <nav class="relative flex flex-1 flex-col">
                  <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                      <ul role="list" class="-mx-2 space-y-1">
                        <li v-for="item in navigation" :key="item.name">
                          <button @click="setActiveView(item.view)" :class="[item.current ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white', 'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold w-full text-left']">
                            <component :is="item.icon" :class="[item.current ? 'text-white' : 'text-gray-400 group-hover:text-white', 'size-6 shrink-0']" aria-hidden="true" />
                            {{ item.name }}
                          </button>
                        </li>
                      </ul>
                    </li>
                    <li>
                      <div class="text-xs/6 font-semibold text-gray-400">Your teams</div>
                      <ul role="list" class="-mx-2 mt-2 space-y-1">
                        <li v-for="team in teams" :key="team.name">
                          <a :href="team.href" :class="[team.current ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white', 'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold']">
                            <span :class="[team.current ? 'border-white/20 text-white' : 'border-white/10 text-gray-400 group-hover:border-white/20 group-hover:text-white', 'flex size-6 shrink-0 items-center justify-center rounded-lg border bg-white/5 text-[0.625rem] font-medium']">{{ team.initial }}</span>
                            <span class="truncate">{{ team.name }}</span>
                          </a>
                        </li>
                      </ul>
                    </li>
                    <li class="mt-auto">
                      <a href="#" class="group -mx-2 flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold text-gray-300 hover:bg-white/5 hover:text-white">
                        <Cog6ToothIcon class="size-6 shrink-0 text-gray-400 group-hover:text-white" aria-hidden="true" />
                        Settings
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Static sidebar for desktop -->
    <div class="hidden bg-gray-900 lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-white/10 bg-black/10 px-6 pb-4">
        <div class="flex h-16 shrink-0 items-center">
          <img class="h-8 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company" />
        </div>
        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul role="list" class="-mx-2 space-y-1">
                <li v-for="item in navigation" :key="item.name">
                  <button @click="setActiveView(item.view)" :class="[item.current ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white', 'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold w-full text-left']">
                    <component :is="item.icon" :class="[item.current ? 'text-white' : 'text-gray-400 group-hover:text-white', 'size-6 shrink-0']" aria-hidden="true" />
                    {{ item.name }}
                  </button>
                </li>
              </ul>
            </li>
            <li>
              <div class="text-xs/6 font-semibold text-gray-400">Your teams</div>
              <ul role="list" class="-mx-2 mt-2 space-y-1">
                <li v-for="team in teams" :key="team.name">
                  <a :href="team.href" :class="[team.current ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white', 'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold']">
                    <span :class="[team.current ? 'border-white/20 text-white' : 'border-white/10 text-gray-400 group-hover:border-white/20 group-hover:text-white', 'flex size-6 shrink-0 items-center justify-center rounded-lg border bg-white/5 text-[0.625rem] font-medium']">{{ team.initial }}</span>
                    <span class="truncate">{{ team.name }}</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="mt-auto">
              <a href="#" class="group -mx-2 flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold text-gray-300 hover:bg-white/5 hover:text-white">
                <Cog6ToothIcon class="size-6 shrink-0 text-gray-400 group-hover:text-white" aria-hidden="true" />
                Settings
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>


    <div class="lg:pl-72 bg-gray-900 h-svh">
      <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-white/10 bg-gray-900 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-white lg:hidden" @click="sidebarOpen = true">
          <span class="sr-only">Open sidebar</span>
          <Bars3Icon class="size-6" aria-hidden="true" />
        </button>

        <!-- Separator -->
        <div class="h-6 w-px bg-white/10 lg:hidden" aria-hidden="true" />

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
          <form class="grid flex-1 grid-cols-1" action="#" method="GET">
            <input name="search" aria-label="Search" class="col-start-1 row-start-1 block size-full bg-gray-900 pl-8 text-base text-white outline-hidden placeholder:text-gray-500 sm:text-sm/6" placeholder="Search" />
            <MagnifyingGlassIcon class="pointer-events-none col-start-1 row-start-1 size-5 self-center text-gray-400" aria-hidden="true" />
          </form>
          <div class="flex items-center gap-x-4 lg:gap-x-6">
            <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-white">
              <span class="sr-only">View notifications</span>
              <BellIcon class="size-6" aria-hidden="true" />
            </button>

            <!-- Separator -->
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-white/10" aria-hidden="true" />

            <!-- Profile dropdown -->
            <Menu as="div" class="relative">
              <MenuButton class="relative flex items-center">
                <span class="absolute -inset-1.5" />
                <span class="sr-only">Open user menu</span>
                <img class="size-8 rounded-full bg-gray-800 outline -outline-offset-1 outline-white/10" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" />
                <span class="hidden lg:flex lg:items-center">
                  <span class="ml-4 text-sm/6 font-semibold text-white" aria-hidden="true">Tom Cook</span>
                  <ChevronDownIcon class="ml-2 size-5 text-gray-500" aria-hidden="true" />
                </span>
              </MenuButton>
              <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform scale-100" leave-to-class="transform opacity-0 scale-95">
                <MenuItems class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-gray-800 py-2 outline-1 -outline-offset-1 outline-white/10">
                  <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                    <a :href="item.href" :class="[active ? 'bg-white/5 outline-hidden' : '', 'block px-3 py-1 text-sm/6 text-white']">{{ item.name }}</a>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
      </div>

      <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
          <!-- Dynamic content based on active view -->
          <div v-if="activeView === 'dashboard'">
            <h1 class="text-2xl font-bold text-white mb-6">Dashboard</h1>
            <p class="text-gray-400">Welcome to your dashboard. Select a menu item to view content.</p>
          </div>

          <div v-else-if="activeView === 'teams'">
            <TeamsComponent @view-team="handleViewTeam" />
          </div>

          <div v-else-if="activeView === 'racers'">
            <RacersComponent @view-racer="handleRacerTeam" />
          </div>

          <div v-else-if="activeView === 'calendar'">
            <h1 class="text-2xl font-bold text-white mb-6">Calendar</h1>
            <p class="text-gray-400">Calendar content will be displayed here.</p>
          </div>

          <div v-else-if="activeView === 'documents'">
            <h1 class="text-2xl font-bold text-white mb-6">Documents</h1>
            <p class="text-gray-400">Documents content will be displayed here.</p>
          </div>

          <div v-else-if="activeView === 'reports'">
            <h1 class="text-2xl font-bold text-white mb-6">Reports</h1>
            <p class="text-gray-400">Reports content will be displayed here.</p>
          </div>
          <div v-else-if="activeView === 'timer'">
            <h1 class="text-2xl font-bold text-white mb-6">Reports</h1>
            <p class="text-gray-400">Reports content will be displayed here.</p>
            <RaceTimer @view-racer="handleRacerTimer" />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TeamsComponent from './TeamsComponent.vue'
import RacersComponent from './RacersComponent.vue'
import RaceTimer from './RaceTimer.vue'  // Optional: Uncomment if you want to include the RaceTimer component
import {
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import {
  Bars3Icon,
  BellIcon,
  CalendarIcon,
  ChartPieIcon,
  Cog6ToothIcon,
  DocumentDuplicateIcon,
  FolderIcon,
  HomeIcon,
  UsersIcon,
  XMarkIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/20/solid'

const activeView = ref('dashboard')

const navigation = [
  { name: 'Dashboard', view: 'dashboard', icon: HomeIcon, current: true },
  { name: 'Team', view: 'teams', icon: UsersIcon, current: false },
  { name: 'Racers', view: 'racers', icon: FolderIcon, current: false },
  { name: 'Calendar', view: 'calendar', icon: CalendarIcon, current: false },
  { name: 'Documents', view: 'documents', icon: DocumentDuplicateIcon, current: false },
  { name: 'Reports', view: 'reports', icon: ChartPieIcon, current: false },
  { name: 'Timer', view: 'timer', icon: ClockIcon, current: false },
]

const teams = [
  { id: 1, name: 'Heroicons', href: '#', initial: 'H', current: false },
  { id: 2, name: 'Tailwind Labs', href: '#', initial: 'T', current: false },
  { id: 3, name: 'Workcation', href: '#', initial: 'W', current: false },
]

const userNavigation = [
  { name: 'Your profile', href: '#' },
  { name: 'Sign out', href: '#' },
]

const sidebarOpen = ref(false)

// Function to set active view and update navigation state
function setActiveView(view) {
  activeView.value = view
  // Update current state for navigation items
  navigation.forEach(item => {
    item.current = item.view === view
  })
}

// Handle team view event from TeamsComponent
function handleViewTeam(team) {
  console.log('Dashboard received view team event:', team)
  // You can handle team viewing logic here
  // For example, navigate to team details or show a modal
}

function handleRacerTeam(racer) {
  console.log('Dashboard received view racer event:', racer)
  // You can handle racer viewing logic here
  // For example, navigate to racer details or show a modal
}

function handleRacerTimer(racer) {
  console.log('Dashboard received view racer event:', racer)
  // You can handle racer viewing logic here
  // For example, navigate to racer details or show a modal
}
</script>
