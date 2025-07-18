import { defineStore } from 'pinia';

export interface MessageNotification {
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

interface NotificationState {
  unreadMessageCount: number;
  messageNotifications: MessageNotification[];
}

export const useNotificationStore = defineStore('notification', {
  state: (): NotificationState => ({
    unreadMessageCount: 0,
    messageNotifications: [],
  }),
  actions: {
    setUnreadMessageCount(count: number) {
      this.unreadMessageCount = count;
    },
    incrementUnreadMessageCount() {
      this.unreadMessageCount++;
    },
    clearUnreadMessageCount() {
      this.unreadMessageCount = 0;
    },
    setMessageNotifications(notifications: MessageNotification[]) {
      this.messageNotifications = notifications;
      this.unreadMessageCount = notifications.filter(n => !n.read_at && n.type.endsWith('MessageNotification')).length;
    },
    addMessageNotification(notification: MessageNotification) {
      this.messageNotifications.unshift(notification);
      if (!notification.read_at && notification.type.endsWith('MessageNotification')) {
        this.unreadMessageCount++;
      }
    },
    markAllMessagesAsRead() {
      this.messageNotifications.forEach(n => {
        if (n.type.endsWith('MessageNotification')) n.read_at = new Date().toISOString();
      });
      this.unreadMessageCount = 0;
    },
    markMessageNotificationAsReadBySender(senderId: number) {
      let updated = false;
      this.messageNotifications = this.messageNotifications.map(n => {
        if (!n.read_at && n.type.endsWith('MessageNotification') && n.data.sender_id === senderId) {
          updated = true;
          return { ...n, read_at: new Date().toISOString() };
        }
        return n;
      });
      if (updated) {
        this.unreadMessageCount = this.messageNotifications.filter(n => !n.read_at && n.type.endsWith('MessageNotification')).length;
      }
    },
  },
}); 