<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
    user: { type: Object, default: () => ({}) },
    turno: { type: Object, default: null },
})

const emit = defineEmits(['toggle-sidebar'])

const showDropdown = ref(false)
</script>

<template>
    <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 shadow-sm lg:px-6">
        <button
            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden"
            @click="emit('toggle-sidebar')"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="flex flex-1 items-center justify-between">
            <div class="flex items-center gap-3">
                <div v-if="turno" class="flex items-center gap-2 rounded-full bg-green-50 px-3 py-1">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    <span class="text-xs font-medium text-green-700">Turno activo</span>
                    <span class="text-xs text-green-600">#{{ turno.id }}</span>
                </div>
            </div>

            <div class="relative flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                    <p class="text-xs text-gray-500">{{ user.rol || user.role }}</p>
                </div>
                <button
                    class="flex items-center gap-2 rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                    @click="showDropdown = !showDropdown"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700">
                        {{ (user.name || 'U')[0].toUpperCase() }}
                    </div>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    v-if="showDropdown"
                    class="absolute right-0 top-full mt-1 w-48 rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
                    @click="showDropdown = false"
                >
                    <Link
                        :href="route('profile.edit')"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        Perfil
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                    >
                        Cerrar Sesión
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>
