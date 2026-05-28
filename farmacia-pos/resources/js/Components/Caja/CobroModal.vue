<script setup>
import { ref, computed } from 'vue'
import BtnPrimary from '@/Components/UI/BtnPrimary.vue'
import BtnDanger from '@/Components/UI/BtnDanger.vue'

const props = defineProps({
    total: { type: Number, required: true },
    talonarios: { type: Array, default: () => [] },
})

const emit = defineEmits(['confirmar', 'cancelar'])

const tipoDocumento = ref('consumidor_final')
const nit = ref('')
const razonSocial = ref('')
const metodoPago = ref('efectivo')
const montoRecibido = ref(0)
const talonarioId = ref('')

const cambio = computed(() => {
    if (metodoPago.value !== 'efectivo') return 0
    return Math.max(0, (Number(montoRecibido.value) || 0) - props.total)
})

const falta = computed(() => {
    if (metodoPago.value !== 'efectivo') return 0
    return Math.max(0, props.total - (Number(montoRecibido.value) || 0))
})

const metodosPago = [
    { key: 'efectivo', label: 'Efectivo', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    { key: 'qr_bancario', label: 'QR Bancario', icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z' },
    { key: 'tarjeta', label: 'Tarjeta', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    { key: 'transferencia', label: 'Transferencia', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
]

function confirmarCobro() {
    if (falta.value > 0) return
    emit('confirmar', {
        tipo_documento: tipoDocumento.value,
        nit: tipoDocumento.value !== 'consumidor_final' ? nit.value : null,
        razon_social: tipoDocumento.value === 'factura_manual' ? razonSocial.value : null,
        metodo_pago: metodoPago.value,
        monto_recibido: metodoPago.value === 'efectivo' ? Number(montoRecibido.value) : props.total,
        cambio: cambio.value,
        talonario_id: talonarioId.value || null,
    })
}

function formatear(valor) {
    return Number(valor || 0).toLocaleString('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    })
}
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Cobro</h3>
        <p class="text-3xl font-bold text-gray-900 text-center">{{ formatear(total) }}</p>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Tipo de Documento</label>
            <select
                v-model="tipoDocumento"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
                <option value="consumidor_final">Consumidor Final</option>
                <option value="con_nit">Con NIT</option>
                <option value="factura_manual">Factura Manual</option>
            </select>
        </div>

        <div v-if="tipoDocumento !== 'consumidor_final'" class="space-y-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">NIT / CI</label>
                <input
                    v-model="nit"
                    type="text"
                    maxlength="15"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Ej: 1234567890"
                />
                <p v-if="nit && nit.length < 6" class="mt-1 text-xs text-yellow-600">
                    Verifica que el NIT sea correcto
                </p>
            </div>
            <div v-if="tipoDocumento === 'factura_manual'">
                <label class="mb-1 block text-xs font-medium text-gray-600">Razón Social</label>
                <input
                    v-model="razonSocial"
                    type="text"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Razón social"
                />
            </div>
        </div>

        <div v-if="talonarios.length">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Talonario</label>
            <select
                v-model="talonarioId"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
                <option value="">Sin talonario</option>
                <option v-for="t in talonarios" :key="t.id" :value="t.id">
                    {{ t.nombre || `Talonario #${t.id}` }} ({{ t.disponibles }} disp.)
                </option>
            </select>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Método de Pago</label>
            <div class="grid grid-cols-2 gap-2">
                <button
                    v-for="mp in metodosPago"
                    :key="mp.key"
                    :class="metodoPago === mp.key ? 'border-blue-500 bg-blue-50 text-blue-700 ring-1 ring-blue-500' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'"
                    class="flex items-center gap-2 rounded-lg border px-3 py-3 text-sm font-medium transition"
                    @click="metodoPago = mp.key"
                >
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="mp.icon" />
                    </svg>
                    {{ mp.label }}
                </button>
            </div>
        </div>

        <div v-if="metodoPago === 'efectivo'">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Monto Recibido</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">Bs</span>
                <input
                    v-model.number="montoRecibido"
                    type="number"
                    min="0"
                    step="0.01"
                    class="block w-full rounded-lg border border-gray-300 px-8 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="0.00"
                />
            </div>
            <div v-if="cambio > 0" class="mt-2 rounded-lg bg-green-50 p-2 text-center text-sm font-medium text-green-700">
                Cambio: {{ formatear(cambio) }}
            </div>
            <div v-if="falta > 0" class="mt-2 rounded-lg bg-red-50 p-2 text-center text-sm font-medium text-red-700">
                Faltan: {{ formatear(falta) }}
            </div>
        </div>

        <div class="flex gap-2 pt-2">
            <BtnDanger class="flex-1" @click="$emit('cancelar')">
                Cancelar
            </BtnDanger>
            <BtnPrimary
                class="flex-1"
                :disabled="falta > 0"
                @click="confirmarCobro"
            >
                Confirmar Cobro
            </BtnPrimary>
        </div>
    </div>
</template>
