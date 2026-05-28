<script setup>
import { ref, computed } from 'vue'
import InputNumber from '@/Components/UI/InputNumber.vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'

const props = defineProps({
    producto: { type: Object, required: true },
})

const emit = defineEmits(['confirmar', 'cancelar'])

const presentacion = ref('unidad')
const cantidad = ref(1)
const requiereReceta = ref(false)

const presentaciones = computed(() => {
    const list = []
    if (props.producto.precio_venta_unidad || props.producto.precio_venta_unidad === 0) {
        list.push({ key: 'unidad', label: 'Unidad' })
    }
    if ((props.producto.precio_venta_blister || props.producto.precio_venta_blister === 0) && props.producto.unidades_por_blister) {
        list.push({ key: 'blister', label: `Blister (${props.producto.unidades_por_blister} uds)` })
    }
    if ((props.producto.precio_venta_caja || props.producto.precio_venta_caja === 0) && props.producto.blisters_por_caja) {
        list.push({ key: 'caja', label: `Caja (${props.producto.unidades_por_blister * props.producto.blisters_por_caja} uds)` })
    }
    return list
})

const precioActual = computed(() => {
    return props.producto[`precio_venta_${presentacion.value}`] || 0
})

const subtotal = computed(() => cantidad.value * precioActual.value)

const stockDisponible = computed(() => {
    if (presentacion.value === 'unidad') return props.producto.stock || 0
    if (presentacion.value === 'blister') return Math.floor((props.producto.stock || 0) / (props.producto.unidades_por_blister || 1))
    return Math.floor((props.producto.stock || 0) / ((props.producto.unidades_por_blister || 1) * (props.producto.blisters_por_caja || 1)))
})

const stockBajo = computed(() => stockDisponible.value <= (props.producto.stock_minimo || 5) && stockDisponible.value > 0)
const sinStock = computed(() => stockDisponible.value <= 0)

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}

function confirmar() {
    emit('confirmar', {
        producto: props.producto,
        presentacion: presentacion.value,
        cantidad: cantidad.value,
        receta: requiereReceta.value,
    })
}
</script>

<template>
    <div class="space-y-4">
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900">{{ producto.nombre_comercial }}</h3>
            <p v-if="producto.nombre_generico" class="text-sm text-gray-500">{{ producto.nombre_generico }}</p>
        </div>

        <div v-if="sinStock" class="rounded-lg bg-red-50 p-3 text-center text-sm font-medium text-red-700">
            Producto sin stock
        </div>
        <div v-else-if="stockBajo" class="rounded-lg bg-yellow-50 p-3 text-center text-sm font-medium text-yellow-700">
            Stock bajo: {{ stockDisponible }} disponibles
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Presentación</label>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="p in presentaciones"
                    :key="p.key"
                    :class="presentacion === p.key ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'"
                    class="rounded-lg border px-3 py-2 text-sm font-medium transition"
                    @click="presentacion = p.key"
                >
                    {{ p.label }}
                </button>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex-1">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Cantidad</label>
                <InputNumber v-model="cantidad" :min="1" :max="stockDisponible || 999" />
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Precio unit.</p>
                <p class="text-lg font-semibold text-gray-900">{{ formatear(precioActual) }}</p>
            </div>
        </div>

        <div v-if="producto.requiere_receta" class="flex items-center gap-2">
            <input
                id="receta"
                v-model="requiereReceta"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="receta" class="text-sm text-gray-700">Requiere receta médica</label>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 pt-3">
            <span class="text-sm text-gray-600">Subtotal</span>
            <span class="text-xl font-bold text-gray-900">{{ formatear(subtotal) }}</span>
        </div>

        <div class="flex gap-2">
            <button
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="$emit('cancelar')"
            >
                Cancelar
            </button>
            <BtnPrimary
                class="flex-1"
                :disabled="sinStock"
                @click="confirmar"
            >
                Agregar al carrito
            </BtnPrimary>
        </div>
    </div>
</template>
