<template>
  <div class="MyBookingCard">
    <div class="MyBookingCard__main">
      <div class="MyBookingCard__header">
        <h4 class="MyBookingCard__title">{{ booking.title }}</h4>
        <v-chip :color="statusColor" size="small">{{ statusLabel }}</v-chip>
      </div>

      <p class="MyBookingCard__space">{{ spaceName }}</p>

      <!-- Affichage du type d'évènement juste sous le nom de l'espace -->
      <span
        v-if="eventType"
        class="MyBookingCard__eventType"
        :style="eventTypeStyle"
      >
        {{ eventType.shortLabel || eventType.label }}
      </span>

      <p class="MyBookingCard__slot">
        {{ formattedDate }} · {{ booking.startTime }} - {{ booking.endTime }}
      </p>
    </div>

    <!-- Unique bouton avec l'icône de l'œil -->
    <div class="MyBookingCard__actions">
      <v-btn
        :icon="mdiEye"
        variant="text"
        color="main-blue"
        density="comfortable"
        @click="$emit('view')"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Booking } from '@/models/interfaces/divercity/Booking'
import { localizeDate } from '@/services/utils/UtilsService'
import { mdiEye } from '@mdi/js'
import { computed } from 'vue'
import { getBookingStatusColor } from '@/services/divercity/BookingStatusService'


const props = defineProps<{ booking: Booking }>()
defineEmits(['view'])

const statusCode = computed(() => (props.booking.status as any)?.code)
const statusLabel = computed(() => (props.booking.status as any)?.label ?? statusCode.value)

const statusColor = computed(() => getBookingStatusColor(statusCode.value))

const spaceName = computed(() => {
  const space = props.booking.space
  return space && typeof space === 'object' ? (space as any).name : ''
})

// eventActivityType peut être un objet (label + color) ou une IRI (string)
const eventType = computed(() => {
  const type = props.booking.eventActivityType
  return type && typeof type === 'object' ? type : null
})

const eventTypeStyle = computed(() => {
  const color = eventType.value?.color
  if (!color) return {}
  return {
    backgroundColor: color,
    color: '#fff'
  }
})

const formattedDate = computed(() => (props.booking.date ? localizeDate(props.booking.date) : ''))
</script>

<style lang="scss" scoped>
.MyBookingCard {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border: 1px solid rgb(var(--v-theme-main-grey));
  border-radius: 8px;

  &__main {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
  }

  &__header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  &__title {
    font-size: $font-size-h5;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__space {
    margin: 0;
    color: rgb(var(--v-theme-dark-grey));
    font-weight: 600;
    font-size: $font-size-sm;
  }

  &__eventType {
    align-self: flex-start;
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    font-size: $font-size-sm;
    font-weight: 500;
    line-height: 1.4;
  }

  &__slot {
    margin: 0;
    color: rgb(var(--v-theme-dark-grey));
    font-size: $font-size-sm;
  }

  &__actions {
    flex-shrink: 0;
  }

  @media (max-width: 600px) {
    flex-direction: row;
    align-items: center;
  }
}
</style>