<template>
  <div class="admin-dashboard">
    <header class="admin-header">
      <h1>Административная панель</h1>
      <button @click="handleLogout" class="logout-btn">Выйти</button>
    </header>
    
    <nav class="admin-nav">
      <router-link to="/admin/type-activity" class="nav-item">Типы мероприятий</router-link>
      <router-link to="/admin/platform" class="nav-item">Площадки</router-link>
      <router-link to="/admin/events" class="nav-item">События</router-link>
      <router-link to="/admin/news" class="nav-item">Новости</router-link>
      <router-link to="/admin/orders" class="nav-item">Заказы</router-link>
    </nav>
    
    <div class="stats-container">
      <div class="stat-card">
        <h3>Типы мероприятий</h3>
        <p>{{ stats.typeActivities }}</p>
      </div>
      <div class="stat-card">
        <h3>Площадки</h3>
        <p>{{ stats.platforms }}</p>
      </div>
      <div class="stat-card">
        <h3>События</h3>
        <p>{{ stats.events }}</p>
      </div>
      <div class="stat-card">
        <h3>Новости</h3>
        <p>{{ stats.news }}</p>
      </div>
      <div class="stat-card">
        <h3>Заказы</h3>
        <p>{{ stats.orders }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import apiService from '@/services/apiService';

const router = useRouter();
const stats = ref({
  typeActivities: 0,
  platforms: 0,
  events: 0,
  news: 0,
  orders: 0
});

const fetchStats = async () => {
  try {
    const [typeRes, platformRes, eventsRes, newsRes, ordersRes] = await Promise.all([
      apiService.typeActivity.getAll(),
      apiService.platform.getAll(),
      apiService.events.getAll(),
      apiService.news.getAll(),
      apiService.orders.getAll()
    ]);
    
    stats.value = {
      typeActivities: typeRes.data.data.type_activity?.length || 0,
      platforms: platformRes.data.data.platform?.length || 0,
      events: eventsRes.data.data.events?.length || 0,
      news: newsRes.data.data.news?.length || 0,
      orders: ordersRes.data.data.orders?.length || 0
    };
  } catch (error) {
    console.error('Error fetching stats:', error);
  }
};

const handleLogout = () => {
  localStorage.removeItem('authToken');
  router.push('/admin');
};

onMounted(fetchStats);
</script>

<style scoped>
.admin-dashboard {
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

.logout-btn {
  padding: 0.5rem 1rem;
  background-color: #000157;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.logout-btn:hover {
  background-color: #000000;
}

.admin-nav {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.nav-item {
  padding: 0.75rem 1.5rem;
  background-color: #A61E26;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-item:hover {
  background-color: #77151c;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  text-align: center;
}

.stat-card h3 {
  margin-top: 0;
  color: #555;
  font-size: 1rem;
}

.stat-card p {
  font-size: 2rem;
  font-weight: bold;
  margin: 0.5rem 0 0;
  color: #333;
}
</style>