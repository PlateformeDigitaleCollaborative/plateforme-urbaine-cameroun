<template>
  <div class="MyMapPlatformLayers" v-show="isShown">
    <MyMapLayerPicker
      v-model:main-layer="myMapStore.projectLayer"
      v-model:sub-layers="myMapStore.projectSubLayers"
      @update="refreshLayer(ItemType.PROJECT)"
    />
    <MyMapLayerPicker
      v-model:main-layer="myMapStore.actorLayer"
      v-model:sub-layers="myMapStore.actorSubLayers"
      @update="refreshLayer(ItemType.ACTOR)"
    />
    <!-- <MyMapLayerPicker
      v-model:main-layer="myMapStore.resourceLayer"
      v-model:sub-layers="myMapStore.resourceSubLayers"
      @update="refreshLayer(ItemType.RESOURCE)"
    /> -->
  </div>
</template>

<script setup lang="ts">
import { ItemType } from '@/models/enums/app/ItemType'
import { AppLayersService } from '@/services/map/AppLayersService'
import { useMyMapStore } from '@/stores/myMapStore'
import MyMapLayerPicker from '@/views/map/components/MyMapLayerPicker.vue'
import { computed } from 'vue'

const myMapStore = useMyMapStore()
const myMap = computed(() => myMapStore.myMap)

const refreshLayer = (itemType: ItemType) => {
  if (myMap.value) {
    myMap.value.setData(itemType, AppLayersService.getGeojsonPerItemType(itemType))
  }
}

const isShown = computed(() => !myMapStore.activeAtlas.rightPanel.active)
</script>

<style lang="scss">
.MyMapPlatformLayers {
  display: flex;
  flex-flow: column nowrap;
  gap: 0.75rem;
}
</style>
