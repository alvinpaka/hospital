import { ref } from 'vue';

const appointmentBlink = ref(false);

export function useAppointmentBlink() {
    function triggerAppointmentBlink() {
        appointmentBlink.value = true;
        setTimeout(() => { appointmentBlink.value = false; }, 1200);
    }
    return { appointmentBlink, triggerAppointmentBlink };
} 