<!-- src/components/content/CountryFlag.vue -->
<template>
  <span class="CountryFlag" :title="countryName ?? code ?? ''">
    <img
      v-if="code && !hasError"
      class="CountryFlag__image"
      :src="`https://flagcdn.com/w40/${code.toLowerCase()}.png`"
      :alt="code"
      loading="lazy"
      @error="hasError = true"
    />
    <span v-else class="CountryFlag__fallback">
      <v-icon icon="$earth" size="small" color="dark-grey" />
    </span>
  </span>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  code: string | null
  countryName?: string | null
}>()

// Si le CDN de drapeaux est inaccessible (réseau restreint, code pays inconnu),
// on retombe sur une icône générique plutôt que sur une image cassée.
const hasError = ref(false)

watch(
  () => props.code,
  () => {
    hasError.value = false
  }
)
</script>

<style lang="scss">
.CountryFlag {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  min-width: 1.5rem;

  &__image {
    width: 1.5rem;
    height: auto;
    border-radius: 2px;
    display: block;
  }

  &__fallback {
    display: inline-flex;
  }
}
</style>
