import type { LngLatBounds } from 'maplibre-gl'

export interface MapState {
  layers: {
    actors?: number[]
    projects?: number[]
    resources?: number[]
    atlasMaps?: {
      id: string
      subLayers: string[]
    }[]
  }
  bbox: LngLatBounds
  order: string[]
}