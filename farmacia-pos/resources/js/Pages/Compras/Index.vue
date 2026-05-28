<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Badge from '@/Components/UI/Badge.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'

const props = defineProps({
    compras: { type: Array, default: () => [] },
})

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Compras" />
        <PageHeader title="Órdenes de Compra">
            <template #actions>
                <BtnPrimary>Nueva Compra</BtnPrimary>
            </template>
        </PageHeader>

        <div v-if="compras.length" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Proveedor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Fecha</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in compras" :key="c.id" class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">#{{ c.id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ c.proveedor?.nombre || c.proveedor || '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ c.created_at }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatear(c.total) }}</td>
                        <td class="px-4 py-3 text-center">
                            <Badge :color="c.estado === 'recibido' ? 'green' : c.estado === 'pendiente' ? 'yellow' : 'gray'">
                                {{ c.estado || 'pendiente' }}
                            </Badge>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400">
            No hay órdenes de compra registradas
        </div>
    </AppLayout>
</template>
