<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'

const props = defineProps({
    usuario: { type: Object, required: true },
})

const form = useForm({
    nombre: props.usuario.nombre || '',
    email: props.usuario.email || '',
    password: '',
    password_confirmation: '',
    rol: props.usuario.roles?.[0]?.name || 'vendedor',
    puede_cobrar: props.usuario.puede_cobrar || false,
    telefono: props.usuario.telefono || '',
    activo: props.usuario.activo !== false,
})

function submit() {
    form.put(route('usuarios.update', props.usuario.id))
}
</script>

<template>
    <AppLayout>
        <Head :title="'Editar: ' + usuario.nombre" />
        <PageHeader :title="'Editar: ' + usuario.nombre">
            <template #actions>
                <BtnDanger @click="form.get(route('usuarios.index'))">Cancelar</BtnDanger>
            </template>
        </PageHeader>

        <div class="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nombre *</label>
                        <input v-model="form.nombre" type="text" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-600">{{ form.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Email *</label>
                        <input v-model="form.email" type="email" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nueva Contraseña (dejar vacío para mantener)</label>
                        <input v-model="form.password" type="password" minlength="8" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Confirmar Contraseña</label>
                        <input v-model="form.password_confirmation" type="password" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Rol *</label>
                        <select v-model="form.rol" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="vendedor">Vendedor</option>
                            <option value="cajero">Cajero</option>
                            <option value="administrador">Administrador</option>
                        </select>
                        <p v-if="form.errors.rol" class="mt-1 text-xs text-red-600">{{ form.errors.rol }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Teléfono</label>
                        <input v-model="form.telefono" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input v-model="form.puede_cobrar" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-gray-700">Puede cobrar ventas</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.activo" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-gray-700">Activo</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <BtnDanger type="button" @click="form.get(route('usuarios.index'))">Cancelar</BtnDanger>
                    <BtnPrimary type="submit" :disabled="form.processing">Actualizar Usuario</BtnPrimary>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
