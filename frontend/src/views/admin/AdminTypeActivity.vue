<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Управление типами мероприятий</h1>
      <router-link to="/admin/dashboard" class="back-btn">Назад</router-link>
    </header>
    
    <div class="actions">
      <button @click="openCreateModal" class="btn">Добавить тип</button>
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
          <tr v-for="type in types" :key="type.id_type_activity">
            <td>{{ type.id_type_activity }}</td>
            <td>{{ type.title }}</td>
            <td>
              <button @click="openEditModal(type)" class="btn-small">Редактировать</button>
              <button @click="deleteType(type.id_type_activity)" class="btn-small danger">Удалить</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Модальное окно для создания/редактирования -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeModal">&times;</span>
        <h2>{{ editingType ? 'Редактировать тип' : 'Добавить тип' }}</h2>
        
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="title">Название</label>
            <input id="title" v-model="formData.title" required>
          </div>
          
          <button type="submit" class="btn">{{ editingType ? 'Обновить' : 'Создать' }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiService from '@/services/apiService';

const types = ref([]);
const showModal = ref(false);
const editingType = ref(null);
const formData = ref({
  title: '',
  image: null
});

const fetchTypes = async () => {
  try {
    const response = await apiService.typeActivity.getAll();
    types.value = response.data.data.type_activity || [];
  } catch (error) {
    console.error('Error fetching types:', error);
  }
};

const openCreateModal = () => {
  editingType.value = null;
  formData.value = { title: '', image: null };
  showModal.value = true;
};

const openEditModal = (type) => {
  editingType.value = type;
  formData.value = { title: type.title, image: null };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const handleImageChange = (e) => {
  formData.value.image = e.target.files[0];
};

const handleSubmit = async () => {
  try {
    if (editingType.value) {
      await apiService.typeActivity.update(editingType.value.id_type_activity, formData.value);
    } else {
      await apiService.typeActivity.create(formData.value);
    }
    closeModal();
    fetchTypes();
  } catch (error) {
    console.error('Error saving type:', error);
  }
};

const deleteType = async (id) => {
  if (confirm('Вы уверены, что хотите удалить этот тип мероприятия?')) {
    try {
      await apiService.typeActivity.delete(id);
      fetchTypes();
    } catch (error) {
      console.error('Error deleting type:', error);
    }
  }
};

onMounted(fetchTypes);
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