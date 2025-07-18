<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { useAppointmentBlink } from '@/composables/useAppointmentBlink';
import { storeToRefs } from 'pinia';
import { useNotificationStore } from '@/stores/notification';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);

interface NotificationData {
  id: string;
  type: string;
  read_at: string | null;
  created_at: string;
  data: {
    message: string;
    body?: string;
    sender_id?: number;
    receiver_id?: number;
    url?: string | null;
  };
}

const notificationStore = useNotificationStore();
const { messageNotifications, unreadMessageCount } = storeToRefs(notificationStore);
const showDropdown = ref(false);
const blink = ref(false);

const { triggerAppointmentBlink } = useAppointmentBlink();

const user = usePage().props.auth.user;

let pollInterval: any = null;

console.log('User in NotificationBell:', user);
console.log('NotificationBell script loaded');

async function fetchNotifications() {
  const res = await axios.get('/api/notifications', { withCredentials: true });
  notificationStore.setMessageNotifications(res.data);
}

async function markAllAsRead() {
  await axios.post('/api/notifications/read', {}, { withCredentials: true });
  notificationStore.markAllMessagesAsRead();
  blink.value = false;
}

async function markNotificationAsRead(notificationId: string) {
  // Optionally, make an API call to mark this notification as read in the backend
  await axios.post('/api/notifications/read', { id: notificationId }, { withCredentials: true });
  notificationStore.messageNotifications = notificationStore.messageNotifications.map(n =>
    n.id === notificationId ? { ...n, read_at: new Date().toISOString() } : n
  );
  notificationStore.unreadMessageCount = notificationStore.messageNotifications.filter(n => !n.read_at && n.type.endsWith('MessageNotification')).length;
}

onMounted(() => {
  fetchNotifications();
  pollInterval = setInterval(fetchNotifications, 3000); // Poll every 3s

  if (window.Echo && user) {
    console.log('Subscribing to', `App.Models.User.${user.id}`);
    window.Echo.private(`App.Models.User.${user.id}`)
      .notification((notification: any) => {
        console.log('Received notification:', notification);
        notificationStore.addMessageNotification(notification);
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

// Clean up polling on unmount
import { onUnmounted } from 'vue';
onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
  <div>
    <div class="relative">
      <button @click="showDropdown = !showDropdown" class="relative">
        <svg :class="['w-6 h-6 transition-all', blink ? 'animate-pulse-bell' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span v-if="messageNotifications.some(n => !n.read_at)" class="absolute top-0 right-0 bg-blue-500 text-white rounded-full text-xs px-1">
          {{ unreadMessageCount }}
        </span>
      </button>
      <transition name="fade">
        <div v-if="showDropdown" class="absolute right-0 mt-2 w-96 max-w-[95vw] rounded-2xl shadow-2xl z-50 bg-white/90 dark:bg-gray-900/90 border border-gray-200 dark:border-gray-800 backdrop-blur-md">
          <!-- Gradient Header -->
          <div class="rounded-t-2xl px-6 py-4 bg-gradient-to-r from-blue-500 via-purple-500 to-blue-700 dark:from-gray-900 dark:via-purple-900 dark:to-blue-900 text-white flex items-center justify-between">
            <span class="font-semibold text-lg flex items-center gap-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8l-4.28 1.07a1 1 0 01-1.22-1.22l1.07-4.28A8.96 8.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              Notifications
            </span>
            <button @click="markAllAsRead" class="text-xs text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded px-3 py-1 transition">Mark all as read</button>
          </div>
          <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            <ul v-if="messageNotifications.length">
              <template v-for="(n, idx) in messageNotifications" :key="n.id">
                <li :class="[
                  'flex items-start gap-3 p-4 group transition',
                  !n.read_at ? 'bg-gradient-to-r from-blue-50 via-purple-50 to-white dark:from-gray-800 dark:via-purple-900 dark:to-gray-900 border-l-4 border-blue-400 dark:border-purple-500 shadow-md' : 'hover:bg-gray-50 dark:hover:bg-gray-800',
                  idx === 0 && !n.read_at ? 'rounded-t-2xl' : '',
                  idx === messageNotifications.length - 1 ? 'rounded-b-2xl' : ''
                ]">
                  <!-- Icon by type -->
                  <span class="inline-flex items-center justify-center rounded-full w-10 h-10 mt-0.5 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300">
                    <svg v-if="n.type.endsWith('MessageNotification')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8l-4.28 1.07a1 1 0 01-1.22-1.22l1.07-4.28A8.96 8.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <svg v-else-if="n.type.endsWith('AppointmentAcceptedNotification')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <svg v-else-if="n.type.endsWith('NewAppointmentNotification')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0v4m0-4h4m-4 0H8" /></svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /></svg>
                  </span>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <div class="font-medium text-blue-700 dark:text-blue-200 truncate">
                        {{ n.data.message.replace(/^New message from /, '') }}
                      </div>
                      <span v-if="!n.read_at" class="ml-2 text-xs bg-blue-200 dark:bg-purple-700 text-blue-800 dark:text-white rounded px-2 py-0.5">New</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ n.data.body ?? '' }}</div>
                    <span class="block text-xs text-gray-400 dark:text-gray-500 mt-1">{{ dayjs(n.created_at).fromNow() }}</span>
                  </div>
                  <button v-if="!n.read_at" @click="markNotificationAsRead(n.id)" class="ml-2 text-xs text-blue-600 dark:text-blue-300 hover:underline">Mark as read</button>
                </li>
              </template>
            </ul>
            <div v-else class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
              <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8l-4.28 1.07a1 1 0 01-1.22-1.22l1.07-4.28A8.96 8.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              <div class="text-lg font-semibold">No notifications</div>
              <div class="text-sm mt-1">You're all caught up!</div>
            </div>
          </div>
        </div>
      </transition>
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