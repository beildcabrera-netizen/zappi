import { ref, computed } from 'vue'

export function useCobro(totalInicial = 0) {
    const montoRecibido = ref(0)
    const metodoPago = ref('efectivo')
    const tipoDocumento = ref('consumidor_final')
    const nit = ref('')
    const talonarioId = ref(null)

    const cambio = computed(() => {
        if (metodoPago.value !== 'efectivo') return 0
        return Math.max(0, (Number(montoRecibido.value) || 0) - totalInicial)
    })

    const falta = computed(() => {
        if (metodoPago.value !== 'efectivo') return 0
        return Math.max(0, totalInicial - (Number(montoRecibido.value) || 0))
    })

    const puedeCobrar = computed(() => {
        if (metodoPago.value === 'efectivo') return falta.value <= 0 && Number(montoRecibido.value) > 0
        return true
    })

    function reset() {
        montoRecibido.value = 0
        metodoPago.value = 'efectivo'
        tipoDocumento.value = 'consumidor_final'
        nit.value = ''
        talonarioId.value = null
    }

    function getDatosCobro() {
        return {
            tipo_documento: tipoDocumento.value,
            nit: tipoDocumento.value !== 'consumidor_final' ? nit.value : null,
            metodo_pago: metodoPago.value,
            monto_recibido: metodoPago.value === 'efectivo' ? Number(montoRecibido.value) : totalInicial,
            cambio: cambio.value,
            talonario_id: talonarioId.value,
        }
    }

    return {
        montoRecibido,
        metodoPago,
        tipoDocumento,
        nit,
        talonarioId,
        cambio,
        falta,
        puedeCobrar,
        reset,
        getDatosCobro,
    }
}
