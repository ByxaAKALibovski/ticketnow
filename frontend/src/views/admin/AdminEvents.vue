<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Управление событиями</h1>
      <router-link to="/admin/dashboard" class="back-btn">Назад</router-link>
    </header>

    <div class="actions">
      <button @click="openCreateModal" class="btn">Добавить событие</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Тип</th>
            <th>Площадка</th>
            <th>Дата</th>
            <th>Время</th>
            <th>Цена</th>
            <th>Билетов</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="event in events" :key="event.id_events">
            <td>{{ event.id_events }}</td>
            <td>{{ event.title }}</td>
            <td>{{ getTypeName(event.id_type_activity) }}</td>
            <td>{{ getPlatformName(event.id_platform) }}</td>
            <td>{{ event.date }}</td>
            <td>{{ event.time }}</td>
            <td>{{ event.price }} ₽</td>
            <td>{{ event.events_ticket?.length || 0 }}</td>
            <td>
              <button @click="openEditModal(event)" class="btn-small">Ред.</button>
              <button @click="deleteEvent(event.id_events)" class="btn-small danger">Удал.</button>
              <router-link :to="`/admin/events/${event.id_events}/tickets`" class="btn-small">
                Билеты
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Модальное окно создания/редактирования -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeModal">&times;</span>
        <h2>{{ editingEvent ? 'Редактировать событие' : 'Добавить событие' }}</h2>

        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="title">Название *</label>
            <input id="title" v-model="formData.title" required />
          </div>

          <div class="form-group">
            <label for="type">Тип мероприятия *</label>
            <select id="type" v-model="formData.id_type_activity" required>
              <option
                v-for="type in types"
                :key="type.id_type_activity"
                :value="type.id_type_activity"
              >
                {{ type.title }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="platform">Площадка *</label>
            <select id="platform" v-model="formData.id_platform" required>
              <option
                v-for="platform in platforms"
                :key="platform.id_platform"
                :value="platform.id_platform"
              >
                {{ platform.title }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="date">Дата *</label>
            <input id="date" v-model="formData.date" type="date" required />
          </div>

          <div class="form-group">
            <label for="time">Время *</label>
            <input id="time" v-model="formData.time" type="time" required />
          </div>

          <div class="form-group">
            <label for="price">Цена (₽) *</label>
            <input id="price" v-model="formData.price" type="text" required />
          </div>

          <div class="form-group">
            <label for="description">Описание *</label>
            <textarea id="description" v-model="formData.description" required></textarea>
          </div>

          <div class="form-group">
            <label for="image">Изображение</label>
            <input id="image" type="file" @change="handleImageChange" />
          </div>

          <button type="submit" class="btn">{{ editingEvent ? 'Обновить' : 'Создать' }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiService from '@/services/apiService'

const events = ref([])
const types = ref([])
const platforms = ref([])
const showModal = ref(false)
const editingEvent = ref(null)
const formData = ref({
  title: '',
  id_type_activity: '',
  id_platform: '',
  date: '',
  time: '',
  description: '',
  price: '',
  image: null,
})

const fetchEvents = async () => {
  try {
    const response = await apiService.events.getAll()
    events.value = response.data.data.events || []
  } catch (error) {
    console.error('Ошибка загрузки событий:', error)
  }
}

const fetchTypes = async () => {
  try {
    const response = await apiService.typeActivity.getAll()
    types.value = response.data.data.type_activity || []
  } catch (error) {
    console.error('Ошибка загрузки типов:', error)
  }
}

const fetchPlatforms = async () => {
  try {
    const response = await apiService.platform.getAll()
    platforms.value = response.data.data.platform || []
  } catch (error) {
    console.error('Ошибка загрузки площадок:', error)
  }
}

const getTypeName = (id) => {
  const type = types.value.find((t) => t.id_type_activity === id)
  return type ? type.title : ''
}

const getPlatformName = (id) => {
  const platform = platforms.value.find((p) => p.id_platform === id)
  return platform ? platform.title : ''
}

const openCreateModal = () => {
  editingEvent.value = null
  formData.value = {
    title: '',
    id_type_activity: types.value[0]?.id_type_activity || '',
    id_platform: platforms.value[0]?.id_platform || '',
    date: '',
    time: '',
    description: '',
    price: '',
    image: null,
  }
  showModal.value = true
}

const openEditModal = (event) => {
  editingEvent.value = event
  formData.value = {
    title: event.title,
    id_type_activity: event.id_type_activity,
    id_platform: event.id_platform,
    date: event.date,
    time: event.time,
    price: event.price,
    description: event.description,
    image: null,
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleImageChange = (e) => {
  formData.value.image = e.target.files[0]
}

const handleSubmit = async () => {
  try {
    if (editingEvent.value) {
      await apiService.events.update(editingEvent.value.id_events, formData.value)
    } else {
      await apiService.events.create(formData.value)
    }
    closeModal()
    fetchEvents()
  } catch (error) {
    console.error('Ошибка сохранения события:', error)
    alert(`Ошибка: ${error.response?.data?.message || 'Неизвестная ошибка'}`)
  }
}

const deleteEvent = async (id) => {
  if (confirm('Вы уверены, что хотите удалить это событие?')) {
    try {
      await apiService.events.delete(id)
      fetchEvents()
    } catch (error) {
      console.error('Ошибка удаления события:', error)
      alert(`Ошибка: ${error.response?.data?.message || 'Не удалось удалить событие'}`)
    }
  }
}

onMounted(() => {
  fetchEvents()
  fetchTypes()
  fetchPlatforms()
})
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
  background-color: #2196f3;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

.actions {
  margin-bottom: 1rem;
}

.btn {
  padding: 0.5rem 1rem;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-small {
  padding: 0.3rem 0.6rem;
  margin-right: 0.5rem;
  background-color: #2196f3;
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

th,
td {
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
  background-color: rgba(0, 0, 0, 0.5);
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

.form-group input[type='text'],
.form-group input[type='file'] {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>