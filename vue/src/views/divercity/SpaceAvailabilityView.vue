<template>
  <div class="SpaceAvailabilityView" v-if="space">
    <PageTitle :title="$t('divercity.availability.title')" />

    <div class="SpaceAvailabilityView__layout">
      <div class="SpaceAvailabilityView__calendar">
        <VCDatePicker
          v-model="selectedDate"
          :attributes="calendarAttributes"
          @update:model-value="onDaySelected"
          :min-date="today"
          expanded
        />
        <div class="SpaceAvailabilityView__legend">
          <span class="SpaceAvailabilityView__legendItem SpaceAvailabilityView__legendItem--free">
            {{ $t('divercity.availability.legend.free') }}
          </span>
          <span class="SpaceAvailabilityView__legendItem SpaceAvailabilityView__legendItem--partial">
            {{ $t('divercity.availability.legend.partial') }}
          </span>
          <span class="SpaceAvailabilityView__legendItem SpaceAvailabilityView__legendItem--full">
            {{ $t('divercity.availability.legend.full') }}
          </span>
        </div>
        <div class="SpaceAvailabilityView__timelineCtn" v-if="selectedDate">
          <p class="SpaceAvailabilityView__timelineLabel">
            {{ $t('divercity.availability.timeline.title') }}
          </p>
          <v-progress-circular v-if="isLoadingDay" indeterminate color="main-blue" size="20" />
          <SpaceOccupiedTimeline v-else :slots="selectedDaySlots" />
        </div>
      </div>

      <div class="SpaceAvailabilityView__dayDetail" v-if="selectedDate">
        <h3>{{ formattedSelectedDate }}</h3>

        <v-progress-circular v-if="isLoadingDay" indeterminate color="main-blue" />

        <template v-else>
          <p v-if="!selectedDaySlots.length" class="text-success">
            {{ $t('divercity.availability.dayFree') }}
          </p>

          <!-- Liste des créneaux déjà réservés, avec le type d'évènement -->
          <!-- <div v-else class="SpaceAvailabilityView__slotsList">
            <div
              v-for="slot in selectedDaySlots"
              :key="slot.id"
              class="SpaceAvailabilityView__slotItem"
            >
              <span class="SpaceAvailabilityView__slotTime">
                {{ slot.startTime }} - {{ slot.endTime }}
              </span>
              <span
                v-if="slot.eventActivityTypeLabel"
                class="SpaceAvailabilityView__slotEventType"
                :style="{ backgroundColor: slot.eventActivityTypeColor || 'rgb(var(--v-theme-main-blue))' }"
              >
                {{ slot.eventActivityTypeLabel }}
              </span>
            </div>
          </div> -->

          <v-divider class="my-4" />

          <div class="SpaceAvailabilityView__slotPickerCtn">
            <p class="Form__label d-flex align-center">
              {{ $t('divercity.availability.pickSlot') }}
            </p>

            <div class="SpaceAvailabilityView__slotPicker">
              <v-select
                v-model="pickedStartTime"
                :items="timeOptions"
                density="compact"
                variant="outlined"
                hide-details
                :label="$t('divercity.booking.fields.startTime')"
              />
              <v-select
                v-model="pickedEndTime"
                :items="timeOptions"
                density="compact"
                variant="outlined"
                hide-details
                :label="$t('divercity.booking.fields.endTime')"
              />
            </div>

            <v-alert v-if="pickedSlotConflict" type="warning" variant="tonal" density="compact">
              {{ $t('divercity.booking.availability.conflict') }}
            </v-alert>

            <v-btn
              color="main-red"
              :disabled="!pickedStartTime || !pickedEndTime || pickedSlotConflict"
              @click="bookThisSlot"
            >
              {{ $t('divercity.availability.bookThisSlot') }}
            </v-btn>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import PageTitle from '@/components/text-elements/PageTitle.vue'
import type { SpaceAvailability } from '@/models/interfaces/divercity/Booking'
import { SpacesService } from '@/services/divercity/SpacesService'
import { useApplicationStore } from '@/stores/applicationStore'
import { useSpacesStore } from '@/stores/divercity/spacesStore'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import SpaceOccupiedTimeline from '@/views/divercity/components/SpaceOccupiedTimeline.vue'
import { DatePicker as VCDatePicker } from 'v-calendar'
import 'v-calendar/style.css'
import { getHalfHourTimeOptions } from '@/services/utils/divercity/timeSlots'

const applicationStore = useApplicationStore()
const spacesStore = useSpacesStore()
const router = useRouter()

const space = computed(() => spacesStore.mainSpace)
const today = new Date()

const selectedDate = ref<Date | null>(null)
const isLoadingDay = ref(false)
const selectedDaySlots = ref<SpaceAvailability[]>([])

// Cache mensuel
const monthAvailability = ref<SpaceAvailability[]>([])

const pickedStartTime = ref('')
const pickedEndTime = ref('')

// Génère les créneaux horaires de 08:30 à 17:30 avec un pas de 15 min
const timeOptions = getHalfHourTimeOptions()

onMounted(async () => {
  await loadMonth(today)
  applicationStore.isLoading = false
})

async function loadMonth(reference: Date) {
  if (!space.value) return
  const from = new Date(reference.getFullYear(), reference.getMonth(), 1)
  const to = new Date(reference.getFullYear(), reference.getMonth() + 1, 0)
  monthAvailability.value = await SpacesService.getSpaceAvailability(
    space.value.id,
    toISODate(from),
    toISODate(to)
  )
}

function toISODate(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function getDaysInMonth(reference: Date): string[] {
  const year = reference.getFullYear()
  const month = reference.getMonth()
  const date = new Date(year, month, 1)
  const days: string[] = []
  
  while (date.getMonth() === month) {
    days.push(toISODate(date))
    date.setDate(date.getDate() + 1)
  }
  
  return days
}

const availabilityByDay = computed(() => {
  const map = new Map<string, SpaceAvailability[]>()
  for (const slot of monthAvailability.value) {
    const key = slot.date.split('T')[0]
    if (!map.has(key)) map.set(key, [])
    map.get(key)!.push(slot)
  }
  return map
})

const FULL_DAY_THRESHOLD_MINUTES = 360

function slotMinutes(slot: SpaceAvailability): number {
  const [sh, sm] = slot.startTime.split(':').map(Number)
  const [eh, em] = slot.endTime.split(':').map(Number)
  return eh * 60 + em - (sh * 60 + sm)
}

function dayStatus(dateKey: string): 'free' | 'partial' | 'full' {
  const slots = availabilityByDay.value.get(dateKey)
  if (!slots || slots.length === 0) return 'free'
  const totalMinutes = slots.reduce((sum, s) => sum + slotMinutes(s), 0)
  return totalMinutes >= FULL_DAY_THRESHOLD_MINUTES ? 'full' : 'partial'
}

const calendarAttributes = computed(() => {
  const allMonthDays = getDaysInMonth(today)

  return allMonthDays.map((dateKey) => {
    const status = dayStatus(dateKey)
    let color = 'rgba(var(--v-theme-main-green), 0.7)'

    if (status === 'full') {
      color = 'rgba(var(--v-theme-main-red), 0.7)'
    } else if (status === 'partial') {
      color = 'rgba(249, 115, 22, 0.7)'
    }

    return {
      key: dateKey,
      dates: new Date(dateKey),
      highlight: {
        style: {
          backgroundColor: color,
          borderRadius: '50%',
        },
        contentStyle: {
          color: '#ffffff',
          fontWeight: '600'
        }
      }
    }
  })
})

async function onDaySelected(date: Date | null) {
  if (!date || !space.value) {
    selectedDaySlots.value = []
    return
  }
  isLoadingDay.value = true
  pickedStartTime.value = ''
  pickedEndTime.value = ''
  try {
    const iso = toISODate(date)
    selectedDaySlots.value = await SpacesService.getSpaceAvailability(space.value.id, iso, iso)
  } finally {
    isLoadingDay.value = false
  }
}

const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  return selectedDate.value.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long'
  })
})

const pickedSlotConflict = computed(() => {
  if (!pickedStartTime.value || !pickedEndTime.value) return false
  return selectedDaySlots.value.some(
    (slot) => pickedStartTime.value < slot.endTime && slot.startTime < pickedEndTime.value
  )
})

function bookThisSlot() {
  if (!selectedDate.value) return
  spacesStore.preselectedSlot = {
    date: toISODate(selectedDate.value),
    startTime: pickedStartTime.value,
    endTime: pickedEndTime.value
  }
  router.push({ name: 'divercitySpaceBooking' })
}
</script>

<style lang="scss" scoped>
.SpaceAvailabilityView {
  max-width: $dim-container-w;
  margin: 4rem auto;

  &__timelineCtn {
    margin-top: 1.5rem;
  }

  &__timelineLabel {
    font-weight: 700;
    font-size: $font-size-sm;
    margin-bottom: 0.5rem;
  }

  &__layout {
    display: grid;
    grid-template-columns: minmax(28rem, 38rem) 1fr;
    gap: 2rem;
    align-items: start;
    margin-top: 2rem;
  }

  &__calendar {
    --vc-accent-600: rgb(var(--v-theme-main-blue));
    --vc-font-family: inherit;
    width: 100%;
  }

  &__legend {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
    font-size: $font-size-xs;
  }

  &__legendItem {
    display: flex;
    align-items: center;
    gap: 0.35rem;

    &::before {
      content: '';
      display: inline-block;
      width: 0.6rem;
      height: 0.6rem;
      border-radius: 50%;
      opacity: 0.7; // adoucit les 3 couleurs sans dupliquer les règles
    }

    &--free::before {
      background: rgb(var(--v-theme-main-green));
    }

    &--partial::before {
      background: #F97316;
    }

    &--full::before {
      background: rgb(var(--v-theme-main-red));
    }
  }

  &__dayDetail {
    border: 1px solid rgb(var(--v-theme-main-grey));
    border-radius: $dim-radius;
    padding: 1.5rem;
    min-height: 28rem;
    overflow-y: auto;
  }

  &__slotsList {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
  }

  &__slotItem {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgb(var(--v-theme-main-grey));

    &:last-child {
      border-bottom: none;
    }
  }

  &__slotTime {
    font-weight: 600;
    font-size: $font-size-sm;
    color: rgb(var(--v-theme-dark-grey));
  }

  /* Rendu du badge de type d'événement avec display inline-block */
  &__slotEventType {
    display: inline-block;
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    font-size: $font-size-xs;
    font-weight: 500;
    color: #ffffff;
    line-height: 1.2;
  }

  &__slotPickerCtn {
    display: flex;
    flex-flow: column nowrap;
    gap: 1rem;
  }

  &__slotPicker {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;

    > * {
      flex: 1 1 10rem;
      min-width: 9rem;
    }
  }
}

@media (max-width: $bp-xl) {
  .SpaceAvailabilityView__layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 400px) {
  .SpaceAvailabilityView__slotPicker > * {
    flex-basis: 100%;
  }
}
</style>