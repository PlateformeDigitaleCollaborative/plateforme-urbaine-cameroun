<!-- src/views/admin/components/admin-stats/AdminStatsPanel.vue -->
<template>
  <div class="AdminStatsPanel">
    <div class="AdminStatsPanel__header">
      <v-btn-toggle
        v-model="statsStore.days"
        mandatory
        density="compact"
        color="main-blue"
        variant="outlined"
        @update:model-value="statsStore.getStats($event)"
      >
        <v-btn :value="7">{{ $t('admin.stats.range7') }}</v-btn>
        <v-btn :value="30">{{ $t('admin.stats.range30') }}</v-btn>
        <v-btn :value="90">{{ $t('admin.stats.range90') }}</v-btn>
      </v-btn-toggle>
    </div>

    <transition name="fade" mode="out-in">
      <div class="AdminStatsPanel__content" v-if="hasData" key="content">
        <section class="AdminStatsPanel__section">
          <SectionBanner :text="$t('admin.stats.audience.title')" />
          <div class="AdminStatsPanel__grid">
            <DiverCityStaticKpi
              :label="$t('admin.stats.audience.totalViews')"
              :value="statsStore.audience?.totalViews ?? 0"
              icon="$eyeOutline"
            />
            <DiverCityStaticKpi
              :label="$t('admin.stats.audience.uniqueVisitors')"
              :value="statsStore.audience?.uniqueVisitors ?? 0"
              icon="$accountGroup"
            />
          </div>
          <StatsLineChart :series="audienceSeries" />
          <div class="AdminStatsPanel__subTitle">{{ $t('admin.stats.audience.topPages') }}</div>
          <StatsTopList :items="topPagesItems" :empty-label="$t('admin.stats.empty')" />
        </section>

        <section class="AdminStatsPanel__section">
          <SectionBanner :text="$t('admin.stats.connections.title')" />
          <div class="AdminStatsPanel__grid">
            <DiverCityStaticKpi
              :label="$t('admin.stats.connections.totalConnections')"
              :value="statsStore.connections?.totalConnections ?? 0"
              icon="$loginVariant"
            />
            <DiverCityStaticKpi
              :label="$t('admin.stats.connections.activeUsers')"
              :value="statsStore.connections?.activeUsers ?? 0"
              icon="$accountCircle"
            />
          </div>
          <StatsLineChart :series="connectionsSeries" />
        </section>

        <section class="AdminStatsPanel__section">
          <SectionBanner :text="$t('admin.stats.contentViews.title')" />
          <div class="AdminStatsPanel__contentGrid">
            <div>
              <div class="AdminStatsPanel__subTitle">
                {{ $t('admin.stats.contentViews.topActors') }}
              </div>
              <StatsTopList :items="topActorsItems" :empty-label="$t('admin.stats.empty')" />
            </div>
            <div>
              <div class="AdminStatsPanel__subTitle">
                {{ $t('admin.stats.contentViews.topProjects') }}
              </div>
              <StatsTopList :items="topProjectsItems" :empty-label="$t('admin.stats.empty')" />
            </div>
          </div>
        </section>
      </div>

      <div class="AdminStatsPanel__loading" v-else-if="statsStore.isLoading" key="loading">
        <v-progress-circular indeterminate color="main-blue" size="36" width="3" />
        <span class="AdminStatsPanel__loadingText">{{ $t('admin.stats.loading') }}</span>
      </div>

      <div class="AdminStatsPanel__empty" v-else key="empty">
        {{ $t('admin.stats.empty') }}
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import SectionBanner from '@/components/banners/SectionBanner.vue'
import DiverCityStaticKpi from '@/components/content/DiverCityStaticKpi.vue'
import StatsLineChart from '@/components/content/StatsLineChart.vue'
import StatsTopList from '@/components/content/StatsTopList.vue'
import { useStatsStore } from '@/stores/statsStore'
import { computed, onMounted } from 'vue'

const statsStore = useStatsStore()

onMounted(() => {
  if (!statsStore.audience && !statsStore.connections && !statsStore.contentViews) {
    statsStore.getStats()
  }
})

const hasData = computed(
  () => !!statsStore.audience || !!statsStore.connections || !!statsStore.contentViews
)

const audienceSeries = computed(
  () =>
    statsStore.audience?.dailySeries.map((p) => ({ date: p.date, value: p.views })) ?? []
)

const connectionsSeries = computed(
  () =>
    statsStore.connections?.dailySeries.map((p) => ({ date: p.date, value: p.count })) ?? []
)

const topPagesItems = computed(
  () => statsStore.audience?.topPages.map((p) => ({ path: p.path, label: p.path, views: p.views })) ?? []
)

const topActorsItems = computed(
  () =>
    statsStore.contentViews?.topActors.map((a) => ({
      path: a.path,
      label: a.slug,
      views: a.views
    })) ?? []
)

const topProjectsItems = computed(
  () =>
    statsStore.contentViews?.topProjects.map((p) => ({
      path: p.path,
      label: p.slug,
      views: p.views
    })) ?? []
)
</script>

<style lang="scss">
.AdminStatsPanel {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;

  &__header {
    display: flex;
    justify-content: flex-end;
  }

  &__content {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
  }

  &__section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }

  &__contentGrid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }

  &__subTitle {
    font-weight: 700;
    color: rgb(var(--v-theme-main-blue));
  }

  &__loading,
  &__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 3rem 1rem;
    color: rgb(var(--v-theme-dark-grey));
  }

  &__loadingText {
    font-size: $font-size-sm;
  }
}

@media (max-width: $bp-md) {
  .AdminStatsPanel {
    &__grid,
    &__contentGrid {
      grid-template-columns: 1fr;
    }
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
