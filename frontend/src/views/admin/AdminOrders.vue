<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Управление заказами</h1>
      <router-link to="/admin/dashboard" class="back-btn">Назад</router-link>
    </header>
    
    <div class="filters">
      
      <div class="filter-group">
        <label for="search">Поиск:</label>
        <input 
          id="search" 
          type="text" 
          v-model="filters.search" 
          placeholder="Имя, email или телефон"
        >
      </div>
      
      <button @click="applyFilters" class="btn">Применить</button>
    </div>
    
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Событие</th>
            <th>Клиент</th>
            <th>Email</th>
            <th>Телефон</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id_orders">
            <td>{{ order.id_orders }}</td>
            <td>{{ getEventName(order.id_events) }}</td>
            <td>{{ order.name }}</td>
            <td>{{ order.email }}</td>
            <td>{{ order.phone }}</td>
            <td>
              <button @click="viewOrder(order)" class="btn-small">Подробно</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Модальное окно просмотра заказа -->
    <div v-if="selectedOrder" class="modal">
      <div class="modal-content wide">
        <span class="close" @click="selectedOrder = null">&times;</span>
        <h2>Заказ #{{ selectedOrder.id_orders }}</h2>
        
        <div class="order-details">
          <div class="detail-row">
            <div class="detail-item">
              <strong>Событие:</strong> {{ getEventName(selectedOrder.id_events) }}
            </div>
            <div class="detail-item">
              <strong>Дата создания:</strong> {{ new Date(selectedOrder.created_at).toLocaleString() }}
            </div>
          </div>
          
          <div class="detail-row">
            <div class="detail-item">
              <strong>Имя клиента:</strong> {{ selectedOrder.name }}
            </div>
            <div class="detail-item">
              <strong>Email:</strong> {{ selectedOrder.email }}
            </div>
            <div class="detail-item">
              <strong>Телефон:</strong> {{ selectedOrder.phone }}
            </div>
          </div>
          
          <div class="detail-row">
            <div class="detail-item">
              <strong>Статус:</strong> 
              <span :class="statusClass(selectedOrder.status)">
                {{ statusText(selectedOrder.status) }}
              </span>
            </div>
            <div class="detail-item">
              <strong>Сумма заказа:</strong> {{ selectedOrder.total }} ₽
            </div>
          </div>
          
          <h3>Билеты</h3>
          <table class="tickets-table">
            <thead>
              <tr>
                <th>Тип билета</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(ticket, index) in selectedOrder.orders_ticket" :key="index">
                <td>{{ ticket.title }}</td>
                <td>{{ ticket.price }} ₽</td>
                <td>{{ ticket.quantity }}</td>
                <td>{{ ticket.price * ticket.quantity }} ₽</td>
              </tr>
              <tr class="total-row">
                <td colspan="3" class="text-right"><strong>Итого:</strong></td>
                <td><strong>{{ selectedOrder.total }} ₽</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiService from '@/services/apiService';

const orders = ref([]);
const events = ref([]);
const selectedOrder = ref(null);
const statusOrder = ref(null);
const newStatus = ref('');
const filters = ref({
  status: '',
  search: ''
});

const fetchOrders = async () => {
  try {
    const response = await apiService.orders.getAll();
    orders.value = response.data.data.orders || [];
  } catch (error) {
    console.error('Ошибка загрузки заказов:', error);
  }
};

const fetchEvents = async () => {
  try {
    const response = await apiService.events.getAll();
    events.value = response.data.data.events || [];
  } catch (error) {
    console.error('Ошибка загрузки событий:', error);
  }
};

const getEventName = (id) => {
  const event = events.value.find(e => e.id_events === id);
  return event ? event.title : 'Неизвестно';
};

const applyFilters = () => {
  // В реальном приложении здесь бы выполнялся запрос к API с параметрами фильтрации
  alert('Фильтрация реализуется на бэкенде. В демо показаны все заказы.');
};

const viewOrder = (order) => {
  selectedOrder.value = order;
};

const updateStatus = (order) => {
  statusOrder.value = order;
  newStatus.value = order.status;
};

const saveStatus = () => {
  // В реальном приложении здесь бы был вызов API для обновления статуса
  statusOrder.value.status = newStatus.value;
  alert(`Статус заказа #${statusOrder.value.id_orders} изменен на "${statusText(newStatus.value)}"`);
  statusOrder.value = null;
};

const statusText = (status) => {
  const statuses = {
    'pending': 'Ожидает',
    'completed': 'Завершен',
    'canceled': 'Отменен'
  };
  return statuses[status] || status;
};

const statusClass = (status) => {
  return {
    'pending': 'status-pending',
    'completed': 'status-completed',
    'canceled': 'status-canceled'
  }[status];
};

onMounted(() => {
  fetchOrders();
  fetchEvents();
});
</script>

<style scoped>
.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  align-items: flex-end;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  margin-bottom: 0.25rem;
  font-weight: bold;
}

.detail-row {
  display: flex;
  gap: 2rem;
  margin-bottom: 1rem;
}

.detail-item {
  flex: 1;
}

.tickets-table {
  width: 100%;
  margin-top: 1rem;
  border-collapse: collapse;
}

.tickets-table th, 
.tickets-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.tickets-table th {
  background-color: #f5f5f5;
}

.total-row {
  font-weight: bold;
}

.text-right {
  text-align: right;
}

.status-pending {
  color: #ff9800;
  font-weight: bold;
}

.status-completed {
  color: #4caf50;
  font-weight: bold;
}

.status-canceled {
  color: #f44336;
  font-weight: bold;
}

.modal-content.wide {
  max-width: 800px;
}

.admin-container {
  padding: 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #eee;
  margin-bottom: 2rem;
  gap: 50px;
}

.back-btn {
  padding: 0.5rem 1rem;
  background-color: #2196F3;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

.actions {
  margin-bottom: 1rem;
}

.btn {
  padding: 0.5rem 1rem;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-small {
  padding: 0.3rem 0.6rem;
  margin-right: 0.5rem;
  background-color: #2196F3;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.danger {
  background-color: #f44336;
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f5f5f5;
}

tr:hover {
  background-color: #f9f9f9;
}

/* Modal styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 100%;
  max-width: 500px;
  position: relative;
}

.close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  cursor: pointer;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
}

.form-group input[type="text"],
.form-group input[type="file"] {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>