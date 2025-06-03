import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '@/views/HomePage.vue';
import DesksPage from '@/views/DesksPage.vue';
import NewsPage from '@/views/NewsPage.vue';
import ReturnPage from '@/views/ReturnPage.vue';
import OrganizersPage from '@/views/OrganizersPage.vue';
import TypeActivityPage from '@/views/TypeActivityPage.vue';
import EventDetail from '@/views/EventDetail.vue';
import AdminLogin from '@/views/admin/AdminLogin.vue';
import AdminDashboard from '@/views/admin/AdminDashboard.vue';
import AdminTypeActivity from '@/views/admin/AdminTypeActivity.vue';
import AdminPlatform from '@/views/admin/AdminPlatform.vue';
import AdminPlatformTickets from '@/views/admin/AdminPlatformTickets.vue';
import AdminEvents from '@/views/admin/AdminEvents.vue';
import AdminEventTickets from '@/views/admin/AdminEventTickets.vue';
import AdminNews from '@/views/admin/AdminNews.vue';
import AdminOrders from '@/views/admin/AdminOrders.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomePage,
    meta: { layout: 'default' }
  },
  {
    path: '/desks',
    name: 'Desks',
    component: DesksPage,
    meta: { layout: 'default' }
  },
  {
    path: '/news',
    name: 'News',
    component: NewsPage,
    meta: { layout: 'default' }
  },
  {
    path: '/return',
    name: 'Return',
    component: ReturnPage,
    meta: { layout: 'default' }
  },
  {
    path: '/organizers',
    name: 'Organizers',
    component: OrganizersPage,
    meta: { layout: 'default' }
  },
  {
    path: '/event/:id',
    name: 'EventDetail',
    component: EventDetail,
    props: true,
    meta: { layout: 'default' }
  },
  {
    path: '/type/:typeSlug',
    name: 'TypeActivity',
    component: TypeActivityPage,
    props: true,
    meta: { layout: 'default' }
  },
  // Admin routes
  {
    path: '/admin',
    name: 'AdminLogin',
    component: AdminLogin,
    meta: { layout: 'admin', requiresAuth: false }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { layout: 'admin', requiresAuth: true }
  },
  {
    path: '/admin/type-activity',
    name: 'AdminTypeActivity',
    component: AdminTypeActivity,
    meta: { layout: 'admin', requiresAuth: true }
  },
  {
    path: '/admin/platform',
    name: 'AdminPlatform',
    component: AdminPlatform,
    meta: { layout: 'admin', requiresAuth: true }
  },
  {
    path: '/admin/platform/:id/tickets',
    name: 'AdminPlatformTickets',
    component: AdminPlatformTickets,
    meta: { layout: 'admin', requiresAuth: true }
  },
  {
    path: '/admin/events',
    name: 'AdminEvents',
    component: AdminEvents,
    meta: { layout: 'admin', requiresAuth: true }
  },
  {
    path: '/admin/events/:id/tickets',
    name: 'AdminEventTickets',
    component: AdminEventTickets,
    meta: { layout: 'admin', requiresAuth: true }
  },
  {
    path: '/admin/news',
    name: 'AdminNews',
    component: AdminNews,
    meta: { layout: 'admin', requiresAuth: true }
  },
  {
    path: '/admin/orders',
    name: 'AdminOrders',
    component: AdminOrders,
    meta: { layout: 'admin', requiresAuth: true }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('authToken');

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/admin');
  } else if (to.name === 'AdminLogin' && isAuthenticated) {
    next('/admin/dashboard');
  } else {
    next();
  }
});

export default router;