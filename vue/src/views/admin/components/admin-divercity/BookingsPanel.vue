<template>
  <div class="AdminPanel">
    <AdminTopBar
      page="Bookings"
      :items="bookingsStore.bookings"
      :sortingListItems="[
        { sortingKey: 'EN_ATTENTE', text: $t('divercity.admin.sortPending') },
        { sortingKey: 'ACCEPTEE', text: $t('divercity.admin.sortAccepted') },
        { sortingKey: 'REFUSEE', text: $t('divercity.admin.sortRefused') },
        { sortingKey: 'ANNULEE', text: $t('divercity.admin.sortCancelled') },
        { sortingKey: 'submittedAt', text: $t('divercity.admin.sortSubmittedAt') },
        { sortingKey: 'eventDate', text: $t('divercity.admin.sortEventDate') }
      ]"
      searchKey="title"
      @updateSortingKey="handleSortingKeyChange"
      @update-search-query="searchQuery = $event"
    >
      <template #right-buttons>
        <BookingCalendarFilter v-model="calendarDateFilter" :occupancy="occupancyByDate" />
      </template>
    </AdminTopBar>
    <AdminTable
      :items="filteredItems"
      :table-keys="['title', 'date', 'timeRange', 'submittedAt', 'status.label']"
      :headers="tableHeaders"
      :date-keys="['date', 'submittedAt']"
      :column-widths="['28%', '15%', '17%', '20%', '20%']"
      :chip-keys="['status.label']"
      :chip-color-fn="(item: any) => getBookingStatusColor((item.status as any)?.code)"
      row-clickable
      selectable
      @row-click="openDetail"
    />
    <BookingDetailDialog
      :show="showDetail"
      :booking="selectedBooking"
      @close="showDetail = false"
      @updated="handleUpdated"
    />
  </div>
</template>

<script setup lang="ts">
import AdminTable from '@/components/admin/AdminTable.vue'
import AdminTopBar from '@/components/admin/AdminTopBar.vue'
import BookingCalendarFilter from './BookingCalendarFilter.vue'
import type { Booking } from '@/models/interfaces/divercity/Booking'
import { useBookingsStore } from '@/stores/divercity/bookingsStore'
import { computed, ref } from 'vue'
import { i18n } from '@/plugins/i18n'
import BookingDetailDialog from './BookingDetailDialog.vue'
import { getBookingStatusColor } from '@/services/divercity/BookingStatusService'


const bookingsStore = useBookingsStore()
const sortingKey = ref('EN_ATTENTE')
const searchQuery = ref('')
const calendarDateFilter = ref<string | null>(null)
const showDetail = ref(false)
const selectedBooking = ref<Booking | null>(null)

// Par défaut : plus ancienne d'abord
const submittedAtSortDirection = ref<'asc' | 'desc'>('asc')
const eventDateSortDirection = ref<'asc' | 'desc'>('asc')

const STATUS_SORT_KEYS = ['EN_ATTENTE', 'ACCEPTEE', 'REFUSEE', 'ANNULEE']

const WORK_DAY_MINUTES = 540

// En-têtes avec flèche indiquant le sens du tri sur "Date de soumission"
const tableHeaders = computed(() => {
  const base = [
    i18n.t('divercity.admin.headers.title'),
    i18n.t('divercity.admin.headers.date'),
    i18n.t('divercity.admin.headers.time'),
    i18n.t('divercity.admin.headers.submittedAt'),
    i18n.t('divercity.admin.headers.status')
  ]

  if (sortingKey.value === 'submittedAt') {
    const arrow = submittedAtSortDirection.value === 'asc' ? '↑' : '↓'
    base[3] = `${arrow} ${base[3]}`
  }

  if (sortingKey.value === 'eventDate') {
    const arrow = eventDateSortDirection.value === 'asc' ? '↑' : '↓'
    base[1] = `${arrow} ${base[1]}`
  }

  return base
})

function handleSortingKeyChange(newKey: string) {
  if (newKey === 'submittedAt') {
    submittedAtSortDirection.value =
      sortingKey.value === 'submittedAt'
        ? submittedAtSortDirection.value === 'asc' ? 'desc' : 'asc'
        : 'asc'
  }

  if (newKey === 'eventDate') {
    eventDateSortDirection.value =
      sortingKey.value === 'eventDate'
        ? eventDateSortDirection.value === 'asc' ? 'desc' : 'asc'
        : 'asc'
  }

  sortingKey.value = newKey
}

async function refresh() {
  await bookingsStore.getManagedBookings()
}

function openDetail(item: Booking) {
  selectedBooking.value = item
  showDetail.value = true
}

async function handleUpdated() {
  await refresh()
  showDetail.value = false
}

function timeToMinutes(time: string): number {
  const [hours, minutes] = time.split(':').map(Number)
  return hours * 60 + minutes
}

const occupancyByDate = computed(() => {
  const totalsByDate: Record<string, number> = {}

  for (const booking of bookingsStore.bookings) {
    if (!booking.date || !booking.startTime || !booking.endTime) continue
    const duration = timeToMinutes(booking.endTime) - timeToMinutes(booking.startTime)
    totalsByDate[booking.date] = (totalsByDate[booking.date] || 0) + Math.max(duration, 0)
  }

  const result: Record<string, 'partial' | 'full'> = {}
  for (const [date, minutes] of Object.entries(totalsByDate)) {
    result[date] = minutes >= WORK_DAY_MINUTES ? 'full' : 'partial'
  }
  return result
})

const sortedBookings = computed(() => {
  const list = [...bookingsStore.bookings]

  if (STATUS_SORT_KEYS.includes(sortingKey.value)) {
    return list.sort((a, b) => {
      const aMatch = (a.status as any)?.code === sortingKey.value ? 0 : 1
      const bMatch = (b.status as any)?.code === sortingKey.value ? 0 : 1
      return aMatch - bMatch
    })
  }

  if (sortingKey.value === 'submittedAt') {
    return list.sort((a, b) => {
      const cmp = (a.submittedAt ?? '').localeCompare(b.submittedAt ?? '')
      return submittedAtSortDirection.value === 'asc' ? cmp : -cmp
    })
  }

  // eventDate (et cas par défaut)
  return list.sort((a, b) => {
    const cmp = a.date.localeCompare(b.date)
    return eventDateSortDirection.value === 'asc' ? cmp : -cmp
  })
})

const searchFilteredBookings = computed(() => {
  if (!searchQuery.value) return sortedBookings.value
  return sortedBookings.value.filter((b) =>
    b.title.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const calendarFilteredBookings = computed(() => {
  if (!calendarDateFilter.value) return searchFilteredBookings.value
  return searchFilteredBookings.value.filter((b) => b.date === calendarDateFilter.value)
})

const filteredItems = computed(() =>
  calendarFilteredBookings.value.map((b) => ({
    ...b,
    timeRange: `${b.startTime} - ${b.endTime}`,
    statusColor: getBookingStatusColor((b.status as any)?.code)
  }))
)
</script>