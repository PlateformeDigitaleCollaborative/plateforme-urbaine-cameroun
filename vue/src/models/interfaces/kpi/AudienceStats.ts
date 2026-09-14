export interface AudienceDailyPoint {
  date: string
  views: number
  uniqueVisitors: number
}

export interface AudienceTopPage {
  path: string
  views: number
}

export interface AudienceStats {
  totalViews: number
  uniqueVisitors: number
  dailySeries: AudienceDailyPoint[]
  topPages: AudienceTopPage[]
}
