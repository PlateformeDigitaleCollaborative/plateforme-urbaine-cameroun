import type { AtlasLayer } from "./Layer"


export interface AtlasMap {
  id: string
  mainLayer: AtlasLayer
  subLayers: AtlasLayer[]
  qgisProjectName: string
  atlasId: string
  needsToBeVisualiseAsPlainImageInsteadOfWMS: boolean
}

export interface AtlasActive {
  leftPanel: {
    active: boolean
    atlasID: string | null
  }
  rightPanel: {
    active: boolean
    atlasID: string | null
  }
}

// Used to parse response of QGIS server GetFeatureInfo request
export interface QGISFeatureAttributes {
  [key: string]: string
}
export interface QGISLayerFeatures {
  [layerName: string]: QGISFeatureAttributes[]
}

export interface FilteredQGISLayerFeatures {
  name: string
  data: [string, QGISFeatureAttributes[]][]
}