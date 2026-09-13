<template>
  <div class="SpaceSheetView" v-if="space">
    <div class="SpaceSheetView__ctn SpaceSheetView__ctn--banner">
      <PageTitle :title="space.name" />
      <div class="SpaceSheetView__description" v-html="formattedDescription"></div>
      <div class="SpaceSheetView__actions">
        <v-btn color="main-blue" variant="outlined" @click="checkAvailability">
          {{ $t('divercity.space.checkAvailability') }}
        </v-btn>
        <v-btn color="main-red" @click="bookSpace">
          {{ $t('divercity.space.bookSpace') }}
        </v-btn>
      </div>
    </div>

    <div class="SpaceSheetView__ctn" v-if="space.photos.length">
      <v-carousel
        cycle
        hide-delimiter-background
        show-arrows="hover"
        height="500"
        class="SpaceSheetView__carousel"
      >
        <v-carousel-item
          v-for="photo in space.photos"
          :key="photo['@id']"
          :src="photo.contentUrl"
          cover
        />
      </v-carousel>
    </div>

    <div class="SpaceSheetView__ctn">
      <DiverCityHighlights />
    </div>

    <div class="SpaceSheetView__ctn" v-if="upcomingBookings.length">
      <SectionBanner :text="$t('divercity.space.upcoming')" />
      <div class="SpaceSheetView__activityGrid SpaceSheetView__activityGrid--upcoming">
        <BookingActivityCard v-for="booking in upcomingBookings" :key="booking.id" :booking="booking" />
      </div>
    </div>

    <div class="SpaceSheetView__ctn" v-if="recentReports.length">
      <SectionBanner :text="$t('divercity.space.activityReports')" />
      <div class="SpaceSheetView__reportsGrid">
        <v-menu
          v-for="highlight in recentReports"
          :key="highlight.id"
          open-on-hover
          location="top"
          :close-on-content-click="false"
        >
          <template v-slot:activator="{ props: menuProps }">
            <a
              v-bind="menuProps"
              :href="highlight.report!.contentUrl"
              target="_blank"
              class="ReportCard"
            >
              <!-- <div class="ReportCard__icon">
                <v-icon icon="$filePdfBox" size="24" color="main-blue" />
              </div> -->
              <div class="ReportCard__info">
                <span class="ReportCard__year">{{ highlight.year }} · S{{ highlight.semester }}</span>
                <span class="ReportCard__label">
                  {{ $t('divercity.space.reportLabel', { year: highlight.year, semester: highlight.semester }) }}
                </span>
              </div>
              <v-icon icon="$downloadOutline" size="18" color="main-blue" class="ReportCard__download" />
            </a>
          </template>
          <div class="SpaceSheetView__reportPreview">
            <iframe :src="highlight.report!.contentUrl" title="Aperçu du rapport" />
          </div>
        </v-menu>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import PageTitle from '@/components/text-elements/PageTitle.vue'
import SectionBanner from '@/components/banners/SectionBanner.vue'
import BookingActivityCard from '@/views/divercity/components/BookingActivityCard.vue'
import { formatHTMLForSheetView } from '@/services/utils/UtilsService'
import { useSpacesStore } from '@/stores/divercity/spacesStore'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApplicationStore } from '@/stores/applicationStore'
import DiverCityStaticKpi from '@/components/content/DiverCityStaticKpi.vue'
import { DialogKey } from '@/models/enums/app/DialogKey'
import { useUserStore } from '@/stores/userStore'
import DiverCityHighlights from './components/DiverCityHighlights.vue'


const applicationStore = useApplicationStore()
const spacesStore = useSpacesStore()
const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const space = computed(() => spacesStore.mainSpace)

const isPreviewOpen = ref(false)
const previewedReport = ref<(typeof recentReports.value)[number] | null>(null)

onMounted(() => {
  applicationStore.isLoading = false
})

const currentHighlight = computed(() => {
  if (!space.value?.highlights?.length) return null
  return [...space.value.highlights].sort(
    (a, b) => b.year - a.year || b.semester - a.semester
  )[0]
})

const recentReports = computed(() => {
  if (!space.value?.highlights?.length) return []
  const currentYear = new Date().getFullYear()
  return [...space.value.highlights]
    .filter((highlight) => highlight.report && highlight.year >= currentYear - 9)
    .sort((a, b) => b.year - a.year || b.semester - a.semester)
})

const formattedDescription = computed(() => formatHTMLForSheetView(space.value?.description as string))
const featuredBookings = computed(() => spacesStore.publicBookings.slice(0, 3))
const upcomingBookings = computed(() => spacesStore.publicBookings.slice(0, 3))

function openReportPreview(highlight: (typeof recentReports.value)[number]) {
  previewedReport.value = highlight
  isPreviewOpen.value = true
}

function checkAvailability() {
  goToOrAskLogin('divercitySpaceAvailability')
}

function bookSpace() {
  goToOrAskLogin('divercitySpaceBooking')
}

function goToOrAskLogin(routeName: string) {
  if (!userStore.userIsLogged) {
    router.replace({
      query: { ...route.query, dialog: DialogKey.AUTH_SIGN_IN, redirect: routeName }
    })
    return
  }
  router.push({ name: routeName })
}
</script>

<style lang="scss">
@import '@/assets/styles/views/SheetView';

.SpaceSheetView {
  .SpaceSheetView__ctn {
    display: flex;
    flex-flow: column nowrap;
    max-width: $dim-container-w;
    margin: 4rem auto;
    gap: 1rem;

    &--banner {
      max-width: none;
      margin: 0 auto 4rem;
      padding: 2rem;
      //border: 1px solid rgb(var(--v-theme-main-grey));

      > * {
        max-width: $dim-container-w;
        margin-left: auto;
        margin-right: auto;
        width: 100%;
      }
    }
  }

  &__reportsGrid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
  }

  &__actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
  }

  &__activityGrid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-top: 1.5rem;

    &--featured {
      grid-template-columns: repeat(3, 1fr);
    }

    &--upcoming {
      grid-template-columns: repeat(3, 1fr);
    }
  }
  &__kpisGrid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    margin: 1.5rem 0;
    justify-content: flex-start;
    align-items: center;
  }

  .ReportCard {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1rem 1.1rem;
    border: 1px solid rgb(var(--v-theme-main-grey));
    border-radius: 12px;
    background: #fff;
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.15s ease, transform 0.15s ease;

    &:hover {
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
      transform: translateY(-2px);
    }

    &__icon {
      flex: none;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      background: rgba(var(--v-theme-main-blue), 0.08);
    }

    &__info {
      display: flex;
      flex-direction: column;
      min-width: 0;
      flex: 1 1 auto;
    }

    &__year {
      font-weight: 700;
      font-size: 0.95rem;
      color: rgb(var(--v-theme-main-blue));
    }

    &__label {
      font-size: 0.8rem;
      color: rgba(0, 0, 0, 0.6);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    &__download {
      flex: none;
      opacity: 0.6;
    }
  }

  .ReportPreviewDialog {
    &__title {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    &__body {
      padding: 0 !important;

      iframe {
        width: 100%;
        height: 65vh;
        border: none;
      }
    }
  }
  
}

@media (max-width: $bp-xl) {
  .SpaceSheetView {
    .SpaceSheetView__ctn {
      margin: 2rem auto;

      &--banner {
        padding: 1rem;
        margin-bottom: 2rem;
      }
    }

    &__reportsGrid {
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }

    &__reportCard {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1rem 1.25rem;
      border: 1px solid rgb(var(--v-theme-main-grey));
      border-radius: 10px;
      text-decoration: none;
      color: rgb(var(--v-theme-main-blue));
      font-weight: 600;
      transition:
        box-shadow 0.15s ease,
        transform 0.15s ease;

      &:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
      }
    }

     &__reportsGrid {
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }

    &__reportPreview {
      width: 280px;
      height: 360px;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);

      iframe {
        width: 100%;
        height: 100%;
        border: none;
      }
    }

    &__actions {
      flex-flow: column nowrap;

      .v-btn {
        width: 100%;
      }
    }

    &__carousel {
      height: 240px !important;
    }

    &__activityGrid {
      grid-template-columns: repeat(2, 1fr);
    }

    &__kpisGrid {
      grid-template-columns: 1fr;
    }
  }
}
</style>