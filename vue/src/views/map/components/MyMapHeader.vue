<template>
  <nav class="MyMapHeader">
    <div class="MyMapHeader__block MyMapHeader__block--left">
      <v-btn
        variant="elevated"
        elevation="1"
        density="comfortable"
        icon="$arrowLeft"
        class="main-blue"
        :to="{ name: 'home' }"
      >
        <v-icon icon="$arrowLeft" color="main-blue" />
      </v-btn>
      <router-link :to="{ name: 'home' }" class="Header__bannerLink">
        <img
          loading="lazy"
          src="@/assets/images/Logo.png"
          alt="Accueil"
          class="MyMapHeader__logo"
        />
      </router-link>
    </div>
    <Geocoding
      :search-type="NominatimSearchType.FREE"
      v-model="myMapStore.mapSearch"
      :placeholder="$t('myMap.header.search.placeholder')"
      :geometry-details="true"
      class="MyMapHeader__search"
      :icon="'$magnify'"
      density="comfortable"
    />

    <div class="MyMapHeader__block MyMapHeader__block--left">
      <v-btn
        class="MyMapHeader__shareBtn"
        prepend-icon="$shareVariant"
        variant="elevated"
        elevation="1"
        @click="getSharedMapLink()"
      >
        {{ $t('myMap.header.share') }}
        <ShareMenu :url="mapUrl" location="bottom" :body="emailBody" />
      </v-btn>

      <LoginButton />
    </div>
  </nav>
</template>

<script setup lang="ts">
import Geocoding from '@/components/forms/Geocoding.vue'
import ShareMenu from '@/components/global/ShareMenu.vue'
import { NotificationType } from '@/models/enums/app/NotificationType'
import { NominatimSearchType } from '@/models/enums/geo/NominatimSearchType'
import { i18n } from '@/plugins/i18n'
import { addNotification } from '@/services/notifications/NotificationService'
import { useMyMapStore } from '@/stores/myMapStore'
import LoginButton from '@/views/_layout/header/LoginButton.vue'
import { ref } from 'vue'

const myMapStore = useMyMapStore()
const mapUrl = ref('')
const emailBody = ref('')

function getSharedMapLink() {
  const serializedMapState = myMapStore.getSerializedMapState()
  const url = window.location.href + '?mapState=' + serializedMapState
  if (url.length > 2048) {
    addNotification(i18n.t('myMap.header.linkTooLong'), NotificationType.ERROR)
    return
  }
  mapUrl.value = url
  emailBody.value = i18n.t('myMap.header.shareEmailBody', { url })
}
</script>

<style lang="scss">
.MyMapHeader {
  display: flex;
  flex-flow: row nowrap;
  align-items: center;
  gap: 1.25rem;
  padding: 0.5rem 1.5rem;
  border-bottom: solid 1px rgb(var(--v-theme-main-grey));

  .MyMapHeader__block {
    display: flex;
    flex-flow: row nowrap;
    align-items: center;
    gap: 0.75rem;
  }
  .MyMapHeader__logo {
    $dim-logo: 3.5rem;

    width: $dim-logo;
    height: $dim-logo;
    min-width: $dim-logo;
    max-width: $dim-logo;
    padding: 0.125rem;
    border-radius: 50%;
    border: solid 1px rgb(var(--v-theme-main-grey));
    object-fit: cover;
  }
  
  .MyMapHeader__search {
    flex: 1 1 auto;

    ::placeholder {
      opacity: 1;
    }
  }

  .MyMapHeader__shareBtn {
    color: rgb(var(--v-theme-main-blue));
    .v-icon {
      color: rgb(var(--v-theme-main-blue));
    }
  }
}
</style>
