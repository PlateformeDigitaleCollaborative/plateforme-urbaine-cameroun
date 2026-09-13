<template>
  <div class="AdminTable" ref="el">
    <div
      v-if="headers && headers.length"
      class="AdminTable__row AdminTable__row--header"
      :style="{ gridTemplateColumns: gridTemplateColumns }"
    >
      <div v-if="selectable" class="AdminTable__item"></div>
      <div v-if="!!$slots.adminTableItemFirst" class="AdminTable__item AdminTable__item--first"></div>
      <div class="AdminTable__item AdminTable__item--header" v-for="(header, index) in headers" :key="index">
        {{ header }}
      </div>
      <div v-if="!!$slots.editContentCell" class="AdminTable__item AdminTable__item--last"></div>
    </div>

    <div
      class="AdminTable__row"
      v-for="item in paginatedItems"
      :key="item.id"
      :item-id="item.id"
      :class="{
        'AdminTable__row--overlay': isOverlayShownFunction ? isOverlayShownFunction(item) : false,
        'AdminTable__row--clickable': rowClickable,
        'AdminTable__row--selected': selectable && selectedItemId === item.id
      }"
      :style="{ gridTemplateColumns: gridTemplateColumns }"
      @click="handleRowClick(item)"
    >
      <div
        v-if="selectable"
        class="AdminTable__item AdminTable__item--selector"
        @click.stop="toggleSelect(item)"
      >
        <v-icon
          :icon="selectedItemId === item.id ? '$checkboxMarkedCircle' : '$checkboxBlankCircleOutline'"
          size="20"
          :color="selectedItemId === item.id ? 'main-blue' : undefined"
        />
      </div>
      <div v-if="!!$slots.adminTableItemFirst" class="AdminTable__item AdminTable__item--first" @click.stop>
        <slot name="adminTableItemFirst" :item="item"></slot>
      </div>
      <div class="AdminTable__item" v-for="(tableKey, index) in tableKeys" :key="index">
        <img
          loading="lazy"
          v-if="index === 0 && withLogo && getNestedObjectValue(item, logoField)"
          :src="getNestedObjectValue(item, logoField)"
          class="AdminTable__item__logo"
        />
        <template v-if="isChipColumn(tableKey)">
          <v-chip :color="chipColorFn?.(item, tableKey)" size="small">
            {{ getNestedObjectValue(item, tableKey) }}
          </v-chip>
        </template>
        <template v-else-if="isDateColumn(tableKey, item)">
          <v-tooltip :text="new Date(getNestedObjectValue(item, tableKey)).toLocaleDateString()">
            <template v-slot:activator="{ props }">
              <span v-bind="props">{{
                new Date(getNestedObjectValue(item, tableKey)).toLocaleDateString()
              }}</span>
            </template>
          </v-tooltip>
        </template>
        <template v-else>
          <v-tooltip :text="getNestedObjectValue(item, tableKey)">
            <template v-slot:activator="{ props }">
              <span v-bind="props">{{ reduceText(getNestedObjectValue(item, tableKey), 25) }}</span>
            </template>
          </v-tooltip>
        </template>
      </div>
      <div v-if="!!$slots.editContentCell" class="AdminTable__item AdminTable__item--last" @click.stop>
        <slot name="editContentCell" :item="item"></slot>
      </div>
    </div>
    <Pagination :items="items" v-model="paginatedItems" />
  </div>
</template>

<script setup lang="ts">
import Pagination from '@/components/global/Pagination.vue'
import type { Actor } from '@/models/interfaces/Actor'
import type { User } from '@/models/interfaces/auth/User'
import type { AppComment } from '@/models/interfaces/Comment'
import type { HighlightedItem } from '@/models/interfaces/HighlightedItem'
import type { Project } from '@/models/interfaces/Project'
import type { QgisMap } from '@/models/interfaces/QgisMap'
import type { Resource } from '@/models/interfaces/Resource'
import { getNestedObjectValue, reduceText } from '@/services/utils/UtilsService'
import type { SortableEvent } from 'sortablejs'
import { computed, onMounted, ref, watch, type Ref } from 'vue'
import { useDraggable } from 'vue-draggable-plus'
import type { Booking } from '@/models/interfaces/divercity/Booking'
import type { HighlightedResource } from '@/models/interfaces/divercity/HighlightedResource'

type Item = Actor | User | Project | Resource | HighlightedItem | QgisMap | AppComment | Booking | HighlightedResource

const props = withDefaults(
  defineProps<{
    items: Item[]
    tableKeys: string[]
    headers?: string[]
    columnWidths?: string[]
    dateKeys?: string[]
    plainText?: boolean
    isDraggable?: boolean
    withLogo?: boolean
    logoField?: string
    isOverlayShownFunction?: (item: Item) => boolean
    rowClickable?: boolean
    selectable?: boolean
    // Clés de tableKeys à afficher en v-chip coloré plutôt qu'en texte simple
    chipKeys?: string[]
    // Calcule la couleur du chip pour une cellule donnée (item + tableKey concerné)
    chipColorFn?: (item: Item, tableKey: string) => string | undefined
  }>(),
  {
    rowClickable: false,
    dateKeys: () => [],
    selectable: false,
    chipKeys: () => []
  }
)

function isChipColumn(tableKey: string): boolean {
  return props.chipKeys.includes(tableKey)
}

const defaultColumnWidths = ['15%', '40%', '25%', '20%']
const columnWidths = props.columnWidths || defaultColumnWidths
const gridTemplateColumns = computed(() =>
  (props.selectable ? '2.5rem ' : '') + columnWidths.join(' ')
)
const paginatedItems: Ref<Item[]> = ref([])

const selectedItemId = ref<Item['id'] | null>(null)

function toggleSelect(item: Item) {
  if (!props.selectable) return
  selectedItemId.value = selectedItemId.value === item.id ? null : item.id
}

function handleRowClick(item: Item) {
  if (!props.rowClickable) return

  if (!props.selectable) {
    emits('row-click', item)
    return
  }

  if (selectedItemId.value === item.id) {
    emits('row-click', item)
  } else {
    selectedItemId.value = item.id
  }
}

/**
 * Détecte si une chaîne représente une date "réelle".
 * On rejette explicitement les chaînes purement numériques ("2001", "42", "2024"),
 * que `new Date()` interprète à tort comme une année (new Date("2001") -> 01/01/2001),
 * ce qui provoquait l'affichage d'une fausse date à la place d'un titre numérique.
 */
function isStringDate(candidate: string): boolean {
  if (candidate === null || candidate === undefined) return false
  const str = String(candidate).trim()
  if (str === '') return false

  const isoDatePattern = /^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?(\.\d+)?(Z|[+-]\d{2}:\d{2})?)?$/
  if (!isoDatePattern.test(str)) return false

  const date = new Date(str)
  return !isNaN(date.getTime())
}

function isDateColumn(tableKey: string, item: Item): boolean {
  if (props.dateKeys.includes(tableKey)) return true
  return isStringDate(getNestedObjectValue(item, tableKey))
}

const el = ref<HTMLElement | null>(null)
onMounted(() => {
  initDraggable()
})

watch(
  () => paginatedItems.value,
  () => {
    initDraggable()
  }
)

const emits = defineEmits(['update:order', 'row-click'])

const initDraggable = () => {
  if (props.isDraggable) {
    useDraggable(el, paginatedItems, {
      animation: 150,
      onUpdate(e: SortableEvent) {
        emits('update:order', {
          id: e.item.getAttribute('item-id'),
          oldIndex: e.oldIndex,
          newIndex: e.newIndex
        })
      }
    })
  }
}
</script>

<style lang="scss">
.AdminTable {
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 100%;
  margin-top: 30px;
  overflow-x: auto; // filet de sécurité : si jamais ça ne rentre toujours pas, on scroll plutôt que de déborder hors du layout
}
.AdminTable__row {
  display: grid;
  flex-direction: row;
  min-height: 3.5rem;
  width: 100%;
  align-items: center;
  padding-left: 10px;
  padding-right: 10px;
  border-bottom: 1px solid rgb(var(--v-theme-main-grey));
  gap: 8px;
  box-sizing: border-box;

  &--overlay {
    background-color: rgb(var(--v-theme-light-yellow));
  }
  &--clickable {
    cursor: pointer;
    &:hover {
      background-color: rgb(var(--v-theme-light-yellow));
    }
  }

  .AdminTable__item {
    display: flex;
    align-items: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0; // essentiel : sans ça, une grid-column ne peut pas rétrécir en dessous du contenu

    &--last {
      justify-content: flex-end;
      flex-wrap: wrap;
      gap: 8px;
      overflow: visible;
      white-space: normal;
      padding-right: 0;
      min-width: 0;
      align-content: center; // pour bien centrer verticalement si les boutons passent sur 2 lignes
    }
  }
}

.AdminTable__row--header {
  min-height: 2.25rem;
  padding-left: 10px;
  padding-right: 10px;
  border-bottom: 2px solid rgb(var(--v-theme-main-grey));
  cursor: default;

  &:hover {
    background-color: transparent;
  }

  .AdminTable__item--header {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: rgb(var(--v-theme-main-grey));
    white-space: nowrap;
  }
}

.AdminTable__item--selector {
  justify-content: center;
}

.AdminTable__row--selected {
  background-color: rgb(var(--v-theme-light-yellow));
}

@media (max-width: 900px) {
  .AdminTable__row {
    grid-template-columns: 1fr !important;
    row-gap: 4px;
    padding: 10px;

    .AdminTable__item {
      white-space: normal;

      &--last {
        justify-content: flex-start;
        margin-top: 6px;
      }
    }
  }

  .AdminTable__row--header {
    display: none; // en-têtes peu utiles quand chaque ligne passe en 1 colonne
  }
}

.AdminTable__item__logo {
  width: 2rem;
  height: 2rem;
  object-fit: cover;
  margin-right: 10px;
}
</style>