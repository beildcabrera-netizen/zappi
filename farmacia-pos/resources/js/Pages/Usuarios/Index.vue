<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Badge from '@/Components/UI/Badge.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import InputSearch from '@/Components/UI/InputSearch.vue'

const props = defineProps({
    usuarios: { type: Object, required: true },
})

const searchQuery = ref('')

function buscar() {
    router.get(route('usuarios.index'), { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    })
}

function eliminar(id) {
    if (confirm('¿Eliminar este usuario?')) {
        router.delete(route('usuarios.destroy', id))
    }
}

function rolBadge(rol) {
    if (rol === 'administrador') return 'red'
    if (rol === 'vendedor') return 'blue'
    if (rol === 'cajero') return 'yellow'
    return 'gray'
}
</script>

<template>
    <AppLayout>
        <Head title="Usuarios" />
        <PageHeader title="Usuarios">
            <template #actions>
                <BtnPrimary @click="router.get(route('usuarios.create'))">Nuevo Usuario</BtnPrimary>
            </template>
        </PageHeader>

        <div class="mb-4">
            <div class="max-w-sm">
                <InputSearch
                    v-model="searchQuery"
                    placeholder="Buscar por nombre o email..."
                    @keyup.enter="buscar"
                />
            </div>
        </div>

        <div v-if="usuarios.data?.length" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Rol</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="u in usuarios.data" :key="u.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ u.nombre }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ u.email }}</td>
                        <td class="px-4 py-3 text-center">
                            <Badge :color="rolBadge(u.rol)">{{ u.rol }}</Badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <Badge :color="u.activo ? 'green' : 'gray'">{{ u.activo ? 'Activo' : 'Inactivo' }}</Badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-blue-600"
                                    :title="'Editar ' + u.nombre"
                                    @click="router.get(route('usuarios.edit', u.id))"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                    :title="'Eliminar ' + u.nombre"
                                    @click="eliminar(u.id)"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!usuarios.data?.length">
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">No hay usuarios registrados</td>
                    </tr>
                </tbody>
            </table>

            <div v-if="usuarios.links?.length" class="border-t border-gray-200 px-4 py-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Mostrando {{ usuarios.meta?.from || 0 }} a {{ usuarios.meta?.to || 0 }} de {{ usuarios.meta?.total || 0 }} usuarios
                    </p>
                    <div class="flex gap-1">
                        <template v-for="(link, i) in usuarios.links" :key="i">
                            <button
                                v-if="link.url"
                                class="rounded px-3 py-1 text-sm transition"
                                :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                                v-html="link.label"
                                @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                            />
                            <span v-else class="rounded px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="!usuarios.data" class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400">
            No hay usuarios registrados
        </div>
    </AppLayout>
</template>
