<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Управление новостями</h1>
      <router-link to="/admin/dashboard" class="back-btn">Назад</router-link>
    </header>
    
    <div class="actions">
      <button @click="openCreateModal" class="btn">Добавить новость</button>
    </div>
    
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Дата создания</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in news" :key="item.id_news">
            <td>{{ item.id_news }}</td>
            <td>{{ item.title }}</td>
            <td>{{ new Date(item.date_public).toLocaleDateString() }}</td>
            <td>
              <button @click="openEditModal(item)" class="btn-small">Ред.</button>
              <button @click="deleteNews(item.id_news)" class="btn-small danger">Удал.</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Модальное окно создания/редактирования -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeModal">&times;</span>
        <h2>{{ editingNews ? 'Редактировать новость' : 'Добавить новость' }}</h2>
        
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="title">Заголовок *</label>
            <input id="title" v-model="formData.title" required>
          </div>
          
          <div class="form-group">
            <label for="description">Содержание *</label>
            <textarea id="description" v-model="formData.description" rows="5" required></textarea>
          </div>
          
          <button type="submit" class="btn">{{ editingNews ? 'Обновить' : 'Создать' }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiService from '@/services/apiService';

const news = ref([]);
const showModal = ref(false);
const editingNews = ref(null);
const formData = ref({
  title: '',
  description: ''
});

const fetchNews = async () => {
  try {
    const response = await apiService.news.getAll();
    news.value = response.data.data.news || [];
  } catch (error) {
    console.error('Ошибка загрузки новостей:', error);
  }
};

const openCreateModal = () => {
  editingNews.value = null;
  formData.value = { title: '', description: ''};
  showModal.value = true;
};

const openEditModal = (item) => {
  editingNews.value = item;
  formData.value = { 
    title: item.title, 
    description: item.description
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const handleSubmit = async () => {
  try {
    if (editingNews.value) {
      await apiService.news.update(editingNews.value.id_news, formData.value);
    } else {
      await apiService.news.create(formData.value);
    }
    closeModal();
    fetchNews();
  } catch (error) {
    console.error('Ошибка сохранения новости:', error);
    alert(`Ошибка: ${error.response?.data?.message || 'Неизвестная ошибка'}`);
  }
};

const deleteNews = async (id) => {
  if (confirm('Вы уверены, что хотите удалить эту новость?')) {
    try {
      await apiService.news.delete(id);
      fetchNews();
    } catch (error) {
      console.error('Ошибка удаления новости:', error);
      alert(`Ошибка: ${error.response?.data?.message || 'Не удалось удалить новость'}`);
    }
  }
};

onMounted(fetchNews);
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