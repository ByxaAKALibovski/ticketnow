<script setup>
import { ref, computed, onMounted } from 'vue';
import apiService from '@/services/apiService';

const events = ref([]);
const types = ref([]);
const platforms = ref([]);
const visibleCount = ref(8); // Начальное количество отображаемых событий
const step = 8; // Шаг для "Показать еще"

const visibleEvents = computed(() => events.value.slice(0, visibleCount.value));

const fetchData = async () => {
  try {
    // Получаем типы мероприятий
    const typeResponse = await apiService.typeActivity.getAll();
    types.value = typeResponse.data.data.type_activity || [];

    // Получаем площадки
    const platformResponse = await apiService.platform.getAll();
    platforms.value = platformResponse.data.data.platform || [];

    // Получаем события
    const eventsResponse = await apiService.events.getAll();
    events.value = (eventsResponse.data.data.events || []).filter(
      event => new Date(event.date) >= new Date() // Только будущие события
    );
  } catch (error) {
    console.error('Ошибка при загрузке данных:', error.response?.data?.message || error.message);
  }
};

const showMore = () => {
  visibleCount.value += step;
};

const getImageUrl = (imgSrc) => {
  return imgSrc ? `https://api.ticket-now.ru/uploads/${imgSrc}` : '/assets/media/temp/jopa.jpg';
};

const getTypeTitle = (idTypeActivity) => {
  const type = types.value.find(t => t.id_type_activity === idTypeActivity);
  return type ? type.title : 'Не указано';
};

const getPlatformTitle = (idPlatform) => {
  const platform = platforms.value.find(p => p.id_platform === idPlatform);
  return platform ? platform.title : 'Не указано';
};

const formatDate = (dateStr) => {
  const months = [
    'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
    'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
  ];
  const date = new Date(dateStr);
  const day = date.getDate();
  const month = months[date.getMonth()];
  return `${day} ${month}`;
};

onMounted(fetchData);
</script>

<template>
  <main>
    <section class="upcoming_events__sec">
      <div class="container">
        <h2 class="title__big"><span>ближайшие</span> мероприятия</h2>
        <ul class="upcoming_events__list">
          <li
            v-for="event in visibleEvents"
            :key="event.id_events"
            class="card__item"
            :data-card-id="event.id_events"
          >
            <div class="img__mask">
              <img :src="getImageUrl(event.img_src)" alt="card-img">
              <svg width="0" height="0">
                <defs>
                  <clipPath id="ticketMask" clipPathUnits="objectBoundingBox">
                    <path
                      d="M1 0 L0 0 L0 0.273 C0 0.273 0.109 0.287 0.109 0.487 C0.109 0.686 0 0.709 0 0.709 L0 1 L1 1 L1 0.709 C1 0.709 0.887 0.689 0.887 0.487 C0.887 0.286 1 0.273 1 0.273 Z" />
                  </clipPath>
                </defs>
              </svg>
            </div>
            <div class="main__content">
              <img src="@/assets/media/icons/dashed-line.svg" alt="dashed-line-img" class="dashed-line-img">
              <p class="card_top__title teg">{{ getTypeTitle(event.id_type_activity) }}</p>
              <h2 class="card_title">{{ event.title }}</h2>
              <p class="card_place">{{ getPlatformTitle(event.id_platform) }}</p>
              <ul class="date_time__block">
                <li><img src="@/assets/media/icons/calendar.png" alt="calendar">
                  <p class="date">{{ formatDate(event.date) }}</p>
                </li>
                <li><img src="@/assets/media/icons/clock.png" alt="clock">
                  <p class="time">{{ event.time }}</p>
                </li>
              </ul>
              <router-link :to="`/event/${event.id_events}`" class="btn btn__fill">Подробнее</router-link>
            </div>
          </li>
        </ul>
        <a
          v-if="visibleCount < events.length"
          href="#"
          class="btn"
          @click.prevent="showMore"
        >Показать еще</a>
      </div>
    </section>
    <section class="home_info__sec">
      <div class="container">
        <div class="info__block">
          <p class="text__def">В городе с большим количеством развлечений не соскучишься, о них вам
            расскажет афиша Пензы, где собраны все культурные события. Здесь вы найдете описание всех
            мероприятий: концертов, театральных и цирковых выступлений, шоу, фестивалей.</p>
          <h2 class="title__small">Куда сходить в Пензе?</h2>
          <div class="text__block">
            <p class="text__def">Если вы не знаете, что сегодня делать, и ищете, куда бы пойти, то
              заходите на сайт ticketnow.ru и выбирайте событие себе по душе. Афиша мероприятий в
              Пензе содержит информацию обо всех развлечениях на этой неделе и следующих, поэтому вы
              можете спланировать, к примеру, семейный поход в театр наперед. Билеты на сегодня, на
              завтра и другие дни можно купить не выходя из дома, через интернет, цены на них такие
              же, как и у официальных представителей.
            </p>
            <p class="text__def">Если вы определились, куда пойти в Пензе, то регистрируйтесь на сайте,
              нажимайте кнопку «Купить билет» и оформляйте заявку. Процедура проста и не займет много
              времени. Помимо стандартных способов оплаты, вы можете взять билет в рассрочку. Если
              вдруг передумаете идти на мероприятие, которое уже оплачено, то возможен возврат билета.
              Сумма, которая будет возвращена, зависит от количества дней до начала события.</p>
          </div>
          <h2 class="title__small">Почему выгодно покупать билеты на мероприятия на ticketnow.ru?</h2>
          <div class="text__block">
            <p class="text__def">На данном сайте представлена полная культурная афиша событий Пензы, что
              позволяет быстро и удобно ознакомиться с ними. Среди основных преимуществ анонса
              мероприятий: большое разнообразие развлечений, возможность купить билет через интернет,
              простая система возврата.

            </p>
            <p class="text__def">Не упускайте возможность весело и интересно провести время и наполнить
              свою жизнь позитивными эмоциями!</p>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>