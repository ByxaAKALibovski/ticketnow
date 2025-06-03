<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Управление билетами площадки: {{ platform?.title || 'Площадка' }}</h1>
      <router-link :to="'/admin/platform'" class="back-btn">Назад к площадкам</router-link>
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
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in tickets" :key="ticket.id_platform_ticket">
            <td>{{ ticket.id_platform_ticket }}</td>
            <td>{{ ticket.title }}</td>
            <td>
              <button @click="openEditModal(ticket)" class="btn-small">Редактировать</button>
              <button @click="deleteTicket(ticket.id_platform_ticket)" class="btn-small danger">Удалить</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Модальное окно для создания/редактирования -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeModal">×</span>
        <h2>{{ editingTicket ? 'Редактировать билет' : 'Добавить билет' }}</h2>
        
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="title">Название</label>
            <input id="title" v-model="formData.title" required>
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
      platform: null,
      tickets: [],
      showModal: false,
      editingTicket: null,
      formData: {
        title: '',
      },
    };
  },
  async mounted() {
    await this.fetchPlatform();
  },
  methods: {
    async fetchPlatform() {
      try {
        const response = await apiService.platform.getOne(this.$route.params.id);
        if (response.data.status === 'success') {
          this.platform = response.data.data.platform;
          this.tickets = response.data.data.platform.platform_ticket.data || [];
        } else {
          console.error('Ошибка загрузки площадки:', response.data.message);
        }
      } catch (error) {
        console.error('Ошибка при получении данных:', error);
      }
    },

    openCreateModal() {
      this.editingTicket = null;
      this.formData = { title: '' };
      this.showModal = true;
    },

    openEditModal(ticket) {
      this.editingTicket = ticket;
      this.formData = { title: ticket.title };
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.editingTicket = null;
      this.formData = { title: '' };
    },

    async handleSubmit() {
      const data = {
        title: this.platform?.title || 'Площадка',
        platform_ticket: this.tickets.map(ticket => ({
          id_platform_ticket: ticket.id_platform_ticket,
          title: ticket.title,
        })),
      };

      if (this.platform?.img_src) {
        data.image = this.platform.img_src; // Отправляем путь как строку
      }

      if (this.editingTicket) {
        const ticketIndex = data.platform_ticket.findIndex(
          ticket => ticket.id_platform_ticket === this.editingTicket.id_platform_ticket
        );
        if (ticketIndex !== -1) {
          data.platform_ticket[ticketIndex].title = this.formData.title;
        }
      } else {
        data.platform_ticket_new = [{ title: this.formData.title }];
      }

      try {
        const response = await apiService.platform.update(this.$route.params.id, data);
        if (response.data.status === 'success') {
          await this.fetchPlatform();
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
        const response = await apiService.platform.deleteTicket(ticketId);
        if (response.data.status === 'success') {
          await this.fetchPlatform();
        } else {
          console.error('Ошибка удаления билета:', response.data.message);
        }
      } catch (error) {
        console.error('Ошибка при удалении билета:', error);
      }
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
  gap: 50px;
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