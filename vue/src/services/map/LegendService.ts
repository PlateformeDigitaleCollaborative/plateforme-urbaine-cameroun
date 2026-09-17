import { ItemType } from '@/models/enums/app/ItemType'
import { LayerType } from '@/models/enums/geo/LayerType'
import type { AtlasMap } from '@/models/interfaces/map/AtlasMap'
import type { Layer } from '@/models/interfaces/map/Layer'
import type { AppLayerLegendItem, AtlasLayerLegendItem } from '@/models/interfaces/map/Legend'
import { i18n } from '@/plugins/i18n'
import { watch, type Ref } from 'vue'

export class LegendService {
  static watchAppLayersVisibilityChanges(
    actorLayer: Ref<Layer | null>,
    projectLayer: Ref<Layer | null>,
    legendList: Ref<(AppLayerLegendItem | AtlasLayerLegendItem)[]>,
    atlasMaps: Ref<AtlasMap[]>
  ) {
    watch(
      [() => actorLayer.value?.isShown, () => projectLayer.value?.isShown],
      ([actorIsShown, projectIsShown], [prevActorIsShown, prevProjectIsShown]) => {
        if (actorIsShown !== prevActorIsShown) {
          this.updateLegendList(
            ItemType.ACTOR,
            LayerType.APP_LAYER,
            legendList,
            atlasMaps,
            actorIsShown
          )
        }
        if (projectIsShown !== prevProjectIsShown) {
          this.updateLegendList(
            ItemType.PROJECT,
            LayerType.APP_LAYER,
            legendList,
            atlasMaps,
            projectIsShown
          )
        }
      },
      { deep: true }
    )
  }
  static updateLegendList(
    layerId: string,
    layerType: LayerType,
    legendList: Ref<(AppLayerLegendItem | AtlasLayerLegendItem)[]>,
    atlasMaps: Ref<AtlasMap[]>,
    isShown = true
  ) {
    const existingEntry = legendList.value.find((x) => x.id === layerId)
    if (existingEntry) {
      if (existingEntry.layerType === LayerType.ATLAS_LAYER) {
        const atlasMap = atlasMaps.value.find((x) => x.id === layerId)
        // When a user remove a sublayer
        if (atlasMap && atlasMap.mainLayer.isShown) {
          existingEntry.subLayers = atlasMap.subLayers
            .filter((subLayer) => subLayer.isShown)
            .map((subLayer) => ({
              name: subLayer.name,
              icon: subLayer.icon as string,
              order: subLayer.mapOrder as number
            }))
            .sort((a, b) => a.order - b.order)
        } else {
          legendList.value = legendList.value.filter((x) => x.id !== layerId)
          legendList.value.forEach((legendItem, i) => {
            legendItem.order = i
          })
        }
      } else {
        legendList.value = legendList.value.filter((x) => x.id !== layerId)
        legendList.value.forEach((legendItem, i) => {
          legendItem.order = i
        })
      }
    } else {
      if (!isShown) return
      if (layerType === LayerType.APP_LAYER) {
        legendList.value.push({
          id: layerId,
          layerType: LayerType.APP_LAYER,
          icon: `/img/map/${layerId}_icon.png`,
          name: i18n.t('myMap.rightSidebar.layers.itemType.' + layerId),
          order: legendList.value.length
        })
      }
      if (layerType === LayerType.ATLAS_LAYER) {
        const atlasMap = atlasMaps.value.find((x) => x.id === layerId)
        if (atlasMap) {
          legendList.value.push({
            id: atlasMap.id,
            layerType: LayerType.ATLAS_LAYER,
            icon: atlasMap.mainLayer.icon as string,
            name: atlasMap.mainLayer.name,
            order: legendList.value.length,
            subLayers: atlasMap.subLayers
              .filter((subLayer) => subLayer.isShown)
              .map((subLayer) => ({
                name: subLayer.name,
                icon: subLayer.icon as string,
                order: subLayer.mapOrder as number
              }))
              .sort((a, b) => a.order - b.order),
            atlasGroup: atlasMap.mainLayer.atlasGroup
          })
        }
      }
    }
  }

  static updateLayersOrder(
    map: maplibregl.Map,
    legendList: Ref<(AppLayerLegendItem | AtlasLayerLegendItem)[]>
  ) {
    legendList.value.forEach((legendItem, i) => {
      const beforeLayer = legendList.value[i + 1]?.id || null
      if (map.getLayer(legendItem.id)) {
        map.moveLayer(legendItem.id, beforeLayer as string)
      }
    })
  }

  static updateAtlasSubLayersOrder(
    atlasMapLayer: AtlasLayerLegendItem,
    atlasMaps: Ref<AtlasMap[]>
  ) {
    const atlasThematicMap = atlasMaps.value.find((x) => x.id === atlasMapLayer.id)
    if (atlasThematicMap) {
      atlasMapLayer.subLayers.map((sortedSubLayer) => {
        for (const sublayer of atlasThematicMap.subLayers) {
          if (sublayer.id === sortedSubLayer.name) sublayer.mapOrder = sortedSubLayer.order
        }
      })
      atlasThematicMap.subLayers.sort((a, b) => a.mapOrder! - b.mapOrder!)
    }
  }
}