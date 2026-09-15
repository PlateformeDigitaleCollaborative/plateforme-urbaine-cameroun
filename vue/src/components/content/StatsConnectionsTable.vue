<!-- src/components/content/StatsConnectionsTable.vue -->
<template>
  <div class="StatsConnectionsTable">
    <table class="StatsConnectionsTable__table" v-if="items.length">
      <thead>
        <tr>
          <th>{{ headers.country }}</th>
          <th>{{ headers.ipAddress }}</th>
          <th>{{ headers.user }}</th>
          <th>{{ headers.date }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in items" :key="item.connectedAt + index">
          <td>
            <div class="StatsConnectionsTable__country">
              <CountryFlag :code="item.countryCode" :country-name="item.countryName" />
              <span>{{ item.countryName ?? item.countryCode ?? unknownLabel }}</span>
            </div>
          </td>
          <td class="StatsConnectionsTable__ip">{{ item.ipAddress ?? '—' }}</td>
          <td>{{ item.user }}</td>
          <td class="StatsConnectionsTable__date">
            {{ localizeDate(item.connectedAt, { day: '2-digit', month: '2-digit', year: 'numeric' }) }}
            {{ localizeTime(item.connectedAt) }}
          </td>
        </tr>
      </tbody>
    </table>
    <div class="StatsConnectionsTable__empty" v-else>{{ emptyLabel }}</div>
  </div>
</template>

<script setup lang="ts">
import CountryFlag from '@/components/content/CountryFlag.vue'
import type { ConnectionOrigin } from '@/models/interfaces/kpi/ConnectionStats'
import { localizeDate, localizeTime } from '@/services/utils/UtilsService'

defineProps<{
  items: ConnectionOrigin[]
  headers: { country: string; ipAddress: string; user: string; date: string }
  emptyLabel: string
  unknownLabel: string
}>()
</script>

<style lang="scss">
.StatsConnectionsTable {
  width: 100%;
  overflow-x: auto;

  &__table {
    width: 100%;
    border-collapse: collapse;
    font-size: $font-size-sm;

    th {
      text-align: left;
      padding: 0.5rem 0.75rem;
      color: rgb(var(--v-theme-main-blue));
      font-weight: 700;
      white-space: nowrap;
    }

    td {
      padding: 0.5rem 0.75rem;
      border-top: 1px solid rgb(var(--v-theme-light-grey));
      vertical-align: middle;
    }
  }

  &__country {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  &__ip {
    font-family: monospace;
    white-space: nowrap;
  }

  &__date {
    white-space: nowrap;
    color: rgb(var(--v-theme-dark-grey));
  }

  &__empty {
    padding: 0.5rem 0.75rem;
    color: rgb(var(--v-theme-dark-grey));
    font-size: $font-size-sm;
  }
}
</style>
