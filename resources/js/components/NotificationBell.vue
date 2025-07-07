<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { useAppointmentBlink } from '@/composables/useAppointmentBlink';

const notifications = ref([]);
const unreadCount = ref(0);
const showDropdown = ref(false);
const blink = ref(false);

const { triggerAppointmentBlink } = useAppointmentBlink();

const user = usePage().props.auth.user;

console.log('User in NotificationBell:', user);

async function fetchNotifications() {
  const res = await axios.get('/api/notifications', { withCredentials: true });
  notifications.value = res.data;
  unreadCount.value = notifications.value.filter(n => !n.read_at).length;
}

async function markAllAsRead() {
  await axios.post('/api/notifications/read', {}, { withCredentials: true });
  unreadCount.value = 0;
  notifications.value.forEach(n => n.read_at = new Date().toISOString());
  blink.value = false;
}

onMounted(() => {
  fetchNotifications();

  if (window.Echo && user) {
    console.log('Subscribing to', `App.Models.User.${user.id}`);
    Echo.private(`App.Models.User.${user.id}`)
      .notification((notification) => {
        console.log('Received notification:', notification);
        notifications.value.unshift(notification);
        unreadCount.value++;
        blink.value = true;
        setTimeout(() => { blink.value = false; }, 1200); // 1.2s blink
        // Trigger appointment blink for relevant notifications
        if (
          notification.type?.endsWith('AppointmentAcceptedNotification') ||
          notification.type?.endsWith('NewAppointmentNotification')
        ) {
          triggerAppointmentBlink();
        }
      });
  }
});
</script>

<template>
  <div class="relative">
    <button @click="showDropdown = !showDropdown" class="relative">
      <svg :class="['w-6 h-6 transition-all', blink ? 'animate-pulse-bell' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span v-if="unreadCount" class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs px-1">
        {{ unreadCount }}
      </span>
    </button>
    <div v-if="showDropdown" class="absolute right-0 mt-2 w-80 bg-white border rounded shadow-lg z-50">
      <div class="p-2 flex justify-between items-center">
        <span class="font-semibold">Notifications</span>
        <button @click="markAllAsRead" class="text-xs text-blue-600">Mark all as read</button>
      </div>
      <ul>
        <li v-for="n in notifications" :key="n.id" :class="{'bg-gray-100': !n.read_at}" class="p-2 border-b">
          <a :href="n.data.url" class="block">
            {{ n.data.message }}
            <span class="block text-xs text-gray-400">{{ new Date(n.created_at).toLocaleString() }}</span>
          </a>
        </li>
      </ul>
      <div v-if="!notifications.length" class="p-2 text-center text-gray-400">No notifications</div>
    </div>
  </div>
</template>

<style>
@keyframes pulse-bell {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.25); }
}
.animate-pulse-bell {
  animation: pulse-bell 1.2s cubic-bezier(0.4, 0, 0.6, 1);
}
</style> 