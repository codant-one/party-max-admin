<script setup>

import sittingGirlWithLaptop from '@images/illustrations/sitting-girl-with-laptop.png'
import { useCategoriesStores } from '@/stores/useFaqCategories'

const categoriesStores = useCategoriesStores()

const categories = ref([])
const activeTab = ref(0)
const activeQuestion = ref(0)
const isRequestOngoing = ref(true)

const fetchData = async () => {

    isRequestOngoing.value = true

    let response = await categoriesStores.allFaqs()

    categories.value = response.categories
    activeTab.value = response.categories[0].name

    isRequestOngoing.value = false
}

watch(activeTab, () => activeQuestion.value = 0)
fetchData()

const contactUs = [
  {
    icon: 'tabler-phone',
    via: '+(57) 310 4870 310',
    tagLine: '¡Siempre estaremos felices de ayudar!'
  },
  {
    icon: 'tabler-mail',
    via: 'fiesta@partymax.co',
    tagLine: '¡La mejor manera de obtener una respuesta más rápido!',
  },
]
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

    <!-- 👉 Search -->
    <AppCardTitle
      title="Preguntas Frecuentes"
      subtitle=" "
      custom-class="rounded-0"
    />

    <!-- 👉 Faq sections and questions -->
    <VRow style="margin-top: 10px">
      <VCol
        v-show="categories.length"
        cols="12"
        sm="4"
        lg="3"
        class="position-relative"
      >
        <!-- 👉 Tabs -->
        <VTabs
          v-model="activeTab"
          direction="vertical"
          class="v-tabs-pill"
          grow
        >
          <VTab
            v-for="category in categories"
            :key="category.name"
            :value="category.name"
            class="text-high-emphasis"
          >
            <VIcon
              :icon="category.icon"
              :size="20"
              start
            />
            {{ category.name }}
          </VTab>
        </VTabs>
        <VImg
          :width="245"
          :src="sittingGirlWithLaptop"
          class="d-none d-sm-block mt-10 mx-auto"
        />
      </VCol>

      <VCol
        cols="12"
        sm="8"
        lg="9"
      >
        <!-- 👉 Windows -->
        <VWindow
          v-model="activeTab"
          class="faq-v-window disable-tab-transition"
        >
          <VWindowItem
            v-for="category in categories"
            :key="category.name"
            :value="category.name"
          >
            <div class="d-flex align-center mb-6">
              <VAvatar
                rounded
                :color="category.color ?? 'primary'"
                variant="tonal"
                class="me-3"
                size="large"
              >
                <VIcon
                  :size="32"
                  :icon="category.icon"
                />
              </VAvatar>

              <div>
                <h6 class="text-h6">
                  {{ category.name }}
                </h6>
                <span class="text-sm">{{ category.description }}</span>
              </div>
            </div>

            <VExpansionPanels
              v-model="activeQuestion"
              multiple
            >
              <VExpansionPanel
                v-for="item in category.faqs"
                :key="item.title"
                :title="item.title"
                :text="item.description"
              />
            </VExpansionPanels>
          </VWindowItem>
        </VWindow>
      </VCol>

      <VCol
        v-show="!categories.length"
        cols="12"
        :class="!categories.length ? 'd-flex justify-center align-center' : ''"
      >
        <VIcon
          icon="tabler-help"
          start
          size="20"
        />
        <span class="text-base font-weight-medium">
            Datos no disponibles
        </span>
      </VCol>
    </VRow>
   
    <!-- 👉 You still have a question? -->
    <div class="text-center pt-15">
      <VChip
        label
        color="primary"
        size="small"
        class="mb-2"
      >
        PREGUNTAS?
      </VChip>

      <h5 class="text-h5 mb-2">
        ¿Aún tienes alguna pregunta?
      </h5>
      <p>
        Si no puede encontrar preguntas en nuestras preguntas frecuentes, puede contactarnos. ¡Te responderemos en breve!
      </p>

      <!-- contacts -->
      <VRow class="mt-4">
        <VCol
          v-for="contact in contactUs"
          :key="contact.icon"
          sm="6"
          cols="12"
        >
          <VCard
            flat
            variant="tonal"
          >
            <VCardText>
              <VAvatar
                rounded
                color="primary"
                variant="tonal"
                class="me-3"
              >
                <VIcon :icon="contact.icon" />
              </VAvatar>
            </VCardText>
            <VCardText>
              <h6 class="text-h6 mb-2">
                {{ contact.via }}
              </h6>
              <span>{{ contact.tagLine }}</span>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>
    </section>
</template>
<style lang="scss">
  .faq-v-window {
    .v-window__container {
      z-index: 0;
    }
  }
</style>
<route lang="yaml">
    meta:
      action: ver
      subject: página-faqs
</route>