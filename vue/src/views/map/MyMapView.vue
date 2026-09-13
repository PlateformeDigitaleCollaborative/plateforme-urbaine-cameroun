<template>
  <div class="MyMapView">
    <MyMapHeader class="MyMapView__header" />
    <div class="MyMapView__mapCtn">
      <MyMapLeftSideBar class="MyMapView__welcomeSideBar" :shown="myMapStore.isLeftSidebarShown" />
      <MyMap class="MyMapView__myMap" />
      <MyMapRightSideBar class="MyMapView__layersSideBar" :shown="myMapStore.isRightSidebarShown" />
    </div>
  </div>
</template>
<script setup lang="ts">
import { LayerType } from '@/models/enums/geo/LayerType'
import { AppLayersService } from '@/services/map/AppLayersService'
import MapService from '@/services/map/MapService'
import { useMyMapStore } from '@/stores/myMapStore'
import MyMap from '@/views/map/components/MyMap.vue'
import MyMapHeader from '@/views/map/components/MyMapHeader.vue'
import MyMapLeftSideBar from '@/views/map/components/MyMapLeftSideBar.vue'
import MyMapRightSideBar from '@/views/map/components/MyMapRightSideBar.vue'
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import maplibregl from 'maplibre-gl'

const myMapStore = useMyMapStore()

const map = computed(() => myMapStore.myMap?.map)
const route = useRoute()

onMounted(() => {
  if (route.query.mapState) {
    myMapStore.serializedMapState = route.query.mapState as string
    myMapStore.deserializeMapState()
    myMapStore.initMapLayers()
    return
  }
  if (myMapStore.isMapAlreadyBeenMounted) {
    AppLayersService.initApplicationLayers()
    myMapStore.isLayersReorderingAlreadyTriggering = false
    MapService.isLoaded(map.value, () => reloadAtlasMaps())
  } else {
    myMapStore.initMapLayers()
  }
  focusOnQueryLocation()
})

function reloadAtlasMaps() {
  for (const item of myMapStore.legendList) {
    if (item.layerType === LayerType.ATLAS_LAYER) {
      myMapStore.updateAtlasLayersVisibility(item.id, false)
    }
  }
  myMapStore.setMapLayersOrderOnMapReMount()
}

// Géolocalise la carte sur un point précis passé en query params (ex: clic sur
// une adresse dans le Footer), et y pose un marqueur.
function focusOnQueryLocation() {
  const lat = parseFloat(route.query.focusLat as string)
  const lng = parseFloat(route.query.focusLng as string)
  if (Number.isNaN(lat) || Number.isNaN(lng)) return

  const label = route.query.focusLabel as string | undefined

  MapService.isLoaded(map.value, () => {
    if (!map.value) return

    const marker = new maplibregl.Marker({ color: '#E83323' })
      .setLngLat([lng, lat])
      .addTo(map.value)

    if (label) {
      const popup = new maplibregl.Popup({ offset: 25, closeButton: false })
        .setLngLat([lng, lat])
        .setText(label)
      marker.setPopup(popup)
    }

    map.value.flyTo({ center: [lng, lat], zoom: 17 })

    map.value.once('moveend', () => {
      marker.togglePopup()
    })
  })
}
</script>

<style lang="scss">
.MyMapView {
  display: flex;
  flex-flow: column nowrap;
  height: 100vh;
  overflow: hidden;
  .MyMapView__header {
    height: $mymap-header-h;
  }
  .MyMapView__mapCtn {
    display: flex;
    flex-flow: row nowrap;
    flex: 1 0 auto;

    .MyMapView__welcomeSideBar,
    .MyMapView__layersSideBar {
      z-index: 1;
      transition: all 0.15s ease-in;

      &[shown='false'] {
        overflow-x: hidden;
        width: 0;
        padding-left: 0;
        padding-right: 0;
      }
    }
  }
}
</style>
