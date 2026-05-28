<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import InputSearch from '@/Components/UI/InputSearch.vue'
import VentaRow from '@/Components/Venta/VentaRow.vue'

const props = defineProps({
    ventas: { type: Array, default: () => [] },
})

const searchQuery = ref('')

const ventasFiltradas = computed(() => {
    const q = searchQuery.value.toLowerCase().trim()
    if (!q) return props.ventas
    return props.ventas.filter(v =>
        String(v.id).includes(q) ||
        (v.vendedor?.name && v.vendedor.name.toLowerCase().includes(q))
    )
})
</script>

<template>
    <AppLayout>
        <Head title="Ventas" />
        <PageHeader title="Historial de Ventas">
            <template #actions>
                <div class="w-64">
                    <InputSearch v-model="searchQuery" placeholder="Buscar venta..." />
                </div>
            </template>
        </PageHeader>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Fecha</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Pago</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Vendedor</th>
                    </tr>
                </thead>
                <tbody>
                    <VentaRow v-for="v in ventasFiltradas" :key="v.id" :venta="v" />
                    <tr v-if="!ventasFiltradas.length">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">No hay ventas</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
