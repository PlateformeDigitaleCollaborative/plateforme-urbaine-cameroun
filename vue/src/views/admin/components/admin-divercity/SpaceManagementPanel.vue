<template>
  <div class="AdminPanel SpaceManagementPanel" v-if="space">
    <h2 class="SpaceManagementPanel__title">{{ $t('admin.panelDiverCity') }}</h2>

    <div class="Form__fieldCtn">
      <label class="Form__label required">{{ $t('divercity.form.name') }}</label>
      <v-text-field density="compact" variant="outlined" v-model="form.name" />
    </div>

    <div class="Form__fieldCtn">
      <label class="Form__label required">{{ $t('divercity.form.description') }}</label>
      <TextEditor v-model:content-model="form.description" :parent-form-error="descriptionError" />
    </div>

    <div class="Form__fieldCtn">
      <label class="Form__label">{{ $t('divercity.form.maxCapacity') }}</label>
      <v-text-field type="number" density="compact" variant="outlined" v-model.number="form.maxCapacity" />
    </div>

    <v-divider class="my-6" />

    <div class="Form__fieldCtn">
      <label class="Form__label">{{ $t('divercity.form.photos') }}</label>
      <ImagesLoader @updateFiles="handlePhotosUpdate" :existingImages="existingPhotos" />
    </div>

    <v-divider class="my-6" />

    <div class="SpaceManagementPanel__reportsHeader">
      <h3 class="SpaceManagementPanel__subtitle">{{ $t('divercity.form.reportsSection') }}</h3>
      <v-btn variant="text" prepend-icon="$plus" @click="addReportRow">
        {{ $t('divercity.form.addReportYear') }}
      </v-btn>
    </div>

    <div
      v-for="(row, index) in reportRows"
      :key="row.id ?? `new-${index}`"
      class="SpaceManagementPanel__reportRow"
    >
      <v-text-field
        type="number"
        density="compact"
        variant="outlined"
        :label="$t('divercity.form.year')"
        v-model.number="row.year"
        class="SpaceManagementPanel__reportYear"
      />
      <v-select
        density="compact"
        variant="outlined"
        :label="$t('divercity.form.semester')"
        :items="[{ title: $t('divercity.form.semester1'), value: 1 }, { title: $t('divercity.form.semester2'), value: 2 }]"
        v-model="row.semester"
        class="SpaceManagementPanel__reportSemester"
      />
      <v-file-input
        density="compact"
        variant="outlined"
        accept=".pdf"
        :label="$t('divercity.form.report')"
        v-model="row.reportFile"
        :placeholder="row.existingReport ? $t('divercity.form.existingReportPlaceholder') : undefined"
      />
      <v-btn
        icon="$delete"
        variant="text"
        color="main-red"
        density="comfortable"
        class="SpaceManagementPanel__reportDelete"
        @click="removeReportRow(index)"
      />
    </div>

    <v-btn color="main-red" class="mt-4" :loading="isSubmitting" @click="submitAll">
      {{ $t('forms.save') }}
    </v-btn>
  </div>
</template>

<script setup lang="ts">
import ImagesLoader from '@/components/forms/ImagesLoader.vue'
import TextEditor from '@/components/forms/TextEditor.vue'
import type { ContentImageFromUserFile } from '@/models/interfaces/ContentImage'
import type { BaseMediaObject } from '@/models/interfaces/object/MediaObject'
import type {
  Space,
  SpaceHighlight,
  SpaceHighlightSubmission
} from '@/models/interfaces/divercity/Space'
import { SpacesService } from '@/services/divercity/SpacesService'
import FileUploader from '@/services/files/FileUploader'
import { useSpacesStore } from '@/stores/divercity/spacesStore'
import { NotificationType } from '@/models/enums/app/NotificationType'
import { addNotification } from '@/services/notifications/NotificationService'
import { i18n } from '@/plugins/i18n'
import { computed, ref, watch } from 'vue'
import { transformSymfonyRelationToIRIs } from '@/services/utils/UtilsService'

const spacesStore = useSpacesStore()
const space = computed(() => spacesStore.mainSpace)
const descriptionError = ref(false)

const form = ref({
  name: '',
  description: '',
  maxCapacity: 0
})

const existingPhotos = ref<BaseMediaObject[]>([])
const photosToUpload = ref<ContentImageFromUserFile[]>([])

function handlePhotosUpdate(list: any) {
  photosToUpload.value = list.selectedFiles
  existingPhotos.value = list.existingImages
}

// Une ligne par année de rapport. Les statistiques existantes de chaque année
// sont conservées telles quelles (non éditées ici, cf. masquage demandé) pour
// ne pas les perdre lors de l'enregistrement.
interface ReportRow {
  id?: number
  year: number
  semester: 1 | 2
  existingReport: SpaceHighlight['report'] | null
  reportFile: File | null
  statistics: SpaceHighlight['statistics']
}

const reportRows = ref<ReportRow[]>([])

watch(
  space,
  (newSpace) => {
    if (!newSpace) return
    form.value.name = newSpace.name
    form.value.description = newSpace.description
    form.value.maxCapacity = newSpace.maxCapacity
    existingPhotos.value = newSpace.photos

    reportRows.value = [...(newSpace.highlights ?? [])]
      .sort((a, b) => b.year - a.year || b.semester - a.semester)
      .map((highlight) => ({
        id: highlight.id,
        year: highlight.year,
        semester: highlight.semester,
        existingReport: highlight.report ?? null,
        reportFile: null,
        statistics: highlight.statistics ?? []
      }))
  },
  { immediate: true }
)

function addReportRow() {
  const last = reportRows.value[0]
  const nextYear = last ? (last.semester === 2 ? last.year + 1 : last.year) : new Date().getFullYear()
  const nextSemester: 1 | 2 = last ? (last.semester === 2 ? 1 : 2) : 1

  reportRows.value.unshift({
    year: nextYear,
    semester: nextSemester,
    existingReport: null,
    reportFile: null,
    statistics: []
  })
}

const isSubmitting = ref(false)
const isDeleting = ref(false)


async function submitAll() {
  if (!space.value) return
  isSubmitting.value = true
  try {
    const uploadedPhotos = await Promise.all(
      photosToUpload.value.map((img) => FileUploader.uploadMedia(img.file))
    )
    const spacePayload = transformSymfonyRelationToIRIs<Partial<Space>>({
      name: form.value.name,
      description: form.value.description,
      maxCapacity: form.value.maxCapacity,
      photos: [...existingPhotos.value, ...uploadedPhotos]
    })
    await SpacesService.patchSpace(space.value.id, spacePayload)

    for (const row of reportRows.value) {
      let reportIri: string | undefined
      if (row.reportFile) {
        const uploaded = await FileUploader.uploadFile(row.reportFile)
        reportIri = uploaded['@id']
      }


      const payload: SpaceHighlightSubmission = {
        year: row.year,
        semester: row.semester, // conservées telles quelles, non éditées dans cette interface
        statistics: row.statistics,
        ...(reportIri ? { report: reportIri } : {})
      }

      if (row.id) {
        await SpacesService.patchHighlight(row.id, payload)
      } else {
        await SpacesService.postHighlight({ ...payload, space: `/api/spaces/${space.value.id}` })
      }
    }

    await spacesStore.getMainSpace()
    addNotification(i18n.t('divercity.form.submitSuccess'), NotificationType.SUCCESS)
  } catch (error) {
    addNotification(i18n.t('divercity.form.submitError'), NotificationType.ERROR, error as string)
  }
  isSubmitting.value = false
}

async function removeReportRow(index: number) {
  const row = reportRows.value[index]

  if (row.id) {
    const confirmed = window.confirm(i18n.t('divercity.form.deleteReportConfirm'))
    if (!confirmed) return

    isDeleting.value = true
    try {
      await SpacesService.deleteHighlight(row.id)
      reportRows.value.splice(index, 1)
      addNotification(i18n.t('divercity.form.deleteReportSuccess'), NotificationType.SUCCESS)
    } catch (error) {
      addNotification(i18n.t('divercity.form.deleteReportError'), NotificationType.ERROR, error as string)
    }
    isDeleting.value = false
  } else {
    // Ligne pas encore enregistrée : simple retrait local, pas d'appel API
    reportRows.value.splice(index, 1)
  }
}
</script>

<style lang="scss" scoped>
.SpaceManagementPanel {
  padding: 2rem;
  background: white;

  &__title {
    font-size: $font-size-h3;
    margin-bottom: 1.5rem;
  }

  &__subtitle {
    font-size: $font-size-h4;
  }

  &__reportsHeader {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }

  &__reportRow {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 0.75rem;
  }

  &__reportYear {
    max-width: 120px;
    flex: 0 0 auto;
  }

  &__reportSemester {
    max-width: 140px;
    flex: 0 0 auto;
  }

  &__reportDelete {
    flex: 0 0 auto;
    margin-top: 0.25rem; // aligne visuellement avec les champs en variant="outlined"
  }
}
</style>