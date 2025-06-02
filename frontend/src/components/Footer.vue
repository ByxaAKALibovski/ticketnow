<template>
    <footer>
        <div class="container">
            <nav class="footer__nav">
                <div class="column">
                    <router-link v-for="type in typeActivities" :key="type.id_type_activity"
                        :to="`/type/${toSlug(type.title)}`">
                        {{ type.title }}
                    </router-link>
                </div>
                <div class="column">
                    <router-link to="/desks">Кассы</router-link>
                    <router-link to="/news">Новости</router-link>
                    <router-link to="/return">Возврат</router-link>
                    <router-link to="/organizers">Организаторам</router-link>
                </div>
            </nav>
        </div>
    </footer>
</template>

<script>
import apiService from '@/services/apiService';

export default {
    name: 'Footer',
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