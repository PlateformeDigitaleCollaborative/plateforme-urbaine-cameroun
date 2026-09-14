<!-- src/components/content/StatsLineChart.vue -->
<!--
  Mini graphique en ligne (SVG) sans dépendance externe : le projet n'a pas
  de librairie de charts installée, et cette vue n'a besoin que d'une courbe
  simple pour visualiser une tendance sur quelques semaines.
-->
<template>
  <div class="StatsLineChart">
    <svg
      v-if="points.length > 1"
      :viewBox="`0 0 ${width} ${height}`"
      preserveAspectRatio="none"
      class="StatsLineChart__svg"
    >
      <polyline class="StatsLineChart__area" :points="areaPoints" />
      <polyline class="StatsLineChart__line" :points="linePoints" />
    </svg>
    <div class="StatsLineChart__labels">
      <span>{{ firstLabel }}</span>
      <span>{{ lastLabel }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    series: { date: string; value: number }[]
    height?: number
  }>(),
  {
    height: 80
  }
)

const width = 300

const points = computed(() => props.series)

const maxValue = computed(() => Math.max(1, ...points.value.map((p) => p.value)))

const coords = computed(() =>
  points.value.map((p, i) => {
    const x = points.value.length > 1 ? (i / (points.value.length - 1)) * width : 0
    const y = props.height - (p.value / maxValue.value) * (props.height - 8) - 4
    return { x, y }
  })
)

const linePoints = computed(() => coords.value.map((c) => `${c.x},${c.y}`).join(' '))

const areaPoints = computed(() => {
  if (!coords.value.length) return ''
  const base = `0,${props.height} ${linePoints.value} ${width},${props.height}`
  return base
})

const formatLabel = (isoDate: string) =>
  new Date(isoDate).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })

const firstLabel = computed(() => (points.value[0] ? formatLabel(points.value[0].date) : ''))
const lastLabel = computed(() => {
  const last = points.value[points.value.length - 1]
  return last ? formatLabel(last.date) : ''
})
</script>

<style lang="scss">
.StatsLineChart {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  width: 100%;

  &__svg {
    width: 100%;
    height: 80px;
    display: block;
  }

  &__line {
    fill: none;
    stroke: rgb(var(--v-theme-main-blue));
    stroke-width: 2;
    vector-effect: non-scaling-stroke;
  }

  &__area {
    fill: rgba(28, 59, 135, 0.08);
    stroke: none;
  }

  &__labels {
    display: flex;
    justify-content: space-between;
    font-size: $font-size-sm;
    color: rgb(var(--v-theme-dark-grey));
  }
}
</style>
