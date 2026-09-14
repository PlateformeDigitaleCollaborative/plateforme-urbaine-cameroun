export interface ConnectionDailyPoint {
  date: string
  count: number
}

export interface ConnectionStats {
  totalConnections: number
  activeUsers: number
  dailySeries: ConnectionDailyPoint[]
}
