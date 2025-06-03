<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiService from '@/services/apiService'

const route = useRoute()
const router = useRouter()
const event = ref(null)
const platforms = ref([])
const orderTickets = ref([])
const activePopup = ref(null)
const activeTab = ref('scheme')
const ticketCounts = ref([])
const orderData = ref({
  name: '',
  email: '',
  phone: '',
})
const orderSuccess = ref(null)

const loadEvent = async () => {
  try {
    const response = await apiService.events.getOne(route.params.id)
    event.value = response.data.data.event || {}
    if (event.value.events_ticket?.data?.length) {
      ticketCounts.value = event.value.events_ticket.data.map(() => 0)
    } else {
      ticketCounts.value = []
    }
    const platformResponse = await apiService.platform.getAll()
    platforms.value = platformResponse.data.data.platform || []
  } catch (error) {
    console.error('Ошибка при загрузке события:', error.response?.data?.message || error)
    router.push('/not-found')
  }
}

const openPopup = (popupName) => {
  activePopup.value = popupName
}

const closeAllPopups = () => {
  activePopup.value = null
  activeTab.value = 'scheme'
  orderTickets.value = null
}

const switchTab = (tabName) => {
  activeTab.value = tabName
}

const incrementTicket = (index) => {
  ticketCounts.value[index]++
}

const decrementTicket = (index) => {
  if (ticketCounts.value[index] > 0) {
    ticketCounts.value[index]--
  }
}

const totalCost = computed(() => {
  return ticketCounts.value.reduce((sum, count, index) => {
    return sum + count * (event.value.events_ticket?.data?.[index]?.price || 0)
  }, 0)
})

const submitOrder = () => {
  const selectedTickets = ticketCounts.value
    .map((count, index) => ({
      id_events_ticket: event.value.events_ticket?.data?.[index]?.id_events_ticket,
      count,
    }))
    .filter((ticket) => ticket.count > 0)

  if (!selectedTickets.length) {
    alert('Выберите хотя бы один билет')
    return
  }

  orderTickets.value = {
    id_events: event.value.id_events,
    name: '',
    email: '',
    phone: '',
    orders_ticket: selectedTickets,
  }
  openPopup('order')
}

const createOrder = async () => {
  try {
    const response = await apiService.orders.create({
      id_events: orderTickets.value.id_events,
      name: orderData.value.name,
      email: orderData.value.email,
      phone: orderData.value.phone,
      orders_ticket: orderTickets.value.orders_ticket,
    })
    orderSuccess.value = response.data.data.order
    openPopup('success')
  } catch (error) {
    console.error('Ошибка при создании заказа:', error.response?.data?.message || error.message)
    alert('Ошибка при оформлении заказа')
  }
}

const getImageUrl = (imgSrc) => {
  return imgSrc
    ? `https://api.ticket-now.ru/uploads/${imgSrc}`
    : '/assets/media/temp/event-detail.png'
}

const getPlatformTitle = (idPlatform) => {
  const platform = platforms.value.find((p) => p.id_platform === idPlatform)
  return platform ? platform.title : 'Не указано'
}

const getPlatformImage = (idPlatform) => {
  const platform = platforms.value.find((p) => p.id_platform === idPlatform)
  return platform?.img_src
    ? `https://api.ticket-now.ru/uploads/${platform.img_src}`
    : '@/assets/media/temp/shema.png'
}

const getPlatformTickets = (idPlatform) => {
  const platform = platforms.value.find((p) => p.id_platform === idPlatform)
  return platform?.platform_ticket || []
}

const getTicketTitle = (idPlatform, index) => {
  const tickets = getPlatformTickets(idPlatform)
  return tickets[index]?.title || 'Входной билет'
}

const formatDate = (dateStr) => {
  const months = [
    'января',
    'февраля',
    'марта',
    'апреля',
    'мая',
    'июня',
    'июля',
    'августа',
    'сентября',
    'октября',
    'ноября',
    'декабря',
  ]
  const date = new Date(dateStr)
  const day = date.getDate()
  const month = months[date.getMonth()]
  return `${day} ${month}`
}

onMounted(loadEvent)
</script>

<template>
  <main>
    <!-- Попап с информацией о событии -->
    <div class="popup" :class="{ active: activePopup === 'def' }" data-popup-name="def">
      <div class="over" @click="closeAllPopups"></div>
      <div class="popup__body">
        <div class="close__popup_btn" @click="closeAllPopups">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            width="24.000000"
            height="24.000000"
            viewBox="0 0 24 24"
            fill="none"
          >
            <desc>Created with Pixso.</desc>
            <defs>
              <clipPath id="clip2_1430">
                <rect
                  id="close"
                  width="24.000000"
                  height="24.000000"
                  fill="white"
                  fill-opacity="0"
                />
              </clipPath>
            </defs>
            <rect id="close" width="24.000000" height="24.000000" fill="#FFFFFF" fill-opacity="0" />
            <g clip-path="url(#clip2_1430)">
              <path
                id="Vector"
                d="M19.2 7.2L17 5L12.1 10L7.2 5L5 7.2L10 12.1L5 17L7.2 19.2L12.1 14.2L17 19.2L19.2 17L14.2 12.1L19.2 7.2Z"
                fill="#C4C4C4"
                fill-opacity="1.000000"
                fill-rule="evenodd"
              />
            </g>
          </svg>
        </div>
        <h2 class="title">{{ event?.title || 'Событие' }}</h2>
        <p class="date">{{ event ? `${formatDate(event.date)}, ${event.time}` : '' }}</p>
        <p class="place">{{ getPlatformTitle(event?.id_platform) }}</p>
        <nav class="popup__nav">
          <a
            href="scheme"
            class="popup__btn"
            :class="{ active: activeTab === 'scheme' }"
            @click.prevent="switchTab('scheme')"
            >Схема зала</a
          >
          <a
            href="tickets"
            class="popup__btn"
            :class="{ active: activeTab === 'tickets' }"
            @click.prevent="switchTab('tickets')"
            >Билеты</a
          >
        </nav>
        <div class="popup__switch_container">
          <div
            class="popup__switch_block"
            :class="{ active: activeTab === 'scheme' }"
            data-name="scheme"
          >
            <img
              class="shema__image"
              :src="getPlatformImage(event?.id_platform)"
              alt="Схема зала"
            />
          </div>
          <div
            class="popup__switch_block"
            :class="{ active: activeTab === 'tickets' }"
            data-name="tickets"
          >
            <ul class="list__ticket">
              <li
                v-for="(ticket, index) in event?.events_ticket?.data || []"
                :key="ticket.id_events_ticket"
                class="ticket__item"
              >
                <p class="ticket__title">{{ getTicketTitle(event?.id_platform, index) }}</p>
                <p class="ticket__price">{{ ticket.price }} ₽</p>
                <div class="ticket__counter">
                  <div class="counter__item dec dis" @click="decrementTicket(index)"></div>
                  <div class="total__count">{{ ticketCounts[index] }}</div>
                  <div class="counter__item inc" @click="incrementTicket(index)"></div>
                </div>
              </li>
            </ul>
            <button class="btn btn__fill" id="bay__ticket_popup" @click="submitOrder">
              Купить билет
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="popup popup__orders"
      :class="{ active: activePopup === 'order' }"
      data-popup-name="order"
    >
      <div class="over" @click="closeAllPopups"></div>
      <div class="popup__body">
        <div class="close__popup_btn" @click="closeAllPopups">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            width="24.000000"
            height="24.000000"
            viewBox="0 0 24 24"
            fill="none"
          >
            <desc>Created with Pixso.</desc>
            <defs>
              <clipPath id="clip2_1430-orders">
                <rect
                  id="close-orders"
                  width="24.000000"
                  height="24.000000"
                  fill="white"
                  fill-opacity="0"
                />
              </clipPath>
            </defs>
            <rect
              id="close-orders"
              width="24.000000"
              height="24.000000"
              fill="#FFFFFF"
              fill-opacity="0"
            />
            <g clip-path="url(#clip2_1430-orders)">
              <path
                id="Vector-orders"
                d="M19.2 7.2L17 5L12.1 10L7.2 5L5 7.2L10 12.1L5 17L7.2 19.2L12.1 14.2L17 19.2L19.2 17L14.2 12.1L19.2 7.2Z"
                fill="#C4C4C4"
                fill-opacity="1.000000"
                fill-rule="evenodd"
              />
            </g>
          </svg>
        </div>
        <h2 class="title">Оформление заказа</h2>
        <p class="total-cost">
          Итоговая стоимость: <span>{{ totalCost }} ₽</span>
        </p>
        <input id="name" v-model="orderData.name" type="text" placeholder="Ваши имя и фамилия" />
        <input
          id="email"
          v-model="orderData.email"
          type="email"
          placeholder="Ваш email (туда будут высланы билеты)"
        />
        <input id="phone" v-model="orderData.phone" type="tel" placeholder="Ваш телефон" />
        <button class="btn btn__fill" @click="createOrder">Купить билет</button>
      </div>
    </div>

    <div
      class="popup popup__success"
      :class="{ active: activePopup === 'success' }"
      data-popup-name="success"
    >
      <div class="over" @click="closeAllPopups"></div>
      <div class="popup__body">
        <div class="close__popup_btn" @click="closeAllPopups">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            width="24.000000"
            height="24.000000"
            viewBox="0 0 24 24"
            fill="none"
          >
            <desc>Created with Pixso.</desc>
            <defs>
              <clipPath id="clip2_1430-success">
                <rect
                  id="close-success"
                  width="24.000000"
                  height="24.000000"
                  fill="white"
                  fill-opacity="0"
                />
              </clipPath>
            </defs>
            <rect
              id="close-success"
              width="24.000000"
              height="24.000000"
              fill="#FFFFFF"
              fill-opacity="0"
            />
            <g clip-path="url(#clip2_1430-success)">
              <path
                id="Vector-success"
                d="M19.2 7.2L17 5L12.1 10L7.2 5L5 7.2L10 12.1L5 17L7.2 19.2L12.1 14.2L17 19.2L19.2 17L14.2 12.1L19.2 7.2Z"
                fill="#C4C4C4"
                fill-opacity="1.000000"
                fill-rule="evenodd"
              />
            </g>
          </svg>
        </div>
        <img src="@/assets/media/icons/tickets-success.png" alt="" />
        <h2 class="title">Спасибо за заказ</h2>
        <p class="text__def">Билет будет направлен на указанную вами электронную почту</p>
      </div>
    </div>

    <section class="event_detail__sec">
      <div class="container">
        <div class="row main__info_event">
          <div class="column">
            <h2 class="title__def">{{ event?.title || 'Событие' }}</h2>
            <ul class="event__info">
              <li id="date">
                <img src="@/assets/media/icons/calendar.png" alt="calendar" />
                <p>{{ event ? formatDate(event.date) : '' }}</p>
              </li>
              <li id="time">
                <img src="@/assets/media/icons/clock.png" alt="clock" />
                <p>{{ event?.time || '' }}</p>
              </li>
              <li id="location">
                <img src="@/assets/media/icons/location.png" alt="location" />
                <p>{{ getPlatformTitle(event?.id_platform) }}</p>
              </li>
              <li id="price">
                <img src="@/assets/media/icons/ticket.png" alt="ticket" />
                <p>{{ event?.price || '' }}</p>
              </li>
            </ul>
            <a href="#" class="btn btn__fill" data-popup="def" @click.prevent="openPopup('def')"
              >Купить билет</a
            >
          </div>
          <img :src="getImageUrl(event?.img_src)" alt="event" />
        </div>
        <div class="text__block description" v-html="event?.description"></div>
      </div>
    </section>
    <section class="home_info__sec">
      <div class="container">
        <div class="info__block">
          <p class="text__def">
            В городе с большим количеством развлечений не соскучишься, о них вам расскажет афиша
            Пензы, где собраны все культурные события. Здесь вы найдете описание всех мероприятий:
            концертов, театральных и цирковых выступлений, шоу, фестивалей.
          </p>
          <h2 class="title__small">Куда сходить в Пензе?</h2>
          <div class="text__block">
            <p class="text__def">
              Если вы не знаете, что сегодня делать, и ищете, куда бы пойти, то заходите на сайт
              ticketnow.ru и выбирайте событие себе по душе. Афиша мероприятий в Пензе содержит
              информацию обо всех развлечениях на этой неделе и следующих, поэтому вы можете
              спланировать, к примеру, семейный поход в театр наперед. Билеты на сегодня, на завтра
              и другие дни можно купить не выходя из дома, через интернет, цены на них такие же, как
              и у официальных представителей.
            </p>
            <p class="text__def">
              Если вы определились, куда пойти в Пензе, то регистрируйтесь на сайте, нажимайте
              кнопку «Купить билет» и оформляйте заявку. Процедура проста и не займет много времени.
              Помимо стандартных способов оплаты, вы можете взять билет в рассрочку. Если вдруг
              передумаете идти на мероприятие, которое уже оплачено, то возможен возврат билета.
              Сумма, которая будет возвращена, зависит от количества дней до начала события.
            </p>
          </div>
          <h2 class="title__small">
            Почему выгодно покупать билеты на мероприятия на ticketnow.ru?
          </h2>
          <div class="text__block">
            <p class="text__def">
              На данном сайте представлена полная культурная афиша событий Пензы, что позволяет
              быстро и удобно ознакомиться с ними. Среди основных преимуществ анонса мероприятий:
              большое разнообразие развлечений, возможность купить билет через интернет, простая
              система возврата.
            </p>
            <p class="text__def">
              Не упускайте возможность весело и интересно провести время и наполнить свою жизнь
              позитивными эмоциями!
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>