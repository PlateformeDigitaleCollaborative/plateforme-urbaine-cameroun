import type Map from '@/components/map/Map.vue'
import { ItemType, type QGISItemType } from '@/models/enums/app/ItemType'
import { StoresList } from '@/models/enums/app/StoresList'
import { LayerType } from '@/models/enums/geo/LayerType'
import type { GeoData } from '@/models/interfaces/geo/GeoData'
import type { Item } from '@/models/interfaces/Item'
import type {
  AtlasActive,
  AtlasMap,
  FilteredQGISLayerFeatures
} from '@/models/interfaces/map/AtlasMap'
import type { Layer } from '@/models/interfaces/map/Layer'
import type { AppLayerLegendItem, AtlasLayerLegendItem } from '@/models/interfaces/map/Legend'
import type { MapState } from '@/models/interfaces/map/MapState'
import { ActorsService } from '@/services/actors/ActorsService'
import { AppLayersService } from '@/services/map/AppLayersService'
import { AtlasMapService } from '@/services/map/AtlasMapService'
import { LegendService } from '@/services/map/LegendService'
import { MapStoreSerializationService } from '@/services/map/MapStoreSerializationService'
import { ProjectService } from '@/services/projects/ProjectService'
import type { LngLat, LngLatBounds } from 'maplibre-gl'
import { defineStore } from 'pinia'
import { computed, reactive, ref, watch, type Ref } from 'vue'
import { useActorsStore } from './actorsStore'
import { useApplicationStore } from './applicationStore'
import { useAtlasStore } from './atlasStore'
import { useProjectStore } from './projectStore'

export const useMyMapStore = defineStore(StoresList.MY_MAP, () => {
  const applicationStore = useApplicationStore()
  const myMap: Ref<InstanceType<typeof Map> | undefined> = ref()
  const map = computed(() => myMap.value?.map)
  const mapCanvasToDataUrl: Ref<string | null> = ref(null)
  const isRightSidebarShown = ref(true)
  const isLeftSidebarShown = ref(true)
  const mapSearch: Ref<GeoData | null> = ref(null)
  const isMapAlreadyBeenMounted = ref(false)
  const isLayersReorderingAlreadyTriggering = ref(false)
  const isMapExportActive = ref(false)
  const isQgisLayerQueryActive = ref(false)

  const actorLayer: Ref<Layer | null> = ref(null)
  const actorSubLayers: Ref<Layer[]> = ref([])
  const projectLayer: Ref<Layer | null> = ref(null)
  const projectSubLayers: Ref<Layer[]> = ref([])

  const activeItemId: Ref<string | null> = ref(null)
  const activeItem: Ref<Item | FilteredQGISLayerFeatures[] | null> = ref(null)
  const activeItemType: Ref<ItemType | QGISItemType | null> = ref(null)
  const activeItemCoords: Ref<LngLat | null> = ref(null)

  const atlasMaps: Ref<AtlasMap[]> = ref([])
  const atlasSearchText: Ref<string> = ref('')
  const activeAtlas: AtlasActive = reactive({
    leftPanel: {
      active: false,
      atlasID: null
    },
    rightPanel: {
      active: false,
      atlasID: null
    }
  })
  let alreadyAddedImageSources: string[] = [] //Used to avoid triggering maplibre event as much time as the layer has been added to the map
  const legendList: Ref<(AppLayerLegendItem | AtlasLayerLegendItem)[]> = ref([])
  const bbox: Ref<LngLatBounds | undefined> = ref(undefined)
  const scaleDenominator = ref(0)
  const tileSize = ref(512)
  const serializedMapState: Ref<string> = ref('')
  const deserializedMapState: Ref<MapState | null> = ref(null)

  async function initMapLayers() {
    await AppLayersService.initApplicationLayers()
    await AtlasMapService.initAtlasLayers(useMyMapStore(), useAtlasStore())
    if (deserializedMapState.value) {
      bbox.value = deserializedMapState.value.bbox
      const bounds: [number, number, number, number] = [
        bbox.value._sw.lng,
        bbox.value._sw.lat,
        bbox.value._ne.lng,
        bbox.value._ne.lat
      ]
      myMap.value?.map?.fitBounds(bounds)
      for (const thematicMap of atlasMaps.value) {
        if (thematicMap.mainLayer.isShown) {
          updateAtlasLayersVisibility(thematicMap.id)
        }
      }
      legendList.value.sort((a, b) => {
        const indexA = deserializedMapState.value!.order.indexOf(a.id)
        const indexB = deserializedMapState.value!.order.indexOf(b.id)
        return indexA - indexB
      })
      updateMapLayersOrder()
    }
    deserializedMapState.value = null
    applicationStore.isLoading = false
  }

  // Activate a watcher for app layers status
  LegendService.watchAppLayersVisibilityChanges(actorLayer, projectLayer, legendList, atlasMaps)

  function getSerializedMapState(): string {
    return MapStoreSerializationService.serializeStore(useMyMapStore())
  }

  function deserializeMapState() {
    deserializedMapState.value = MapStoreSerializationService.deserializeStore(
      serializedMapState.value
    )
    serializedMapState.value = ''
  }

  function updateAtlasLayersVisibility(qgismapId: string, updateLegend = true) {
    AtlasMapService.handleAtlasLayersVisibility(
      atlasMaps.value,
      myMap.value?.map,
      qgismapId,
      alreadyAddedImageSources
    )
    alreadyAddedImageSources = [...new Set([...alreadyAddedImageSources, qgismapId])]
    if (updateLegend) {
      LegendService.updateLegendList(qgismapId, LayerType.ATLAS_LAYER, legendList, atlasMaps)
    }
  }

  function updateMapLayersOrder() {
    LegendService.updateLayersOrder(myMap.value?.map as maplibregl.Map, legendList)
  }

  function updateAtlasSubLayersOrder(atlasMapLayer: AtlasLayerLegendItem) {
    LegendService.updateAtlasSubLayersOrder(atlasMapLayer, atlasMaps)
    updateAtlasLayersVisibility(atlasMapLayer.id, false)
  }

  // When a user quit the map and come back, we wait for all the sources/layers to be loaded then we
  // reorder the layers according to the legend
  function setMapLayersOrderOnMapReMount() {
    const map = myMap.value?.map as maplibregl.Map
    if (!map) return
    const sources = map.getStyle().sources
    const numberOfSources = Object.keys(sources).length - 1 //We don't want basemap provider's (Maptiler/Mapbox) source
    const numberOfLegendItems = legendList.value.length
    if (numberOfSources === numberOfLegendItems) {
      if (map.loaded()) {
        updateMapLayersOrder()
      } else {
        map.on('idle', () => {
          // Avoid an infinite loop as layers ordering triggers 'idle' event
          if (isLayersReorderingAlreadyTriggering.value) return
          updateMapLayersOrder()
          isLayersReorderingAlreadyTriggering.value = true
        })
      }
    }
  }

  async function queryQgisLayer(coords: LngLat) {
    const features = await AtlasMapService.queryAtlasLayer(
      myMap.value?.map as maplibregl.Map,
      coords,
      legendList.value,
      atlasMaps.value
    )
    if (features) {
      activeItemCoords.value = coords
      activeItemType.value = 'QGIS'
      activeItem.value = features
    }
  }

  // PopUp Management
  watch(
    () => activeItemId.value,
    async () => {
      const projectStore = useProjectStore()
      const actorStore = useActorsStore()
      activeItem.value = null
      activeItemType.value = null

      await Promise.all([actorStore.getAll(), projectStore.getAll()])
      if (activeItemId.value) {
        let item: Item | undefined = projectStore.projects.find(
          (project) => project.id === activeItemId.value
        )

        if (item) {
          activeItemType.value = ItemType.PROJECT
          activeItem.value = await ProjectService.get(item)
        } else {
          item = actorStore.actors.find((actor) => actor.id === activeItemId.value)
          if (item) {
            activeItemType.value = ItemType.ACTOR
            activeItem.value = await ActorsService.getActor(item.id)
          }
        }
      }
    }
  )

  return {
    isRightSidebarShown,
    isLeftSidebarShown,
    isMapAlreadyBeenMounted,
    isLayersReorderingAlreadyTriggering,
    isMapExportActive,
    isQgisLayerQueryActive,
    myMap,
    map,
    mapCanvasToDataUrl,
    mapSearch,
    actorLayer,
    actorSubLayers,
    projectLayer,
    projectSubLayers,
    atlasMaps,
    atlasSearchText,
    activeAtlas,
    initMapLayers,
    updateAtlasLayersVisibility,
    legendList,
    updateMapLayersOrder,
    updateAtlasSubLayersOrder,
    setMapLayersOrderOnMapReMount,
    bbox,
    scaleDenominator,
    tileSize,
    getSerializedMapState,
    serializedMapState,
    deserializedMapState,
    deserializeMapState,
    queryQgisLayer,
    activeItemId,
    activeItemType,
    activeItem,
    activeItemCoords
  }
})