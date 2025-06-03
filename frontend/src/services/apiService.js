import axios from 'axios';

// Создаем экземпляр axios с базовым URL
const apiClient = axios.create({
  baseURL: 'https://api.ticket-now.ru/backend',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Перехватчик для добавления токена авторизации
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

const apiService = {
  // Авторизация
  auth: {
    login(credentials) {
      return apiClient.post('/auth/login', credentials);
    },
  },

  // Тип деятельности
  typeActivity: {
    getAll() {
      return apiClient.get('/type-activity');
    },
    create(data) {
      const formData = new FormData();
      formData.append('title', data.title);
      if (data.image) {
        formData.append('image', data.image);
      }
      return apiClient.post('/type-activity', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    update(id, data) {
      const formData = new FormData();
      formData.append('title', data.title);
      if (data.image) {
        formData.append('image', data.image);
      }
      return apiClient.put(`/type-activity/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    delete(id) {
      return apiClient.delete(`/type-activity/${id}`);
    },
  },

  // События
  events: {
    getAll() {
      return apiClient.get('/events');
    },
    getOne(id) {
      return apiClient.get(`/events/${id}`);
    },
    create(data) {
      const formData = new FormData();
      formData.append('id_type_activity', data.id_type_activity);
      formData.append('id_platform', data.id_platform);
      formData.append('title', data.title);
      formData.append('date', data.date);
      formData.append('time', data.time);
      formData.append('description', data.description);
      formData.append('price', data.price);
      if (data.image) {
        formData.append('image', data.image);
      }
      if (data.events_ticket) {
        formData.append('events_ticket', JSON.stringify(data.events_ticket));
      }
      if (data.events_ticket_new) {
        formData.append('events_ticket_new', JSON.stringify(data.events_ticket_new));
      }
      return apiClient.post('/events', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    update(id, data) {
      const formData = new FormData();
      formData.append('id_type_activity', data.id_type_activity);
      formData.append('id_platform', data.id_platform);
      formData.append('title', data.title);
      formData.append('date', data.date);
      formData.append('time', data.time);
      formData.append('description', data.description);
      formData.append('price', data.price);
      if (data.image) {
        formData.append('image', data.image);
      }
      if (data.events_ticket) {
        formData.append('events_ticket', JSON.stringify(data.events_ticket));
      }
      if (data.events_ticket_new) {
        formData.append('events_ticket_new', JSON.stringify(data.events_ticket_new));
      }
      return apiClient.put(`/events/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    delete(id) {
      return apiClient.delete(`/events/${id}`);
    },
    deleteTicket(id) {
      return apiClient.delete(`/events/ticket/${id}`);
    },
  },

  // Площадки
  platform: {
    getAll() {
      return apiClient.get('/platform');
    },
    getOne(id) {
      return apiClient.get(`/platform/${id}`);
    },
    create(data) {
      const formData = new FormData();
      formData.append('title', data.title);
      if (data.image) {
        formData.append('image', data.image);
      }
      if (data.platform_ticket) {
        formData.append('platform_ticket', JSON.stringify(data.platform_ticket));
      }
      return apiClient.post('/platform', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    update(id, data) {
      const formData = new FormData();
      formData.append('title', data.title);
      if (data.image) {
        formData.append('image', data.image);
      }
      if (data.platform_ticket) {
        formData.append('platform_ticket', JSON.stringify(data.platform_ticket));
      }
      if (data.platform_ticket_new) {
        formData.append('platform_ticket_new', JSON.stringify(data.platform_ticket_new));
      }
      return apiClient.put(`/platform/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    delete(id) {
      return apiClient.delete(`/platform/${id}`);
    },
    deleteTicket(id) {
      return apiClient.delete(`/platform/ticket/${id}`);
    },
  },

  // Новости
  news: {
    getAll() {
      return apiClient.get('/news');
    },
    create(data) {
      const formData = new FormData();
      formData.append('title', data.title);
      // Предполагается, что новости могут содержать дополнительные поля, например, content
      if (data.description) {
        formData.append('description', data.description);
      }
      return apiClient.post('/news', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    update(id, data) {
      const formData = new FormData();
      formData.append('title', data.title);
      if (data.description) {
        formData.append('description', data.description);
      }
      return apiClient.put(`/news/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    delete(id) {
      return apiClient.delete(`/news/${id}`);
    },
  },

  // Заказы
  orders: {
    getAll() {
      return apiClient.get('/orders');
    },
    create(data) {
      return apiClient.post('/orders', {
        id_events: data.id_events,
        name: data.name,
        email: data.email,
        phone: data.phone,
        orders_ticket: data.orders_ticket,
      });
    },
  },
};

export default apiService;