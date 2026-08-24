importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: 'AIzaSyCx5k2Ex9fvtW4S6AAFSAvk-gw-D5xO3lk',
  appId: '1:1009458279788:web:b106e0341471b9dc68af49',
  messagingSenderId: '1009458279788',
  projectId: 'hanva-app',
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const payload = event.notification.data || {};
  const url = payload.admin_url || payload.url || payload.FCM_MSG?.data?.admin_url || '/admin/notifications';
  event.waitUntil(clients.openWindow(url));
});
