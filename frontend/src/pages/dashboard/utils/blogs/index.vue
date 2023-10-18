<script setup>

import InstructionLandingInstructionsOverview from '@/views/pages/instructions/InstructionLandingInstructionsOverview.vue'
import InstructionLandingCategories from '@/views/pages/instructions/InstructionLandingCategories.vue'
import { useCategoriesStores } from '@/views/dashboard/categories/useCategories'

const apiData = ref()
const categoriesStores = useCategoriesStores()

// fetching data from the @fake-db
const fetchData = async () => {

  apiData.value = await categoriesStores.allCategories()

}

fetchData()
</script>

<template>
  <section>
  <VCard v-if="apiData">
    <AppCardTitle
      title="Wiki de Iznoval"
      subtitle="¡Aquí encontrarás toda la información necesaria para unirte al equipo!"
      custom-class="rounded-0"
    />

    <!-- 👉 Popular Instructions -->
    <VCardText class="py-12">
      <h5 class="text-h5 text-center my-6">
        Instrucciones Populares
      </h5>

      <InstructionLandingInstructionsOverview :instructions="apiData.instructionsPopulars" />
    </VCardText>

    <!-- 👉 Knowledge Base -->
    <div>
      <VCardText class="bg-var-theme-background py-12">
        <h5 class="text-h5 text-center my-6">
          Instrucciones
        </h5>

        <InstructionLandingCategories :categories="apiData.categories" />
      </VCardText>
    </div>
  </VCard>
</section>
</template>

<route lang="yaml">
  meta:
    action: ver
    subject: página-instrucciones
</route>

