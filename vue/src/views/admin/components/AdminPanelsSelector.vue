<template>
  <div class="Admin__panelSelector_container">
    <v-expansion-panels variant="accordion" v-model="adminStore.selectedAdminPanel" elevation="0">
      <v-expansion-panel
        v-if="userStore.userIsAdmin()"
        :readonly="true"
        :value="AdministrationPanels.MEMBERS"
        @click="adminStore.selectedAdminPanel = AdministrationPanels.MEMBERS"
        class="text-main-blue"
        :class="{
          Admin__selectedPanel: adminStore.selectedAdminPanel === AdministrationPanels.MEMBERS
        }"
      >
        <v-expansion-panel-title>
          {{ $t('admin.panelMembers') }}
          <template v-slot:actions>
            <v-icon color="main-blue" icon="$chevronRight"></v-icon>
          </template>
        </v-expansion-panel-title>
      </v-expansion-panel>

      <v-expansion-panel
        v-if="userStore.userIsAdmin()"
        :title="$t('admin.panelContent')"
        :value="AdministrationPanels.CONTENT"
        :class="{
          Admin__selectedPanel: adminStore.selectedAdminPanel === AdministrationPanels.CONTENT
        }"
        class="text-main-blue"
      >
        <v-expansion-panel-text>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminActors' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelContentActors') }}
            <div class="Admin__itemToValidateCounter" v-if="actorsToValidate > 0">
              {{ actorsToValidate }}
            </div>
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminProjects' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelContentProjects') }}
            <div class="Admin__itemToValidateCounter" v-if="projectsToValidate > 0">
              {{ projectsToValidate }}
            </div>
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminResources' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelContentResources') }}
            <div class="Admin__itemToValidateCounter" v-if="resourcesToValidate > 0">
              {{ resourcesToValidate }}
            </div>
          </router-link>
        </v-expansion-panel-text>
      </v-expansion-panel>

      <v-expansion-panel
        v-if="userStore.userIsAdmin()"
        :title="$t('admin.panelMap')"
        :value="AdministrationPanels.MAPS"
        :class="{
          Admin__selectedPanel: adminStore.selectedAdminPanel === AdministrationPanels.MAPS
        }"
        class="text-main-blue"
      >
        <v-expansion-panel-text>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminPredefinedMaps' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelPredefinedMaps') }}
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminThematicMaps' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelThematicMaps') }}
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminQgisMaps' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelMapQGIS') }}
          </router-link>
        </v-expansion-panel-text>
      </v-expansion-panel>

      <v-expansion-panel
        v-if="userStore.userIsAdmin()"
        :value="AdministrationPanels.HIGHLIGHTS"
        :class="{
          Admin__selectedPanel: adminStore.selectedAdminPanel === AdministrationPanels.HIGHLIGHTS
        }"
        class="text-main-blue"
      >
        <router-link :to="{ name: 'adminHighlights' }">
          <v-expansion-panel-title>
            {{ $t('admin.panelHighlightedItems') }}
            <template v-slot:actions>
              <v-icon color="main-blue" icon="$chevronRight"></v-icon>
            </template>
          </v-expansion-panel-title>
        </router-link>
      </v-expansion-panel>

      <v-expansion-panel
        v-if="userStore.userIsAdmin()"
        :value="AdministrationPanels.STATS"
        :class="{
          Admin__selectedPanel: adminStore.selectedAdminPanel === AdministrationPanels.STATS
        }"
        class="text-main-blue"
      >
        <router-link :to="{ name: 'adminStats' }">
          <v-expansion-panel-title>
            {{ $t('admin.panelStats') }}
            <template v-slot:actions>
              <v-icon color="main-blue" icon="$chevronRight"></v-icon>
            </template>
          </v-expansion-panel-title>
        </router-link>
      </v-expansion-panel>

      <v-expansion-panel
        v-if="userStore.userIsAdmin()"
        :title="$t('admin.panelComments')"
        :value="AdministrationPanels.COMMENTS"
        :class="{
          Admin__selectedPanel: adminStore.selectedAdminPanel === AdministrationPanels.COMMENTS
        }"
        class="text-main-blue"
      >
        <v-expansion-panel-text>
          <router-link class="Admin__itemSelector" :to="{ name: 'actorsComments' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelContentActors') }}
            <div class="Admin__itemToValidateCounter" v-if="actorsCommentsToRead > 0">
              {{ actorsCommentsToRead }}
            </div>
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'projectsComments' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelContentProjects') }}
            <div class="Admin__itemToValidateCounter" v-if="projectsCommentsToRead > 0">
              {{ projectsCommentsToRead }}
            </div>
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'resourcesComments' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelContentResources') }}
            <div class="Admin__itemToValidateCounter" v-if="resourcesCommentsToRead > 0">
              {{ resourcesCommentsToRead }}
            </div>
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'mapComments' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelCommentsMap') }}
            <div class="Admin__itemToValidateCounter" v-if="mapCommentsToRead > 0">
              {{ mapCommentsToRead }}
            </div>
          </router-link>
        </v-expansion-panel-text>
      </v-expansion-panel>

      <v-expansion-panel
        :title="$t('admin.panelDiverCity')"
        :value="AdministrationPanels.DIVERCITY"
        :class="{
          Admin__selectedPanel: adminStore.selectedAdminPanel === AdministrationPanels.DIVERCITY
        }"
        class="text-main-blue"
      >
        <v-expansion-panel-text>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminDiverCityBookings' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelDiverCityBookings') }}
            <div class="Admin__itemToValidateCounter" v-if="pendingBookingsCount > 0">
              {{ pendingBookingsCount }}
            </div>
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminDiverCityBlockedPeriods' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelDiverCityBlockedPeriods') }}
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminDiverCityHighlights' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelDiverCityHighlights') }}
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminDiverCityKpis' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelDiverCityKpis') }}
          </router-link>
          <router-link class="Admin__itemSelector" :to="{ name: 'adminDiverCitySpace' }">
            <v-icon icon="$circleSmall" size="large"></v-icon>
            {{ $t('admin.panelDiverCitySpace') }}
          </router-link>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>
  </div>
</template>
<script setup lang="ts">
import { AdministrationPanels } from '@/models/enums/app/AdministrationPanels'
import { CommentOrigin } from '@/models/interfaces/Comment'
import { useActorsStore } from '@/stores/actorsStore'
import { useAdminStore } from '@/stores/adminStore'
import { useCommentStore } from '@/stores/commentStore'
import { useProjectStore } from '@/stores/projectStore'
import { useResourceStore } from '@/stores/resourceStore'
import { useUserStore } from '@/stores/userStore'
import { useBookingsStore } from '@/stores/divercity/bookingsStore'
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'

const adminStore = useAdminStore()
const actorsStore = useActorsStore()
const projectStore = useProjectStore()
const resourceStore = useResourceStore()
const commentStore = useCommentStore()
const bookingsStore = useBookingsStore()
const userStore = useUserStore()
const router = useRouter()



watch(
  () => adminStore.selectedAdminPanel,
  () => {
    if (adminStore.selectedAdminPanel === AdministrationPanels.MEMBERS) {
      router.push({ name: 'adminUsers' })
      adminStore.selectedAdminItem = null
    } else if (adminStore.selectedAdminPanel === AdministrationPanels.CONTENT) {
      router.push({ name: 'adminActors' })
      adminStore.selectedAdminItem = AdministrationPanels.CONTENT_ACTORS
    } else if (adminStore.selectedAdminPanel === AdministrationPanels.MAPS) {
      router.push({ name: 'adminPredefinedMaps' })
      adminStore.selectedAdminItem = AdministrationPanels.MAP_ATLAS
    } else if (adminStore.selectedAdminPanel === AdministrationPanels.DIVERCITY) {
      router.push({ name: 'adminDiverCityBookings' })
      adminStore.selectedAdminItem = AdministrationPanels.DIVERCITY_SPACE
    } else if (adminStore.selectedAdminPanel === AdministrationPanels.STATS) {
      router.push({ name: 'adminStats' })
    } else {
      router.push({ name: 'actorsComments' })
      adminStore.selectedAdminItem = AdministrationPanels.COMMENTS_ACTORS
    }
  }
)

const actorsToValidate = computed(() => actorsStore.actors.filter((x) => !x.isValidated).length)
const projectsToValidate = computed(
  () => projectStore.projects.filter((x) => !x.isValidated).length
)
const resourcesToValidate = computed(
  () => resourceStore.resources.filter((x) => !x.isValidated).length
)
const actorsCommentsToRead = computed(
  () =>
    commentStore.comments.filter((x) => !x.readByAdmin && x.origin === CommentOrigin.ACTOR).length
)
const projectsCommentsToRead = computed(
  () =>
    commentStore.comments.filter((x) => !x.readByAdmin && x.origin === CommentOrigin.PROJECT).length
)
const resourcesCommentsToRead = computed(
  () =>
    commentStore.comments.filter((x) => !x.readByAdmin && x.origin === CommentOrigin.RESOURCE)
      .length
)
const mapCommentsToRead = computed(
  () => commentStore.comments.filter((x) => !x.readByAdmin && x.origin === CommentOrigin.MAP).length
)

const pendingBookingsCount = computed(
  () => bookingsStore.bookings.filter((b) => (b.status as any)?.code === 'EN_ATTENTE').length
)
</script>

<style lang="scss">
.Admin {
  &__panelSelector_container {
    width: 100%;

    a {
      color: rgb(var(--v-theme-main-blue));
      text-decoration: none;
    }
  }
  &__selectedPanel {
    border-left: 4px solid rgb(var(--v-theme-main-blue));
    font-weight: 700;
  }
  &__itemSelector {
    display: flex;
    align-items: center;
    font-weight: 500;
    cursor: pointer;
    height: 40px;
    text-decoration: none;
    color: rgb(var(--v-theme-main-blue));
    &:hover {
      background-color: rgb(var(--v-theme-light-yellow));
    }
    &.router-link-exact-active {
      font-weight: 700;
      background-color: rgb(var(--v-theme-light-yellow));
    }
  }
  &__itemToValidateCounter {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgb(var(--v-theme-main-red));
    border-radius: 1rem;
    height: 16px;
    min-width: 16px;
    padding: 0.25rem;
    color: white;
    font-size: 10px;
    font-weight: 400;
    margin-left: 8px;
  }
}
</style>