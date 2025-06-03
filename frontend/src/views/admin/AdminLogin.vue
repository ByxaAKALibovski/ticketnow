<template>
  <div class="admin-login">
    <div class="login-container">
      <h1>Вход в админ-панель</h1>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="login">Логин</label>
          <input 
            id="login" 
            v-model="credentials.login" 
            type="text" 
            required
          >
        </div>
        <div class="form-group">
          <label for="password">Пароль</label>
          <input 
            id="password" 
            v-model="credentials.password" 
            type="password" 
            required
          >
        </div>
        <button type="submit" class="btn">Войти</button>
        <div v-if="error" class="error-message">{{ error }}</div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import apiService from '@/services/apiService';

const router = useRouter();
const credentials = ref({
  login: '',
  password: ''
});
const error = ref('');

const handleLogin = async () => {
  try {
    const response = await apiService.auth.login(credentials.value);
    localStorage.setItem('authToken', response.data.data.token);
    router.push('/admin/dashboard');
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка авторизации';
  }
};
</script>

<style scoped>
.admin-login {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.login-container {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h1 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #333;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #555;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.btn {
  width: 100%;
  padding: 0.75rem;
  background-color: #A61E26;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  margin-top: 1rem;
}

.btn:hover {
  background-color: #A61E26;
}

.error-message {
  color: #f44336;
  margin-top: 1rem;
  text-align: center;
}
</style>