import { ref } from 'vue'

const visible = ref(false)
const message = ref('')
const type = ref('success')

let timer = null

export function useToast() {
    function show(msg, t = 'success', duration = 3000) {
        clearTimeout(timer)
        message.value = msg
        type.value = t
        visible.value = true
        timer = setTimeout(() => {
            visible.value = false
        }, duration)
    }

    function hide() {
        visible.value = false
        clearTimeout(timer)
    }

    function success(msg) {
        show(msg, 'success')
    }

    function error(msg) {
        show(msg, 'error')
    }

    function warning(msg) {
        show(msg, 'warning')
    }

    function info(msg) {
        show(msg, 'info')
    }

    return {
        visible,
        message,
        type,
        show,
        hide,
        success,
        error,
        warning,
        info,
    }
}
