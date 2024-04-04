<script setup>

import { useCategoriesStores } from '@/stores/useBlogCategories'
import { useRoute } from 'vue-router'
import { onBeforeUnmount } from 'vue';

const images = ref([])
const imageZoom = ref(null)
const band = ref(0)

const route = useRoute()
const apiData = ref()
const activeTab = ref(route.params.blog)
const activeBlog = ref()
const data = ref()
const isRequestOngoing = ref(true)

const categoriesStores = useCategoriesStores()

const fetchBlogsData = async () => {

  isRequestOngoing.value = true

  if(Number(route.params.blog)) {
    data.value = await categoriesStores.allCategories()

    apiData.value = data.value.categories.filter(category => category.slug === route.params.category)[0];

    activeBlog.value = apiData.value.blogs.filter(blog => blog.slug === route.params.blog)[0];
  }
  isRequestOngoing.value = false
  
  images.value = imagesContent();
}

watch(activeTab, fetchBlogsData, { immediate: true })

const imagesContent = () => {
  const imgRegExp = /<img.*?src="(.*?)".*?>/g;
  const images = [];
  let match;
  
  while ((match = imgRegExp.exec(activeBlog.value.description))) {
    images.push({ id: images.length + 1, src: match[1], active: false });
  }
  
  return images;
};

const onContentClick = (event) => {
  const target = event.target;
  const imageNode = target.closest('.content-blog img');

  if (imageNode !== null && band.value === 0) {
    imageNode.classList.add('zoomed');

    const imageIndex = images.value.findIndex((element) => element.src === imageNode.src);
    if (!isNaN(imageIndex)) {
      const image = images.value[imageIndex];
      toggleZoom(image);
      band.value = 1
    }
  } else {
    band.value = 0
    const allZoomed = document.querySelectorAll('.zoomed');

    allZoomed.forEach((element) => {
      element.classList.remove('zoomed');
    });
  }
}
    
const toggleZoom = (image) => {
  if (!image.active) {
    image.active = true;
    imageZoom.value = image;
  } else {
    image.active = false;
    imageZoom.value = null;
  }
}

onBeforeUnmount(() => {
  images.value = [];
});

</script>

<template>
  <section>
    <VDialog
      v-model="isRequestOngoing"
      width="300"
      persistent>
            
      <VCard
        color="primary"
        width="300">
              
        <VCardText class="pt-3">
          Cargando

          <VProgressLinear
            indeterminate
            color="white"
            class="mb-0"/>
        </VCardText>
      </VCard>
    </VDialog>
    <VRow v-if="activeBlog && apiData">
      <VCol
        cols="12"    
        md="9"
      >
        <VWindow>
          <VWindowItem>
            <VCard>
              <VCardText class="pb-0">
                <VBtn
                  variant="tonal"
                  :to="{
                    name: 'dashboard-utils-blogs'
                  }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    class="flip-in-rtl"
                  />
                  <span>Volver a Blogs</span>
                </VBtn>
              </VCardText>

              <VCardItem>
                <VCardTitle>{{ activeBlog.title }}</VCardTitle>
              </VCardItem>

              <v-divider />
              <!-- eslint-disable-next-line vue/no-v-html vue/no-v-text-v-html-on-component -->
              <VCardText class="content-blog">
                <span v-html="activeBlog.description" @click="onContentClick"></span>
              </VCardText>
            </VCard>
          </VWindowItem>
        </VWindow>
      </VCol>

      <VCol
        cols="12"
        md="3"
        >
        <h6 class="text-base mb-3">
          {{ apiData.name }}
        </h6>

        <VTabs
          v-model="activeTab"
          direction="vertical"
          class="v-tabs-pill"
        >
          <VTab
            v-for="data in apiData.blogs"
            :key="data.slug"
            :value="data.slug"
            :to="{
              name: 'dashboard-utils-blogs-category-blog',
              params: {
                category: route.params.category,
                blog: data.slug
              },
            }"
          >
            <VTooltip
              open-on-focus
              location="top"
              activator="parent"
              >
              {{ data.title }}
            </VTooltip>
            <span v-if="data.title.length > 27">{{ data.title.slice(0,28) + '...' }}</span>
            <span v-else>{{ data.title }}</span>
            
          </VTab>
        </VTabs>
      </VCol>
    </VRow>
  </section>
</template>

<style lang="scss" scoped>
.v-card .v-card-text, .v-card-item {
  padding: 20px 70px;

  @media (max-width: 767px) {
    padding: 10px 30px;
  }
}
.v-card-title {
  @media (max-width: 767px) {
    display: inline;
    white-space: normal;
  }
}
  .content-blog {
    position: relative;
    padding: 20px 70px;

    :deep(img)  {
      max-width: 100%;
      cursor: pointer;
    }

    :deep(ul)  {
      list-style: disc;
      padding-left: 1.5em;
    }

    :deep(li .ql-indent-1) {
      padding-left: 1em;
    }

    :deep(.zoomed)  {
      position: fixed;
      top: 15%;
      left: 18%;
      width: 59%;
      height: auto;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      border: 3px solid #000;

      @media (max-width: 767px) {
        top: 15%;
        left: 2%;
        width: 95%;
      }
    }

    :deep(.ql-video) {
      padding: 0 15% !important;
      width: 100% !important;
      height: 400px !important;

      @media (max-width: 767px) {
        height: 200px !important;
        padding: 0 10% !important;
      }
    }

    :deep(p br) {
      display: none;
    }

    :deep(.ql-align-center) {
      text-align: center !important;
    }

    :deep(.ql-align-right){
      text-align: right !important;
    }

  }

  .blog {
    height: 350px;
    overflow-y: auto;
    overflow-x: hidden;
  }
</style>

<route lang="yaml">
  navActiveLink: pages-blogs
  meta:
    action: ver
    subject: página-blogs
</route>
