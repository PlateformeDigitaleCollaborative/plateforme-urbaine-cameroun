<template>
  <div class="Header Header--desktop">
    <div class="Header__banner">
      <div class="Header__bannerContent container container--transition">
        <div class="Header__bannerContent container container--transition">
          <LoginButton variant="link" />
        </div>
      </div>
    </div>
    <div class="Header__nav">
      <div class="Header__navContent container container--transition">
        <div class="Header__navBlock Header__navBlock--left">
          <router-link :to="{ name: 'home' }" class="Header__bannerLink">
            <img
              loading="lazy"
              src="@/assets/images/Logo.png"
              alt="Accueil"
              class="Header__appLogo"
            />
          </router-link>
        </div>
        <nav class="Header__navBlock Header__navBlock--right">
          <v-tabs v-model="appStore.activeTab" align-tabs="end" color="main-red">
            <v-tab
              v-for="(tab, index) in navigationTabs"
              :value="tab.value"
              :to="tab.route"
              :key="index"
              :disabled="tab.disabled"
            >
              <span class="Header__tabsText">{{ tab.name }}</span>
            </v-tab>
          </v-tabs>
          <v-btn
            base-color="main-red"
            class="text-white"
            :to="{ name: 'divercitySpace' }"
            flat
          >
            {{ $t('header.divercitySpace') }}
          </v-btn>
          <v-btn base-color="white" class="text-main-blue" :to="{ name: 'map' }" flat>
            <img
              loading="lazy"
              src="@/assets/images/icons/add_location_alt.svg"
              alt="Accueil"
              class="Header__appLogo mr-1"
            />
            {{ $t('header.map') }}
          </v-btn>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { NavigationTabsService } from '@/services/application/NavigationTabsService'
import { NavigationTabs } from '@/models/enums/app/NavigationTabs'
import { useApplicationStore } from '@/stores/applicationStore'
import { computed } from 'vue'
import LoginButton from './LoginButton.vue'

const appStore = useApplicationStore()

// "DiverCity Space" devient un bouton dédié (comme "Ma carte"), il sort donc de la liste des onglets
const navigationTabs = computed(() =>
  NavigationTabsService.getContent().filter((tab) => tab.value !== NavigationTabs.DIVERCITY_SPACE)
)
</script>

<style lang="scss">
.Header {
  &--desktop {
    $dim-logo: 10rem;

    &::after {
      width: 50vw;
      min-height: 50rem;
      height: 80vh;
    }

    .Header__banner {
      background: rgb(var(--v-theme-main-yellow));

      .Header__bannerContent {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 0; // retiré : provoquait le décalage avec Header__navContent, qui n'a pas ce padding
        color: rgb(var(--v-theme-main-blue));
        height: var(--dim-banner-h);

        .Header__bannerLink {
          display: flex;
          flex-flow: row nowrap;
          align-items: center;
          text-decoration: none;
          color: rgb(var(--v-theme-main-blue));
          gap: 0.375rem;
          font-size: $font-size-xs;
          cursor: pointer;

          span {
            margin-top: 0.125rem;
          }
        }

        // Neutralise le style "bouton" par défaut de LoginButton pour qu'il ressemble
        // au lien texte+icône qu'avaient FAQ/Hotline à cet emplacement.
        .Header__bannerLink--auth {
          :deep(.v-btn) {
            background: transparent !important;
            box-shadow: none;
            color: rgb(var(--v-theme-main-blue));
            padding: 0;
            min-width: 0;
            height: auto;
            font-size: $font-size-xs;
            font-weight: 400;
            letter-spacing: normal;
            text-transform: none;
          }
        }
      }
    }

    .Header__nav {
      background: linear-gradient(to top, transparent 0%, rgb(var(--v-theme-light-yellow)) 100%);
      height: $dim-logo;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;

      .Header__navContent {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-flow: row nowrap;

        .Header__navBlock {
          &--left {
            .Header__appLogo {
              z-index: 10;
              position: relative;
              height: $dim-logo;
              transform: translateY(calc(-1 * var(--dim-banner-h)));
              border-radius: 50%;
            }
          }

          &--right {
            flex: 1 0 auto;
            display: flex;
            flex-flow: row nowrap;
            justify-content: flex-end;
            align-items: center;
            padding: 20px 0 10px 0;
            gap: 0.75rem; // remplace les mr-3 individuels retirés des v-btn
          }
        }
      }
    }
  }
}
</style>