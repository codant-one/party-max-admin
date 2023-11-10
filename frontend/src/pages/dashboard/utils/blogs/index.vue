<script setup>

import BlogLandingBlogsOverview from '@/views/pages/blogs/BlogLandingBlogsOverview.vue'
import BlogLandingCategories from '@/views/pages/blogs/BlogLandingCategories.vue'
import { useCategoriesStores } from '@/stores/useBlogCategories'

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
      title="Actualidad"
      subtitle="¡Aquí encontrarás toda la información necesaria para todos los post!"
      custom-class="rounded-0"
    />

    <!-- 👉 Popular Blogs -->
    <VCardText class="py-12">
      <h5 class="text-h5 text-center my-6">
        Blogs Populares
      </h5>

      <BlogLandingBlogsOverview :blogs="apiData.blogsPopulars" />
    </VCardText>

    <!-- 👉 Knowledge Base -->
    <div>
      <VCardText class="bg-var-theme-background py-12">
        <h5 class="text-h5 text-center my-6">
          Blogs
        </h5>

        <BlogLandingCategories :categories="apiData.categories" />
      </VCardText>
    </div>
  </VCard>
</section>
</template>

<route lang="yaml">
  meta:
    action: ver
    subject: página-blogs
</route>