<!-- src/components/content/StatsTopList.vue -->
<template>
  <ol class="StatsTopList">
    <li class="StatsTopList__item" v-for="(item, index) in items" :key="item.path + index">
      <span class="StatsTopList__rank">{{ index + 1 }}</span>
      <span class="StatsTopList__label">{{ item.label }}</span>
      <span class="StatsTopList__views">{{ item.views }}</span>
    </li>
    <li class="StatsTopList__empty" v-if="!items.length">
      {{ emptyLabel }}
    </li>
  </ol>
</template>

<script setup lang="ts">
defineProps<{
  items: { path: string; label: string; views: number }[]
  emptyLabel: string
}>()
</script>

<style lang="scss">
.StatsTopList {
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

  &__views {
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
