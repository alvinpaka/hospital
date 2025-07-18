<script setup lang="ts">
import { onMounted, onUnmounted, computed, nextTick, watch, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Input from '@/components/ui/input/Input.vue';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import type { AppPageProps } from '@/types';
import { useNotificationStore } from '@/stores/notification';
import { storeToRefs } from 'pinia';
import axios from 'axios';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

interface Message {
  id: number;
  sender_id: number;
  receiver_id: number;
  message: string;
  created_at: string;
  sender?: User;
  receiver?: User;
  read_at?: string;
}

interface Patient {
  id: number;
  name: string;
  email?: string;
}

const props = defineProps<{ receiverId?: number }>();

const page = usePage<AppPageProps<{ adminId?: number }>>();
const currentUser = computed<User>(() => page.props.auth.user);
const messages = ref<Message[]>([]);
const newMessage = ref('');
const loading = ref(false);
const polling = ref(false);
const error = ref('');
const chatBoxRef = ref<HTMLElement | null>(null);
let lastMessageId: number | null = null;
let pollTimeout: ReturnType<typeof setTimeout> | null = null;

const showChat = ref(false);

// Admin: patient search and selection
const patientSearch = ref('');
const patientResults = ref<Patient[]>([]);
const patientLoading = ref(false);
const selectedPatient = ref<Patient | null>(null);

// Auto-detect receiverId from page context if not provided
const detectedReceiverId = ref<number | null>(null);
const patientName = ref<string | null>(null);
const adminId = ref<number | null>(null);

const isEchoActive = ref(false);

async function searchPatients(query: string) {
  if (!query || query.length < 2) {
    patientResults.value = [];
    return;
  }
  patientLoading.value = true;
  try {
    const res = await fetch(`/api/patients?per_page=10&search=${encodeURIComponent(query)}`, {
      credentials: 'include',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (res.ok) {
      const data = await res.json();
      patientResults.value = data.data || data.patients?.data || [];
    }
  } finally {
    patientLoading.value = false;
  }
}

function selectPatient(p: Patient) {
  selectedPatient.value = { ...p };
  patientSearch.value = p.name;
  patientResults.value = [];
}

function detectReceiverId() {
  console.log('[detectReceiverId] selectedPatient:', selectedPatient.value, 'currentUser:', currentUser.value, 'adminId:', adminId.value);
  // Admin: use selected patient
  if (currentUser.value?.role === 'admin') {
    if (selectedPatient.value) {
      detectedReceiverId.value = selectedPatient.value.id;
      patientName.value = selectedPatient.value.name;
      adminId.value = currentUser.value.id;
      console.log('[detectReceiverId] set detectedReceiverId:', detectedReceiverId.value, 'patientName:', patientName.value, 'adminId:', adminId.value);
      return;
    }
    detectedReceiverId.value = null;
    patientName.value = null;
    adminId.value = currentUser.value.id;
    return;
  }
  // If patient viewing their own profile, allow chat with admin
  if (currentUser.value?.role === 'patient') {
    adminId.value = page.props.adminId ?? null;
    detectedReceiverId.value = adminId.value;
    patientName.value = currentUser.value.name;
    return;
  }
  detectedReceiverId.value = null;
  patientName.value = null;
  adminId.value = null;
}

watch(selectedPatient, (val) => {
  console.log('[watch selectedPatient]', val);
  detectReceiverId();
  messages.value = [];
  lastMessageId = null;
  if (receiverId.value) fetchMessages(true);
});

// Use prop if provided, else detected
const receiverId = computed<number | null>(() => props.receiverId ?? detectedReceiverId.value);

// Only allow chat if (1) admin chatting with patient, or (2) patient chatting with admin
const canChat = computed(() => {
  if (!receiverId.value) return false;
  if (currentUser.value?.role === 'admin' && receiverId.value) return true;
  if (currentUser.value?.role === 'patient' && receiverId.value === (adminId.value || 1)) return true;
  return false;
});

function scrollToBottom(): void {
  if (chatBoxRef.value) {
    chatBoxRef.value.scrollTop = chatBoxRef.value.scrollHeight;
  }
}

function isUserNearBottom(): boolean {
  if (!chatBoxRef.value) return true;
  const threshold = 60; // px, adjust as needed
  const { scrollTop, scrollHeight, clientHeight } = chatBoxRef.value;
  return scrollHeight - scrollTop - clientHeight < threshold;
}

const userHasInteracted = ref(false);

function onUserInteraction() {
  userHasInteracted.value = true;
  // Remove listeners after first interaction
  window.removeEventListener('click', onUserInteraction);
  window.removeEventListener('keydown', onUserInteraction);
  window.removeEventListener('touchstart', onUserInteraction);
}

function playReceiveSound() {
  if (userHasInteracted.value) {
    const audio = new Audio('/sounds/message-received.mp3');
    audio.play();
  }
}

async function fetchMessages(initial = false): Promise<void> {
  if (!receiverId.value) {
    console.log('fetchMessages: No receiverId');
    return;
  }
  try {
    const url = `/api/messages/poll?with_user_id=${receiverId.value}` + (lastMessageId ? `&since=${lastMessageId}` : '');
    polling.value = true;
    const res = await fetch(url, {
      credentials: 'include',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    console.log('fetchMessages: url', url, 'status', res.status);
    if (res.ok) {
      const data = await res.json();
      // Always set messages.value, even if empty
      if (data.messages) {
        const wasNearBottom = isUserNearBottom();
        let newMessages: Message[] = [];
        if (!lastMessageId || initial) {
          messages.value = data.messages;
        } else {
          const existingIds = new Set(messages.value.map((m: Message) => m.id));
          newMessages = data.messages.filter((m: Message) => !existingIds.has(m.id));
          if (newMessages.length > 0) {
            messages.value.push(...newMessages);
          }
        }
        lastMessageId = messages.value.length > 0 ? messages.value[messages.value.length - 1].id : null;
        // If admin and patientName is missing, infer from messages
        if (currentUser.value?.role === 'admin' && !patientName.value && messages.value.length > 0) {
          const firstMsg = messages.value[0];
          if (firstMsg.sender && firstMsg.sender.role === 'patient') {
            patientName.value = firstMsg.sender.name;
          } else if (firstMsg.receiver && firstMsg.receiver.role === 'patient') {
            patientName.value = firstMsg.receiver.name;
          }
        }
        await nextTick();
        if (wasNearBottom || initial) scrollToBottom();
        // Play sound if new messages from other user, but only if Echo is not active
        if (!isEchoActive.value && newMessages && newMessages.length > 0) {
          const received = newMessages.some((m: Message) => m.sender_id !== currentUser.value.id);
          if (received) playReceiveSound();
        }
        console.log('fetchMessages: messages.value', messages.value);
      }
    } else {
      console.log('fetchMessages: response not ok', res.status);
    }
  } catch (e) {
    console.log('fetchMessages: error', e);
    // Ignore polling errors
  } finally {
    polling.value = false;
    pollTimeout = setTimeout(() => fetchMessages(false), 1000);
  }
}

function getCookie(name: string): string | undefined {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift();
}

async function ensureCsrfCookie(): Promise<void> {
  if (!getCookie('XSRF-TOKEN')) {
    await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
  }
}

async function sendMessage(): Promise<void> {
  if (!newMessage.value.trim() || !receiverId.value) {
    if (currentUser.value?.role === 'admin' && !receiverId.value) {
      error.value = 'Please select a patient to chat with.';
    }
    return;
  }
  loading.value = true;
  error.value = '';
  try {
    await ensureCsrfCookie();
    const res = await fetch('/api/messages/send', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(getCookie('XSRF-TOKEN') || ''),
      },
      body: JSON.stringify({
        receiver_id: receiverId.value,
        message: newMessage.value.trim(),
      }),
    });
    if (res.ok) {
      newMessage.value = '';
      // After sending, reload all messages to avoid duplicates
      lastMessageId = null;
      await fetchMessages(true);
      // Mark the notification from this receiver as read
      if (receiverId.value) {
        notificationStore.markMessageNotificationAsReadBySender(receiverId.value);
      }
    } else {
      const data = await res.json();
      error.value = data.error || 'Failed to send message.';
    }
  } catch {
    error.value = 'Failed to send message.';
  } finally {
    loading.value = false;
  }
}

declare global {
  interface Window {
    Echo?: any;
  }
}

onMounted(() => {
  detectReceiverId();
  if (receiverId.value) fetchMessages(true);

  window.addEventListener('click', onUserInteraction, { once: true });
  window.addEventListener('keydown', onUserInteraction, { once: true });
  window.addEventListener('touchstart', onUserInteraction, { once: true });

  // Listen for real-time message notifications (if Echo is available)
  if (window.Echo && currentUser.value) {
    isEchoActive.value = true;
    window.Echo.private(`App.Models.User.${currentUser.value.id}`)
      .notification((notification: { type: string; message: string; sender_id: number }) => {
        if (notification.type?.endsWith('MessageNotification')) {
          // If admin, set selectedPatient to sender
          if (currentUser.value.role === 'admin') {
            const senderName = notification.message && typeof notification.message === 'string'
              ? notification.message.replace('New message from ', '')
              : '';
            selectedPatient.value = {
              id: Number(notification.sender_id),
              name: senderName,
            };
            patientSearch.value = senderName;
            // This will trigger detectReceiverId and fetchMessages
            playReceiveSound();
          }
          // If patient, just fetch messages
          if (currentUser.value.role === 'patient') {
            fetchMessages(true);
            playReceiveSound();
          }
        }
      });
  }
});
onUnmounted(() => {
  if (pollTimeout) clearTimeout(pollTimeout);
});

watch(receiverId, (val: number | null) => {
  messages.value = [];
  lastMessageId = null;
  if (val) fetchMessages(true);
});

const notificationStore = useNotificationStore();
const { unreadMessageCount } = storeToRefs(notificationStore);

watch(showChat, async (val) => {
  if (val) {
    // Mark as read in backend
    if (receiverId.value) {
      try {
        await axios.post('/api/messages/mark-read', { with_user_id: receiverId.value });
      } catch {
        // Optionally handle error
      }
    }
    notificationStore.markAllMessagesAsRead();
  }
});
</script>

<template>
  <div>
    <button
      class="fixed z-50 bottom-6 right-6 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full shadow-lg p-4 hover:scale-105 transition-all focus:outline-none"
      @click="showChat = !showChat"
      aria-label="Open chat"
    >
      <svg v-if="!showChat" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8l-4.28 1.07a1 1 0 01-1.22-1.22l1.07-4.28A8.96 8.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      <span v-if="unreadMessageCount > 0 && !showChat" class="absolute -top-1 -right-1 bg-blue-500 text-white rounded-full text-xs px-1 min-w-[18px] h-[18px] flex items-center justify-center">{{ unreadMessageCount }}</span>
    </button>
    <transition name="fade">
      <div v-if="showChat" class="fixed z-50 bottom-20 right-6 w-80 max-w-full">
        <Card class="shadow-2xl border-0 bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <span>Chat</span>
              <span v-if="currentUser.role === 'admin' && patientName" class="ml-2 text-base font-normal text-muted-foreground">with {{ patientName }}</span>
              <span v-else-if="currentUser.role === 'patient'" class="ml-2 text-base font-normal text-muted-foreground">with Admin</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="currentUser.role === 'admin'">
              <div class="mb-2">
                <Input v-model="patientSearch" placeholder="Search patient..." @input="searchPatients(patientSearch)" />
                <div v-if="patientSearch.length >= 2 && patientResults.length > 0" class="bg-white dark:bg-gray-800 border rounded shadow mt-1 max-h-40 overflow-y-auto">
                  <div v-for="p in patientResults" :key="p.id" @click="selectPatient(p)" class="px-4 py-2 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900">
                    {{ p.name }} <span class="text-xs text-muted-foreground">{{ p.email }}</span>
                  </div>
                </div>
                <div v-if="patientSearch.length >= 2 && !patientLoading && patientResults.length === 0" class="text-xs text-muted-foreground mt-1 px-2">No patients found.</div>
                <div v-if="patientLoading" class="text-xs text-muted-foreground mt-1 px-2">Searching...</div>
              </div>
            </div>
            <div v-if="!canChat">
              <div class="text-center text-muted-foreground py-8">Select a patient to chat.</div>
            </div>
            <div v-else>
              <div ref="chatBoxRef" class="h-72 overflow-y-auto flex flex-col gap-2 p-2 bg-white/60 dark:bg-gray-900/60 rounded-lg border border-muted-foreground/10">
                <div v-if="messages.length === 0" class="text-center text-muted-foreground py-8">No messages yet.</div>
                <div v-for="msg in messages" :key="msg.id" class="flex items-end gap-2" :class="currentUser && msg.sender_id === currentUser.id ? 'justify-end' : 'justify-start'">
                  <!-- Avatar for received messages -->
                  <template v-if="currentUser && msg.sender_id !== currentUser.id">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-700 dark:text-gray-200">
                      {{ msg.sender?.name ? msg.sender.name.charAt(0) : '?' }}
                    </div>
                  </template>
                  <div>
                    <!-- Sender label -->
                    <div class="text-xs mb-0.5 font-semibold" :class="msg.sender_id === currentUser.id ? 'text-blue-500 text-right' : 'text-purple-600 text-left'">
                      {{ msg.sender_id === currentUser.id ? 'You' : (msg.sender?.name || 'Unknown') }}
                    </div>
                    <!-- Message bubble -->
                    <div :class="msg.sender_id === currentUser.id ? 'bg-gradient-to-r from-blue-400 to-purple-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-black dark:text-white'" class="rounded-2xl px-4 py-2 max-w-xs shadow relative">
                      <div class="text-sm">{{ msg.message }}</div>
                      <!-- Read/Delivered receipt for sent messages -->
                      <template v-if="msg.sender_id === currentUser.id">
                        <span v-if="msg.read_at" class="absolute bottom-1 right-2 text-xs text-green-200 flex items-center gap-1">
                          <svg class="inline w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                          Read
                        </span>
                        <span v-else class="absolute bottom-1 right-2 text-xs text-gray-300 flex items-center gap-1">
                          <svg class="inline w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                          Delivered
                        </span>
                      </template>
                    </div>
                    <!-- Timestamp -->
                    <div class="text-xs mt-1 opacity-60 text-right">
                      {{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                  </div>
                </div>
              </div>
              <form class="flex gap-2 mt-4" @submit.prevent="sendMessage">
                <Input v-model="newMessage" placeholder="Type a message..." :disabled="loading" class="flex-1" />
                <Button type="submit" :disabled="loading || !newMessage.trim() || (currentUser.role === 'admin' && !receiverId)" >Send</Button>
              </form>
              <div v-if="error" class="text-red-600 text-sm mt-2">{{ error }}</div>
            </div>
          </CardContent>
        </Card>
      </div>
    </transition>
  </div>
</template>

<style scoped>

</style> 