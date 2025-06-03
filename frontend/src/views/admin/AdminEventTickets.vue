<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Управление билетами события: {{ event?.title || 'Событие' }}</h1>
      <router-link :to="'/admin/events'" class="back-btn">Назад к событиям</router-link>
    </header>
    
    <div class="actions">
      <button @click="openCreateModal" class="btn">Добавить билет</button>
    </div>
    
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in tickets" :key="ticket.id_events_ticket">
            <td>{{ ticket.id_events_ticket }}</td>
            <td>{{ getPlatformTicketTitle(ticket.id_platform_ticket) }}</td>
            <td>{{ ticket.price }} ₽</td>
            <td>
              <button @click="openEditModal(ticket)" class="btn-small">Ред.</button>
              <button @click="deleteTicket(ticket.id_events_ticket)" class="btn-small danger">Удал.</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Модальное окно создания/редактирования -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeModal">×</span>
        <h2>{{ editingTicket ? 'Редактировать билет' : 'Добавить билет' }}</h2>
        
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="title">Название *</label>
            <select id="title" v-model="formData.id_platform_ticket" required>
              <option value="" disabled>Выберите билет площадки</option>
              <option v-for="platformTicket in platformTickets" :key="platformTicket.id_platform_ticket" :value="platformTicket.id_platform_ticket">
                {{ platformTicket.title }}
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="price">Цена (₽) *</label>
            <input id="price" v-model.number="formData.price" type="number" required>
          </div>
          
          <button type="submit" class="btn">{{ editingTicket ? 'Обновить' : 'Создать' }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import apiService from '@/services/apiService';

export default {
  data() {
    return {
      event: null,
      tickets: [],
      platformTickets: [],
      showModal: false,
      editingTicket: null,
      formData: {
        id_platform_ticket: '',
        price: 0,
      },
    };
  },
  async mounted() {
    await this.fetchEvent();
    if (this.event?.id_platform) {
      await this.fetchPlatformTickets();
    }
  },
  methods: {
    async fetchEvent() {
      try {
        const response = await apiService.events.getOne(this.$route.params.id);
        if (response.data.status === 'success') {
          this.event = response.data.data.event;
          this.tickets = response.data.data.event.events_ticket.data || [];
        } else {
          console.error('Ошибка загрузки события:', response.data.message);
        }
      } catch (error) {
        console.error('Ошибка при получении данных события:', error);
      }
    },

    async fetchPlatformTickets() {
      try {
        const response = await apiService.platform.getOne(this.event.id_platform);
        if (response.data.status === 'success') {
          this.platformTickets = response.data.data.platform.platform_ticket.data || [];
        } else {
          console.error('Ошибка загрузки билетов площадки:', response.data.message);
        }
      } catch (error) {
        console.error('Ошибка при получении билетов площадки:', error);
      }
    },

    openCreateModal() {
      this.editingTicket = null;
      this.formData = { id_platform_ticket: '', price: 0, quantity: 0 };
      this.showModal = true;
    },

    openEditModal(ticket) {
      this.editingTicket = ticket;
      this.formData = {
        id_platform_ticket: ticket.id_platform_ticket,
        price: ticket.price,
        quantity: ticket.quantity || 0,
      };
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.editingTicket = null;
      this.formData = { id_platform_ticket: '', price: 0, quantity: 0 };
    },

    async handleSubmit() {
      const data = {
        id_type_activity: this.event?.id_type_activity || 1,
        id_platform: this.event?.id_platform || 1,
        title: this.event?.title || 'Событие',
        date: this.event?.date || '2025-01-01',
        time: this.event?.time || '00:00:00',
        price: this.event?.price || '0',
        description: this.event?.description || '',
        events_ticket: this.tickets.map(ticket => ({
          id_events_ticket: ticket.id_events_ticket,
          id_platform_ticket: ticket.id_platform_ticket,
          price: ticket.price,
          quantity: ticket.quantity || 0,
        })),
      };

      if (this.event?.img_src) {
        data.image = this.event.img_src;
      }

      if (this.editingTicket) {
        const ticketIndex = data.events_ticket.findIndex(
          ticket => ticket.id_events_ticket === this.editingTicket.id_events_ticket
        );
        if (ticketIndex !== -1) {
          data.events_ticket[ticketIndex] = {
            id_events_ticket: this.editingTicket.id_events_ticket,
            id_platform_ticket: this.formData.id_platform_ticket,
            price: this.formData.price,
            quantity: this.formData.quantity,
          };
        }
      } else {
        data.events_ticket_new = [{
          id_platform_ticket: this.formData.id_platform_ticket,
          price: this.formData.price,
          quantity: this.formData.quantity,
        }];
      }

      try {
        const response = await apiService.events.update(this.$route.params.id, data);
        if (response.data.status === 'success') {
          await this.fetchEvent();
          this.closeModal();
        } else {
          console.error('Ошибка сохранения билета:', response.data.message);
        }
      } catch (error) {
        console.error('Ошибка при отправке запроса:', error);
      }
    },

    async deleteTicket(ticketId) {
      if (!confirm('Вы уверены, что хотите удалить билет?')) return;

      try {
        const response = await apiService.events.deleteTicket(ticketId);
        if (response.data.status === 'success') {
          await this.fetchEvent();
        } else {
          console.error('Ошибка удаления билета:', response.data.message);
        }
      } catch (error) {
        console.error('Ошибка при удалении билета:', error);
      }
    },

    getPlatformTicketTitle(id_platform_ticket) {
      const platformTicket = this.platformTickets.find(
        ticket => ticket.id_platform_ticket === id_platform_ticket
      );
      return platformTicket ? platformTicket.title : 'Неизвестно';
    },
  },
};
</script>

<style scoped>
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