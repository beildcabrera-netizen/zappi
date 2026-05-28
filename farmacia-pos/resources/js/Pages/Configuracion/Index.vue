<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'

const props = defineProps({
    config: { type: Object, default: () => ({}) },
})

const form = useForm({
    nombre_farmacia: props.config.nombre_farmacia || '',
    nit: props.config.nit || '',
    direccion: props.config.direccion || '',
    telefono: props.config.telefono || '',
    leyenda_ticket: props.config.leyenda_ticket || '¡Gracias por su compra!',
})

function submit() {
    form.put(route('configuracion.update'), {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Configuración" />
        <PageHeader title="Configuración" description="Administrar configuración del sistema" />

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nombre de la Farmacia</label>
                        <input v-model="form.nombre_farmacia" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">NIT</label>
                        <input v-model="form.nit" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Dirección</label>
                    <input v-model="form.direccion" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Teléfono</label>
                    <input v-model="form.telefono" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Leyenda en Ticket</label>
                    <input v-model="form.leyenda_ticket" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div class="flex justify-end">
                    <BtnPrimary type="submit" :disabled="form.processing">Guardar Cambios</BtnPrimary>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
