import { StoresList } from '@/models/enums/app/StoresList'
import type { AudienceStats } from '@/models/interfaces/kpi/AudienceStats'
import type { ConnectionStats } from '@/models/interfaces/kpi/ConnectionStats'
import type { ContentViewStats } from '@/models/interfaces/kpi/ContentViewStats'
import { StatsService } from '@/services/StatsService'
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useStatsStore = defineStore(StoresList.STATS, () => {
  const audience = ref<AudienceStats | null>(null)
  const connections = ref<ConnectionStats | null>(null)
  const contentViews = ref<ContentViewStats | null>(null)
  const isLoading = ref(false)
  const days = ref(30)

  async function getStats(newDays?: number): Promise<void> {
    if (newDays) {
      days.value = newDays
    }
    isLoading.value = true
    try {
      const [audienceResult, connectionsResult, contentViewsResult] = await Promise.all([
        StatsService.getAudienceStats(days.value),
        StatsService.getConnectionStats(days.value),
        StatsService.getContentViewStats(days.value)
      ])
      audience.value = audienceResult
      connections.value = connectionsResult
      contentViews.value = contentViewsResult
    } finally {
      isLoading.value = false
    }
  }

  return { audience, connections, contentViews, isLoading, days, getStats }
})
