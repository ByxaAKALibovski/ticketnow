<template>
    <header>
        <div class="row">
            <h1 class="logo">
                <router-link to="/">
                    <img src="@/assets/media/icons/logo.png" alt="logo" />
                    <span>ticket</span><span class="red__text">now</span>
                </router-link>
            </h1>
            <nav class="header__nav">
                <router-link to="/desks">Кассы</router-link>
                <router-link to="/news">Новости</router-link>
                <router-link to="/return">Возврат</router-link>
                <router-link to="/organizers">Организаторам</router-link>
            </nav>
            <address>
                <a href="tel:+79677710101">+7 (967) 771-01-01</a>
                <a href="mailto:support@ticketnow.ru">support@ticketnow.ru</a>
            </address>
        </div>
        <nav class="dop__nav">
            <router-link v-for="type in typeActivities" :key="type.id_type_activity"
                :to="`/type/${toSlug(type.title)}`">
                {{ type.title }}
            </router-link>
        </nav>
    </header>
</template>

<script>
import apiService from '@/services/apiService';

export default {
    name: 'Header',
    data() {
        return {
            typeActivities: [],
        };
    },
    created() {
        this.fetchTypeActivities();
    },
    methods: {
        async fetchTypeActivities() {
            try {
                const response = await apiService.typeActivity.getAll();
                this.typeActivities = response.data.data.type_activity || [];
            } catch (error) {
                console.error('Ошибка при загрузке типов мероприятий:', error.response?.data?.message || error.message);
            }
        },
        toSlug(title) {
            return title
                .toLowerCase()
                .replace(/[^a-z0-9а-яё]+/g, '-') // Заменяем пробелы и специальные символы на дефис
                .replace(/(^-|-$)/g, ''); // Удаляем дефисы в начале и конце
        },
    },
};
</script>