<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'

const props = defineProps({
    proveedores: { type: Array, required: true },
    productos: { type: Array, required: true },
})

const form = useForm({
    supplier_id: '',
    fecha_orden: new Date().toISOString().split('T')[0],
    observaciones: '',
    items: [{ product_id: '', presentacion_comprada: 'unidad', cantidad: 1, costo_unitario: 0, lote: '', fecha_vencimiento: '' }],
})

function agregarItem() {
    form.items.push({ product_id: '', presentacion_comprada: 'unidad', cantidad: 1, costo_unitario: 0, lote: '', fecha_vencimiento: '' })
}

function quitarItem(index) {
    form.items.splice(index, 1)
}

function submit() {
    form.post(route('compras.store'))
}
</script>

<template>
    <AppLayout>
        <Head title="Nueva Compra" />
        <PageHeader title="Nueva Compra" description="Registra una orden de compra a proveedor">
            <template #actions>
                <BtnDanger @click="form.get(route('compras.index'))">Cancelar</BtnDanger>
            </template>
        </PageHeader>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Proveedor *</label>
                        <select v-model="form.supplier_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Seleccionar proveedor</option>
                            <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                        <p v-if="form.errors.supplier_id" class="mt-1 text-xs text-red-600">{{ form.errors.supplier_id }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Fecha de Orden</label>
                        <input v-model="form.fecha_orden" type="date" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Observaciones</label>
                    <textarea v-model="form.observaciones" rows="2" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Productos</h3>
                        <BtnPrimary type="button" @click="agregarItem" class="text-xs">Agregar Item</BtnPrimary>
                    </div>

                    <div v-for="(item, i) in form.items" :key="i" class="mb-3 rounded-lg border border-gray-200 p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500">Item #{{ i + 1 }}</span>
                            <button v-if="form.items.length > 1" type="button" class="text-xs text-red-600 hover:text-red-800" @click="quitarItem(i)">Eliminar</button>
                        </div>
                        <div class="grid grid-cols-5 gap-3">
                            <div class="col-span-2">
                                <label class="mb-1 block text-xs text-gray-500">Producto</label>
                                <select v-model="item.product_id" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="">Seleccionar</option>
                                    <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.nombre_comercial }} ({{ p.codigo_interno }})</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-gray-500">Presentación</label>
                                <select v-model="item.presentacion_comprada" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="unidad">Unidad</option>
                                    <option value="blister">Blister</option>
                                    <option value="caja">Caja</option>
                                    <option value="frasco">Frasco</option>
                                    <option value="tubo">Tubo</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-gray-500">Cantidad</label>
                                <input v-model.number="item.cantidad" type="number" min="0.01" step="1" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-gray-500">Costo Unit. (Bs)</label>
                                <input v-model.number="item.costo_unitario" type="number" min="0" step="0.01" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.items" class="text-xs text-red-600">{{ form.errors.items }}</p>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <BtnDanger type="button" @click="form.get(route('compras.index'))">Cancelar</BtnDanger>
                    <BtnPrimary type="submit" :disabled="form.processing">Crear Compra</BtnPrimary>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
