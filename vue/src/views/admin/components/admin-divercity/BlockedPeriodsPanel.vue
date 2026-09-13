<template>
  <div class="AdminPanel BlockedPeriodsPanel" v-if="space">
    <h2 class="BlockedPeriodsPanel__title">{{ $t('admin.panelDiverCityBlockedPeriods') }}</h2>

    <div class="BlockedPeriodsPanel__layout">
      <!-- Calendrier de vue globale des périodes bloquées -->
      <div class="BlockedPeriodsPanel__calendarCtn">
        <VCDatePicker v-model="selectedDate" :attributes="calendarAttributes" />
      </div>

      <div class="BlockedPeriodsPanel__form">
        <h3>{{ $t('divercity.blockedPeriods.addTitle') }}</h3>
        
        <div class="Form__fieldCtn">
          <label class="Form__label required">{{ $t('divercity.blockedPeriods.fields.date') }}</label>
          <v-text-field type="date" density="compact" variant="outlined" v-model="form.date" />
        </div>

        <!-- Sélection des heures via v-select (08:30 à 17:30 par pas de 15m) -->
        <div class="BlockedPeriodsPanel__timeRow">
          <v-select
            v-model="form.startTime"
            :items="timeOptions"
            density="compact"
            variant="outlined"
            hide-details
            :label="$t('divercity.booking.fields.startTime')"
          />
          <v-select
            v-model="form.endTime"
            :items="timeOptions"
            density="compact"
            variant="outlined"
            hide-details
            :label="$t('divercity.booking.fields.endTime')"
          />
        </div>

        <div class="Form__fieldCtn">
          <label class="Form__label">{{ $t('divercity.blockedPeriods.fields.reason') }}</label>
          <v-text-field density="compact" variant="outlined" v-model="form.reason" />
        </div>

        <v-checkbox
          v-model="form.isRecurring"
          :label="$t('divercity.blockedPeriods.recurrence.enable')"
          hide-details
        />

        <template v-if="form.isRecurring">
          <p class="BlockedPeriodsPanel__recurrenceInfo" v-if="selectedWeekdayLabel">
            {{ $t('divercity.blockedPeriods.recurrence.info', { weekday: selectedWeekdayLabel }) }}
          </p>
          <v-radio-group v-model="form.recurrenceEndMode" inline density="compact" hide-details>
            <v-radio :label="$t('divercity.blockedPeriods.recurrence.untilDate')" value="date" />
            <v-radio :label="$t('divercity.blockedPeriods.recurrence.occurrenceCount')" value="count" />
          </v-radio-group>
          <v-text-field
            v-if="form.recurrenceEndMode === 'date'"
            type="date" density="compact" variant="outlined"
            v-model="form.recurrenceEndDate"
            :label="$t('divercity.blockedPeriods.recurrence.untilDate')"
          />
          <v-text-field
            v-else
            type="number" density="compact" variant="outlined"
            v-model.number="form.recurrenceCount"
            :min="1" :max="MAX_RECURRENCE_OCCURRENCES"
            :label="$t('divercity.blockedPeriods.recurrence.occurrenceCount')"
          />
        </template>

        <v-alert
          v-if="form.isRecurring && recurrenceConflictDates.length"
          type="warning" variant="tonal" density="compact"
        >
          {{ $t('divercity.blockedPeriods.recurrence.conflictWarning', { dates: recurrenceConflictDates.join(', ') }) }}
        </v-alert>
        <v-alert
          v-else-if="!form.isRecurring && bookingConflict"
          type="warning" variant="tonal" density="compact"
        >
          {{ $t('divercity.blockedPeriods.errors.bookingConflict') }}
        </v-alert>
        <v-alert v-if="formError" type="error" variant="tonal" density="compact">
          {{ formError }}
        </v-alert>

        <v-btn
          color="main-red"
          :loading="isSubmitting"
          :disabled="(form.isRecurring ? recurrenceConflictDates.length > 0 : bookingConflict) || isLoadingDayAvailability"
          @click="submit"
        >
          {{ $t('divercity.blockedPeriods.submit') }}
        </v-btn>
      </div>
    </div>

    <v-divider class="my-6" />

    <div class="BlockedPeriodsPanel__listHeader">
      <p class="BlockedPeriodsPanel__listTitle" v-if="selectedDate">
        {{ $t('divercity.blockedPeriods.filteredBy', { date: formatDate(toISODate(selectedDate)) }) }}
      </p>
      <v-btn v-if="selectedDate" variant="text" size="small" @click="clearDateFilter">
        {{ $t('divercity.blockedPeriods.clearFilter') }}
      </v-btn>
    </div>

    <ul class="BlockedPeriodsPanel__list">
      <li v-for="period in filteredBlockedPeriods" :key="period.id" class="BlockedPeriodsPanel__listItem">
        <span class="BlockedPeriodsPanel__listDate">{{ formatDate(period.date) }}</span>
        <span>{{ period.startTime }} - {{ period.endTime }}</span>
        <span v-if="period.reason" class="BlockedPeriodsPanel__listReason">{{ period.reason }}</span>
        <span v-if="period.recurrenceGroupId" class="BlockedPeriodsPanel__seriesTag">
          {{ $t('divercity.blockedPeriods.recurrence.seriesTag') }}
        </span>
        <v-btn
          variant="text" density="comfortable" size="small"
          :loading="deletingId === period.id"
          @click="unblock(period.id)"
        >
          {{ $t('divercity.blockedPeriods.recurrence.unblockThisOne') }}
        </v-btn>
        <v-btn
          v-if="period.recurrenceGroupId"
          variant="text" density="comfortable" size="small" color="main-red"
          :loading="unblockingSeriesId === period.recurrenceGroupId"
          @click="unblockWholeSeries(period.recurrenceGroupId)"
        >
          {{ $t('divercity.blockedPeriods.recurrence.unblockWholeSeries') }}
        </v-btn>
      </li>
      <li v-if="!filteredBlockedPeriods.length" class="BlockedPeriodsPanel__empty">
        {{ $t('divercity.blockedPeriods.empty') }}
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { useSpacesStore } from '@/stores/divercity/spacesStore'
import { useBlockedPeriodsStore } from '@/stores/divercity/blockedPeriodsStore'
import { NotificationType } from '@/models/enums/app/NotificationType'
import { addNotification } from '@/services/notifications/NotificationService'
import { i18n } from '@/plugins/i18n'
import type { SpaceAvailability } from '@/models/interfaces/divercity/Booking'
import { SpacesService } from '@/services/divercity/SpacesService'
import { computed, onMounted, ref, watch } from 'vue'
import { DatePicker as VCDatePicker } from 'v-calendar'
import 'v-calendar/style.css'
import { getHalfHourTimeOptions } from '@/services/utils/divercity/timeSlots'

const MAX_RECURRENCE_OCCURRENCES = 104

const spacesStore = useSpacesStore()
const blockedPeriodsStore = useBlockedPeriodsStore()
const space = computed(() => spacesStore.mainSpace)
const dayAvailability = ref<SpaceAvailability[]>([])
const isLoadingDayAvailability = ref(false)

const selectedDate = ref<Date | null>(null)
const form = ref({
  date: '',
  startTime: '',
  endTime: '',
  reason: '',
  isRecurring: false,
  recurrenceEndMode: 'date' as 'date' | 'count',
  recurrenceEndDate: '',
  recurrenceCount: 4
})
const formError = ref('')
const isSubmitting = ref(false)
const deletingId = ref<string | null>(null)
const unblockingSeriesId = ref<string | null>(null)
const recurrenceConflictDates = ref<string[]>([])

// Heures restreintes de 08:30 à 17:30
const timeOptions = getHalfHourTimeOptions()

watch(
  () => form.value.date,
  async (newDate) => {
    dayAvailability.value = []
    if (!newDate || !space.value) return
    isLoadingDayAvailability.value = true
    try {
      dayAvailability.value = await SpacesService.getSpaceAvailability(space.value.id, newDate, newDate)
    } finally {
      isLoadingDayAvailability.value = false
    }
  }
)

onMounted(async () => {
  if (space.value) {
    await blockedPeriodsStore.getBlockedPeriods(space.value.id)
  }
})

const sortedBlockedPeriods = computed(() =>
  [...blockedPeriodsStore.blockedPeriods].sort((a, b) =>
    a.date === b.date ? a.startTime.localeCompare(b.startTime) : a.date.localeCompare(b.date)
  )
)

const calendarAttributes = computed(() =>
  blockedPeriodsStore.blockedPeriods.map((period) => ({
    key: period.id,
    dates: new Date(period.date),
    highlight: {
      style: {
        backgroundColor: 'rgba(249, 115, 22, 0.7)', // au lieu de '#F97316' en plein
        borderRadius: '50%'
      },
      contentStyle: {
        color: '#ffffff',
        fontWeight: '600'
      }
    }
  }))
)

const bookingConflict = computed(() => {
  if (!form.value.startTime || !form.value.endTime) return false
  return dayAvailability.value.some(
    (slot) =>
      slot.type === 'booking' &&
      form.value.startTime < slot.endTime &&
      slot.startTime < form.value.endTime
  )
})

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'long' })
}

function toISODate(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function addDays(date: Date, days: number): Date {
  const d = new Date(date)
  d.setDate(d.getDate() + days)
  return d
}

const selectedWeekdayLabel = computed(() => {
  if (!form.value.date) return ''
  return new Date(form.value.date).toLocaleDateString('fr-FR', { weekday: 'long' })
})

const recurrenceDates = computed<string[]>(() => {
  if (!form.value.date) return []
  if (!form.value.isRecurring) return [form.value.date]
  const start = new Date(form.value.date)
  const dates: string[] = []
  if (form.value.recurrenceEndMode === 'count') {
    const count = Math.min(Math.max(form.value.recurrenceCount || 0, 1), MAX_RECURRENCE_OCCURRENCES)
    for (let i = 0; i < count; i++) dates.push(toISODate(addDays(start, i * 7)))
  } else if (form.value.recurrenceEndDate) {
    const end = new Date(form.value.recurrenceEndDate)
    let current = start
    let safety = 0
    while (current <= end && safety < MAX_RECURRENCE_OCCURRENCES) {
      dates.push(toISODate(current))
      current = addDays(current, 7)
      safety++
    }
  }
  return dates
})

async function checkRecurrenceConflicts() {
  recurrenceConflictDates.value = []
  if (!space.value || !form.value.startTime || !form.value.endTime) return
  const conflicts: string[] = []
  for (const date of recurrenceDates.value) {
    const dayAvail = await SpacesService.getSpaceAvailability(space.value.id, date, date)
    const hasConflict = dayAvail.some(
      (slot) => slot.type === 'booking' && form.value.startTime < slot.endTime && slot.startTime < form.value.endTime
    )
    if (hasConflict) conflicts.push(date)
  }
  recurrenceConflictDates.value = conflicts
}

watch(
  [
    () => form.value.isRecurring,
    () => form.value.recurrenceEndMode,
    () => form.value.recurrenceEndDate,
    () => form.value.recurrenceCount,
    () => form.value.startTime,
    () => form.value.endTime,
    () => form.value.date
  ],
  () => {
    if (form.value.isRecurring) checkRecurrenceConflicts()
  }
)

const filteredBlockedPeriods = computed(() => {
  if (!selectedDate.value) return sortedBlockedPeriods.value
  const iso = toISODate(selectedDate.value)
  return sortedBlockedPeriods.value.filter((period) => period.date.split('T')[0] === iso)
})

function clearDateFilter() {
  selectedDate.value = null
}

async function unblock(id: string) {
  deletingId.value = id
  try {
    await blockedPeriodsStore.unblockBlockedPeriod(id)
    addNotification(i18n.t('divercity.blockedPeriods.unblockSuccess'), NotificationType.SUCCESS)
  } catch (error) {
    addNotification(i18n.t('divercity.blockedPeriods.unblockError'), NotificationType.ERROR, error as string)
  }
  deletingId.value = null
}

async function unblockWholeSeries(recurrenceGroupId: string) {
  unblockingSeriesId.value = recurrenceGroupId
  try {
    await blockedPeriodsStore.unblockSeries(recurrenceGroupId)
    addNotification(i18n.t('divercity.blockedPeriods.unblockSeriesSuccess'), NotificationType.SUCCESS)
  } catch (error) {
    addNotification(i18n.t('divercity.blockedPeriods.unblockSeriesError'), NotificationType.ERROR, error as string)
  }
  unblockingSeriesId.value = null
}

function resetForm() {
  form.value = {
    date: '',
    startTime: '',
    endTime: '',
    reason: '',
    isRecurring: false,
    recurrenceEndMode: 'date',
    recurrenceEndDate: '',
    recurrenceCount: 4
  }
  recurrenceConflictDates.value = []
}

async function submit() {
  formError.value = ''
  if (!space.value) return
  if (!form.value.date || !form.value.startTime || !form.value.endTime) {
    formError.value = i18n.t('divercity.blockedPeriods.errors.missingFields')
    return
  }
  if (form.value.startTime >= form.value.endTime) {
    formError.value = i18n.t('divercity.blockedPeriods.errors.invalidRange')
    return
  }

  if (form.value.isRecurring) {
    if (form.value.recurrenceEndMode === 'date' && !form.value.recurrenceEndDate) {
      formError.value = i18n.t('divercity.blockedPeriods.recurrence.errors.missingEndDate')
      return
    }
    if (recurrenceConflictDates.value.length) {
      formError.value = i18n.t('divercity.blockedPeriods.errors.bookingConflict')
      return
    }
  } else if (bookingConflict.value) {
    formError.value = i18n.t('divercity.blockedPeriods.errors.bookingConflict')
    return
  }

  isSubmitting.value = true
  try {
    const basePayload = {
      space: `/api/spaces/${space.value.id}`,
      startTime: form.value.startTime,
      endTime: form.value.endTime,
      ...(form.value.reason ? { reason: form.value.reason } : {})
    }

    if (form.value.isRecurring) {
      const recurrenceGroupId = crypto.randomUUID()
      await blockedPeriodsStore.addRecurringBlockedPeriods(
        recurrenceDates.value.map((date) => ({ ...basePayload, date, recurrenceGroupId }))
      )
    } else {
      await blockedPeriodsStore.addBlockedPeriod({ ...basePayload, date: form.value.date })
    }

    resetForm()
    addNotification(i18n.t('divercity.blockedPeriods.submitSuccess'), NotificationType.SUCCESS)
  } catch (error) {
    addNotification(i18n.t('divercity.blockedPeriods.submitError'), NotificationType.ERROR, error as string)
  }
  isSubmitting.value = false
}
</script>

<style lang="scss" scoped>
.BlockedPeriodsPanel {
  padding: 2rem;
  background: white;

  &__title { font-size: $font-size-h3; margin-bottom: 1.5rem; }
  &__layout { 
    display: grid; 
    grid-template-columns: minmax(18rem, 22rem) 1fr; 
    gap: 2rem; 
    align-items: start; 
  }
  
  &__form {
    display: flex;
    flex-flow: column nowrap;
    gap: 1.25rem;
    position: relative;
    z-index: 1;

    /* Alignement et placement propre de l'icône calendrier native Vuetify/HTML */
    :deep(input[type="date"]) {
      position: relative;
      
      &::-webkit-calendar-picker-indicator {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
      }
    }
  }

  &__calendarCtn {
    position: relative;
    z-index: 0;
    --vc-accent-600: #F97316;
    --vc-font-family: inherit;
    width: 100%;
  }

  &__timeRow {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;

    > * {
      flex: 1 1 12rem;
      min-width: 0;
    }
  }

  &__recurrenceInfo { font-size: $font-size-sm; color: rgb(var(--v-theme-main-grey-dark)); margin: 0; }
  
  &__listHeader {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
  }
  
  &__listTitle {
    font-weight: 700;
    font-size: $font-size-sm;
  }

  &__list { display: flex; flex-flow: column nowrap; gap: 0.5rem; padding: 0; list-style: none; }
  
  &__listItem {
    display: flex; 
    align-items: center; 
    gap: 1rem; 
    padding: 0.5rem 0.75rem;
    border-left: 3px solid #F97316;
    background: rgba(249, 115, 22, 0.08);
    flex-wrap: wrap;
    border-radius: 0 $dim-radius $dim-radius 0;
  }

  &__listDate { font-weight: 700; min-width: 9rem; }
  &__listReason { color: rgb(var(--v-theme-main-grey-dark)); flex: 1; }
  
  &__seriesTag {
    font-size: $font-size-xs;
    font-weight: 700;
    color: #F97316;
    background: rgba(249, 115, 22, 0.15);
    border-radius: 1rem;
    padding: 0.15rem 0.6rem;
  }

  &__empty { color: rgb(var(--v-theme-main-grey)); font-size: $font-size-sm; }
}

@media (max-width: $bp-xl) {
  .BlockedPeriodsPanel__layout { grid-template-columns: 1fr; }
}
</style>