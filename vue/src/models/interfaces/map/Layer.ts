import type { AtlasGroup } from '@/models/enums/geo/AtlasGroup'
import type { QgisMapType } from '@/models/enums/geo/QgisMapType'

export interface Layer {
  id: string | number
  name: string
  isShown?: boolean
  icon?: string
  opacity?: number
}

export interface AtlasLayer extends Layer {
  atlasGroup: AtlasGroup
  qgisMapType?: QgisMapType
  mapOrder?: number //Used for change layer order in Qgis Server requests
}