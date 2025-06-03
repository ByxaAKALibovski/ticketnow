import { createApp } from 'vue'
import { createPinia } from 'pinia'
import AppRoot from './AppRoot.vue'
import DefaultLayout from './App.vue'
import AdminLayout from './layouts/AdminLayout.vue'
import router from './router'

// Импортируем стили (но пока не применяем)
import fontsStyles from './assets/css/fonts.css?inline'
import rootStyles from './assets/css/root.css?inline'
import uiStyles from './assets/css/ui.css?inline'
import mainStyles from './assets/css/main.css?inline'

const app = createApp(AppRoot)

// Регистрируем оба layout
app.component('DefaultLayout', DefaultLayout)
app.component('AdminLayout', AdminLayout)

app.use(createPinia())
app.use(router)

// Ссылка на элемент style в DOM
let styleElement = null

// Функция для загрузки стилей
function loadStyles() {
  // Удаляем предыдущие стили, если они есть
  if (styleElement) {
    styleElement.remove()
    styleElement = null
  }

  // Загружаем стили только для публичной части
  if (!router.currentRoute.value.meta.layout || router.currentRoute.value.meta.layout === 'default') {
    styleElement = document.createElement('style')
    styleElement.textContent = [
      fontsStyles,
      rootStyles,
      uiStyles,
      mainStyles
    ].join('\n')
    document.head.appendChild(styleElement)
  }
}

// Загружаем стили при первом запуске
loadStyles()

// И при каждом изменении маршрута
router.afterEach(() => {
  loadStyles()
})

app.mount('#app')