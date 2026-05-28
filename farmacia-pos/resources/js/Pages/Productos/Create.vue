<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'

const form = useForm({
    codigo_barras: '',
    nombre_comercial: '',
    nombre_generico: '',
    principio_activo: '',
    concentracion: '',
    forma_farmaceutica: '',
    laboratorio: '',
    presentacion_entrada: 'unidad',
    unidades_por_blister: 0,
    blisters_por_caja: 0,
    fraccionamiento_habilitado: false,
    precio_venta_unidad: 0,
    precio_venta_blister: 0,
    precio_venta_caja: 0,
    costo_compra_unidad: 0,
    stock_unidades: 0,
    stock_minimo_alertas: 5,
    seccion: '',
    estante: '',
    controlado: false,
    refrigerado: false,
    activo: true,
})

function submit() {
    form.post(route('productos.store'))
}
</script>

<template>
    <AppLayout>
        <Head title="Nuevo Producto" />
        <PageHeader title="Nuevo Producto" description="Registra un nuevo producto en el inventario">
            <template #actions>
                <BtnDanger @click="form.get(route('productos.index'))">Cancelar</BtnDanger>
            </template>
        </PageHeader>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nombre Comercial *</label>
                        <input v-model="form.nombre_comercial" type="text" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        <p v-if="form.errors.nombre_comercial" class="mt-1 text-xs text-red-600">{{ form.errors.nombre_comercial }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Nombre Genérico</label>
                        <input v-model="form.nombre_generico" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Código de Barras</label>
                        <input v-model="form.codigo_barras" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Principio Activo</label>
                        <input v-model="form.principio_activo" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Concentración</label>
                        <input v-model="form.concentracion" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Presentación Entrada</label>
                        <select v-model="form.presentacion_entrada" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="unidad">Unidad</option>
                            <option value="blister">Blister</option>
                            <option value="caja">Caja</option>
                            <option value="frasco">Frasco</option>
                            <option value="tubo">Tubo</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Unidades/Blister</label>
                        <input v-model.number="form.unidades_por_blister" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Blisters/Caja</label>
                        <input v-model.number="form.blisters_por_caja" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Precio Unidad (Bs)</label>
                        <input v-model.number="form.precio_venta_unidad" type="number" step="0.01" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Precio Blister (Bs)</label>
                        <input v-model.number="form.precio_venta_blister" type="number" step="0.01" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Precio Caja (Bs)</label>
                        <input v-model.number="form.precio_venta_caja" type="number" step="0.01" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Costo Compra (Bs)</label>
                        <input v-model.number="form.costo_compra_unidad" type="number" step="0.01" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Stock Inicial</label>
                        <input v-model.number="form.stock_unidades" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Stock Mínimo</label>
                        <input v-model.number="form.stock_minimo_alertas" type="number" min="0" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Laboratorio</label>
                        <input v-model="form.laboratorio" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Sección</label>
                        <input v-model="form.seccion" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Estante</label>
                        <input v-model="form.estante" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input v-model="form.fraccionamiento_habilitado" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-gray-700">Fraccionamiento habilitado</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.controlado" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-gray-700">Medicamento controlado</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.refrigerado" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-gray-700">Requiere refrigeración</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <BtnDanger type="button" @click="form.get(route('productos.index'))">Cancelar</BtnDanger>
                    <BtnPrimary type="submit" :disabled="form.processing">Crear Producto</BtnPrimary>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
