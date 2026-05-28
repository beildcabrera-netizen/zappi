<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import InputNumber from '@/Components/UI/InputNumber.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'

const props = defineProps({
    cajas: { type: Array, default: () => [] },
})

const form = useForm({
    caja_id: '',
    monto_inicial: 0,
})

function submit() {
    form.post(route('caja.turno.apertura.store'), {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Abrir Turno" />
        <PageHeader title="Apertura de Turno" description="Seleccione la caja y el monto inicial" />

        <div class="mx-auto max-w-lg">
            <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Caja</label>
                    <select
                        v-model="form.caja_id"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        required
                    >
                        <option value="" disabled>Seleccione una caja</option>
                        <option v-for="caja in cajas" :key="caja.id" :value="caja.id">
                            {{ caja.nombre }}
                        </option>
                    </select>
                    <p v-if="form.errors.caja_id" class="mt-1 text-xs text-red-600">{{ form.errors.caja_id }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Monto Inicial (Bs)</label>
                    <InputNumber v-model="form.monto_inicial" :min="0" step="0.01" />
                    <p v-if="form.errors.monto_inicial" class="mt-1 text-xs text-red-600">{{ form.errors.monto_inicial }}</p>
                </div>

                <div class="flex gap-2">
                    <BtnDanger type="button" @click="form.reset()">Limpiar</BtnDanger>
                    <BtnPrimary type="submit" :disabled="form.processing">Abrir Turno</BtnPrimary>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
