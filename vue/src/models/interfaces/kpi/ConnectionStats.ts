export interface ConnectionDailyPoint {
  date: string
  count: number
}

export interface ConnectionOrigin {
  connectedAt: string
  ipAddress: string | null
  countryCode: string | null
  countryName: string | null
  user: string
}

export interface ConnectionCountry {
  countryCode: string | null
  countryName: string | null
  count: number
}

export interface ConnectionStats {
  totalConnections: number
  activeUsers: number
  dailySeries: ConnectionDailyPoint[]
  recentConnections: ConnectionOrigin[]
  topCountries: ConnectionCountry[]
}
