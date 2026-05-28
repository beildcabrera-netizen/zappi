<script setup>
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    producto: { type: Object, required: true },
})

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 })
}

function editar() {
    router.get(route('productos.edit', props.producto.id))
}

function eliminar() {
    if (confirm('¿Eliminar este producto?')) {
        router.delete(route('productos.destroy', props.producto.id))
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="producto.nombre_comercial" />
        <PageHeader :title="producto.nombre_comercial">
            <template #actions>
                <BtnPrimary @click="editar">Editar</BtnPrimary>
                <BtnDanger @click="eliminar">Eliminar</BtnDanger>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Información General</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <div><dt class="text-xs text-gray-500">Código Interno</dt><dd class="text-sm font-medium text-gray-900">{{ producto.codigo_interno || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Código de Barras</dt><dd class="text-sm font-medium text-gray-900">{{ producto.codigo_barras || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Nombre Genérico</dt><dd class="text-sm font-medium text-gray-900">{{ producto.nombre_generico || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Principio Activo</dt><dd class="text-sm font-medium text-gray-900">{{ producto.principio_activo || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Concentración</dt><dd class="text-sm font-medium text-gray-900">{{ producto.concentracion || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Forma Farmacéutica</dt><dd class="text-sm font-medium text-gray-900">{{ producto.forma_farmaceutica || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Laboratorio</dt><dd class="text-sm font-medium text-gray-900">{{ producto.laboratorio || '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Sección / Estante</dt><dd class="text-sm font-medium text-gray-900">{{ producto.seccion || '—' }} / {{ producto.estante || '—' }}</dd></div>
                </dl>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Stock</h2>
                    <div class="text-3xl font-bold text-gray-900">{{ producto.stock_unidades }}</div>
                    <p class="text-sm text-gray-500">unidades</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <Badge v-if="producto.controlado" variant="warning">Controlado</Badge>
                        <Badge v-if="producto.refrigerado" variant="info">Refrigerado</Badge>
                        <Badge v-if="!producto.activo" variant="danger">Inactivo</Badge>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Precios</h2>
                    <dl class="space-y-2">
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Unidad</dt><dd class="text-sm font-medium text-gray-900">{{ formatear(producto.precio_venta_unidad) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Blister</dt><dd class="text-sm font-medium text-gray-900">{{ formatear(producto.precio_venta_blister) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-sm text-gray-500">Caja</dt><dd class="text-sm font-medium text-gray-900">{{ formatear(producto.precio_venta_caja) }}</dd></div>
                        <div class="border-t border-gray-100 pt-2"><dt class="text-sm text-gray-500">Costo compra</dt><dd class="text-sm font-medium text-gray-900">{{ formatear(producto.costo_compra_unidad) }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
