<!-- src/components/content/StatsCountryList.vue -->
<template>
  <ol class="StatsCountryList">
    <li
      class="StatsCountryList__item"
      v-for="(item, index) in items"
      :key="(item.countryCode ?? 'unknown') + index"
    >
      <span class="StatsCountryList__rank">{{ index + 1 }}</span>
      <CountryFlag :code="item.countryCode" :country-name="item.countryName" />
      <span class="StatsCountryList__label">
        {{ item.countryName ?? item.countryCode ?? unknownLabel }}
      </span>
      <span class="StatsCountryList__count">{{ item.count }}</span>
    </li>
    <li class="StatsCountryList__empty" v-if="!items.length">
      {{ emptyLabel }}
    </li>
  </ol>
</template>

<script setup lang="ts">
import CountryFlag from '@/components/content/CountryFlag.vue'
import type { ConnectionCountry } from '@/models/interfaces/kpi/ConnectionStats'

defineProps<{
  items: ConnectionCountry[]
  emptyLabel: string
  unknownLabel: string
}>()
</script>

<style lang="scss">
.StatsCountryList {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  list-style: none;
  padding: 0;
  margin: 0;

  &__item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    background-color: rgb(var(--v-theme-light-grey));
  }

  &__rank {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    min-width: 1.5rem;
    border-radius: 50%;
    background-color: rgb(var(--v-theme-main-blue));
    color: white;
    font-size: $font-size-sm;
    font-weight: 700;
  }

  &__label {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: $font-size-sm;
  }

  &__count {
    font-weight: 700;
    color: rgb(var(--v-theme-main-blue));
  }

  &__empty {
    padding: 0.5rem 0.75rem;
    color: rgb(var(--v-theme-dark-grey));
    font-size: $font-size-sm;
  }
}
</style>
