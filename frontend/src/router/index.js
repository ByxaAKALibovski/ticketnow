import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '@/views/HomePage.vue'; // Компонент главной страницы
import DesksPage from '@/views/DesksPage.vue'; // Компонент для "Кассы"
import NewsPage from '@/views/NewsPage.vue'; // Компонент для "Новости"
import ReturnPage from '@/views/ReturnPage.vue'; // Компонент для "Возврат"
import OrganizersPage from '@/views/OrganizersPage.vue'; // Компонент для "Организаторам"
import TypeActivityPage from '@/views/TypeActivityPage.vue'; // Компонент для страницы типа мероприятия
import EventDetail from '@/views/EventDetail.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomePage,
  },
  {
    path: '/desks',
    name: 'Desks',
    component: DesksPage,
  },
  {
    path: '/news',
    name: 'News',
    component: NewsPage,
  },
  {
    path: '/return',
    name: 'Return',
    component: ReturnPage,
  },
  {
    path: '/organizers',
    name: 'Organizers',
    component: OrganizersPage,
  },
  {
    path: '/event/:id',
    name: 'EventDetail',
    component: EventDetail,
    props: true,
  },
  {
    path: '/type/:typeSlug',
    name: 'TypeActivity',
    component: TypeActivityPage,
    props: true, // Передаем параметр typeSlug в компонент
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;