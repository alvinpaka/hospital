import { ref } from 'vue';

const prescriptionBlink = ref(false);

export function usePrescriptionBlink() {
    function triggerBlink() {
        prescriptionBlink.value = true;
        setTimeout(() => { prescriptionBlink.value = false; }, 1200);
    }
    return { prescriptionBlink, triggerBlink };
} 