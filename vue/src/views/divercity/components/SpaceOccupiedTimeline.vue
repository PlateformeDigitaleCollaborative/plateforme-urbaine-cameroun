<template>
  <div class="SpaceOccupiedTimeline">
    <template v-if="slots.length">
      <div class="SpaceOccupiedTimeline__track">
        <span
          v-for="slot in slots"
          :key="slot.id"
          class="SpaceOccupiedTimeline__slot"
          :class="`SpaceOccupiedTimeline__slot--${slot.type}`"
          :style="slotStyle(slot)"
          :title="slotTooltip(slot)"
        />
      </div>
      <div class="SpaceOccupiedTimeline__hours">
        <span v-for="h in hourMarks" :key="h">{{ h }}</span>
      </div>

      <ul class="SpaceOccupiedTimeline__details">
        <li
          v-for="slot in slots"
          :key="slot.id"
          class="SpaceOccupiedTimeline__detailItem"
          :class="`SpaceOccupiedTimeline__detailItem--${slot.type}`"
        >
          <span class="SpaceOccupiedTimeline__detailTime">{{ slot.startTime }} - {{ slot.endTime }}</span>
          
          <div class="SpaceOccupiedTimeline__detailContent">
            <!-- Titre de l'événement -->
            <h4 class="SpaceOccupiedTimeline__detailTitle">
              {{ slotTitle(slot) }}
            </h4>
            
            <!-- Badge dynamique -->
            <span 
              class="SpaceOccupiedTimeline__activityBadge"
              :style="badgeStyle(slot)"
            >
              {{ slotTypeLabel(slot) }}
            </span>
          </div>
        </li>
      </ul>

      <ul class="SpaceOccupiedTimeline__legend">
        <li class="SpaceOccupiedTimeline__legendItem SpaceOccupiedTimeline__legendItem--booking">
          {{ $t('divercity.availability.legend.booking') }}
        </li>
        <li class="SpaceOccupiedTimeline__legendItem SpaceOccupiedTimeline__legendItem--blocked_period">
          {{ $t('divercity.availability.legend.blockedPeriod') }}
        </li>
      </ul>
    </template>
    <p v-else class="SpaceOccupiedTimeline__empty">
      {{ $t('divercity.availability.dayFree') }}
    </p>
  </div>
</template>

<script setup lang="ts">
import type { SpaceAvailability } from '@/models/interfaces/divercity/Booking'
import { i18n } from '@/plugins/i18n'
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    slots: SpaceAvailability[]
    dayStartHour?: number
    dayEndHour?: number
  }>(),
  {
    dayStartHour: 8.5, // 08:30
    dayEndHour: 17.5   // 17:30
  }
)



const totalMinutes = computed(() => (props.dayEndHour - props.dayStartHour) * 60)

function toMinutes(time: string): number {
  const [h, m] = time.split(':').map(Number)
  return h * 60 + m
}

function slotStyle(slot: SpaceAvailability) {
  const rangeStart = props.dayStartHour * 60
  const start = Math.min(Math.max(toMinutes(slot.startTime) - rangeStart, 0), totalMinutes.value)
  const end = Math.min(Math.max(toMinutes(slot.endTime) - rangeStart, 0), totalMinutes.value)
  const left = (start / totalMinutes.value) * 100
  const width = Math.max(((end - start) / totalMinutes.value) * 100, 1)
  return { left: `${left}%`, width: `${width}%` }
}

function fallbackTypeLabel(slot: SpaceAvailability): string {
  return slot.type === 'blocked_period'
    ? i18n.t('divercity.availability.legend.blockedPeriod')
    : i18n.t('divercity.availability.legend.booking')
}

function slotTitle(slot: SpaceAvailability): string {
  return slot.title || fallbackTypeLabel(slot)
}

function slotTypeLabel(slot: SpaceAvailability): string {
  if (slot.type === 'booking' && slot.eventActivityTypeShortLabel ) {
    return slot.eventActivityTypeShortLabel 
  }

  if (slot.type === 'booking' && slot.eventActivityTypeLabel) {
    return slot.eventActivityTypeLabel
  }
  return fallbackTypeLabel(slot)
}

function badgeStyle(slot: SpaceAvailability) {
  if (slot.type === 'blocked_period') {
    return {
      backgroundColor: 'rgba(249, 115, 22, 0.1)',
      borderColor: 'rgba(249, 115, 22, 0.4)',
      color: '#F97316'
    }
  }

  const baseColor = slot.eventActivityTypeColor || '#3B82F6'

  return {
    backgroundColor: `${baseColor}1F`,
    borderColor: `${baseColor}66`,
    color: baseColor
  }
}

function slotTooltip(slot: SpaceAvailability): string {
  return `${slotTitle(slot)} (${slotTypeLabel(slot)}) : ${slot.startTime} - ${slot.endTime}`
}

// Génération des repères d'heures (Ex: 08h30, 10h30, 12h30, 14h30, 16h30, 17h30)
const hourMarks = computed(() => {
  const marks: string[] = []
  const startMins = props.dayStartHour * 60
  const endMins = props.dayEndHour * 60
  const stepMins = 120 // Pas de 2 heures

  for (let m = startMins; m < endMins; m += stepMins) {
    const hh = Math.floor(m / 60).toString().padStart(2, '0')
    const mm = (m % 60).toString().padStart(2, '0')
    marks.push(`${hh}h${mm}`)
  }

  // Ajoute l'heure de fin exacte (17h30) à la fin
  const endH = Math.floor(endMins / 60).toString().padStart(2, '0')
  const endM = (endMins % 60).toString().padStart(2, '0')
  marks.push(`${endH}h${endM}`)

  return marks
})
</script>

<style lang="scss" scoped>
.SpaceOccupiedTimeline {
  margin-top: 1rem;

  &__track {
    position: relative;
    height: 2.5rem;
    background: rgb(var(--v-theme-main-grey), 0.15);
    border-radius: $dim-radius;
    overflow: hidden;
  }

  &__slot {
    position: absolute;
    top: 0.35rem;
    bottom: 0.35rem;
    border-radius: 0.25rem;
    cursor: default;

    &--booking {
      background: rgb(var(--v-theme-main-red));
    }

    &--blocked_period {
      background: #F97316;
    }
  }

  &__hours {
    display: flex;
    justify-content: space-between;
    margin-top: 0.35rem;
    font-size: $font-size-xs;
    color: rgb(var(--v-theme-main-grey));
  }

  &__details {
    display: flex;
    flex-flow: column nowrap;
    gap: 1rem;
    margin-top: 1rem;
    padding: 0;
    list-style: none;
  }

  &__detailItem {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding-left: 0.75rem;
    border-left: 3px solid transparent;

    &--booking {
      border-left-color: rgb(var(--v-theme-main-red));
    }

    &--blocked_period {
      border-left-color: #F97316;
    }
  }

  &__detailTime {
    font-size: $font-size-sm;
    font-weight: 700;
    white-space: nowrap;
    margin-top: 0.15rem;
  }

  &__detailContent {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
  }

  &__detailTitle {
    font-size: $font-size-sm;
    font-weight: 700;
    margin: 0;
    color: rgb(var(--v-theme-main-dark, 0, 0, 0));
  }

  &__activityBadge {
    display: inline-block;
    padding: 0.2rem 0.55rem;
    font-size: $font-size-xs;
    font-weight: 600;
    border-radius: 999px;
    border: 1px solid;
    transition: all 0.2s ease;
  }

  &__legend {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
    font-size: $font-size-xs;
    list-style: none;
    padding: 0;
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
      border-radius: 0.15rem;
    }

    &--booking::before {
      background: rgb(var(--v-theme-main-red));
    }

    &--blocked_period::before {
      background: #F97316;
    }
  }

  &__empty {
    font-size: $font-size-sm;
    color: rgb(var(--v-theme-main-grey));
  }
}
</style>