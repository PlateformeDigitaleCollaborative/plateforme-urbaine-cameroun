import type { LayerType } from '@/models/enums/geo/LayerType'

export interface AppLayerLegendItem {
  id: string //Id of the layer in the map
  layerType: LayerType.APP_LAYER
  order: number
  icon: string
  name: string
}

export interface AtlasLayerLegendItem {
  id: string //Id of the qgisProject in the map
  layerType: LayerType.ATLAS_LAYER
  icon: string
  order: number
  name: string
  subLayers: AtlasSubLayerLegendItem[]
  atlasGroup: string
}

export interface AtlasSubLayerLegendItem {
  name: string
  icon: string
  order: number
}