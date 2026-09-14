import { apiClient } from '@/plugins/axios/api'
import type { AudienceStats } from '@/models/interfaces/kpi/AudienceStats'
import type { ConnectionStats } from '@/models/interfaces/kpi/ConnectionStats'
import type { ContentViewStats } from '@/models/interfaces/kpi/ContentViewStats'

export class StatsService {
  static async getAudienceStats(days = 30): Promise<AudienceStats> {
    return await apiClient
      .get('/api/kpis/audience', { params: { days } })
      .then((response) => response.data)
  }

  static async getConnectionStats(days = 30): Promise<ConnectionStats> {
    return await apiClient
      .get('/api/kpis/connections', { params: { days } })
      .then((response) => response.data)
  }

  static async getContentViewStats(days = 30, limit = 10): Promise<ContentViewStats> {
    return await apiClient
      .get('/api/kpis/content-views', { params: { days, limit } })
      .then((response) => response.data)
  }
}
