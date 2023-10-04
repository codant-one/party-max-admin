<script setup>

import { useFaqsStores } from '@/stores/useFaqs'
import { themeConfig } from '@themeConfig'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { requiredValidator } from '@validators'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  faq: {
    type: Object,
    required: false,
  }

})

const emit = defineEmits([
  'update:isDrawerOpen',
  'faqData',
])

const faqsStores = useFaqsStores()

const isFormValid = ref(false)
const refForm = ref()

const faqs = ref([])
const id = ref(0)
const title = ref('')
const faq_id = ref()
const description = ref('')
const image = ref('')
const avatar = ref('')
const filename = ref([])
const isEdit = ref(false)

const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar FAQ': 'Agregar FAQ'
})

watchEffect(async() => {
    if (props.isDrawerOpen) {
        let data = { limit: -1 }

        await faqsStores.fetchFaqsOrder(data)

        faqs.value = faqsStores.getFaqs

        if (!(Object.entries(props.faq).length === 0) && props.faq.constructor === Object) {
            isEdit.value = true
            id.value = props.faq.id
            title.value = props.faq.title
            description.value = props.faq.description
            faq_id.value = props.faq.faq_id
            avatar.value = props.faq.image === null ? '' : themeConfig.settings.urlStorage + props.faq.image
        }
    }
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()

    image.value = ''
    avatar.value = ''
    filename.value = []
    isEdit.value = false
    id.value = 0
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      let formData = new FormData()

      formData.append('id', id.value)
      formData.append('title', title.value)
      formData.append('description', description.value)

      emit('faqData', { data: formData, id: id.value }, isEdit.value ? 'update' : 'create')
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        image.value = ''
        avatar.value = ''
        filename.value = []
        isEdit.value = false
        id.value = 0
      })
    }
  })
}


const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const closeDropdown = () => { 
  document.getElementById("selectFaq").blur()
}

</script>

<template>
  <VNavigationDrawer
    temporary
    :width="550"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
    <div class="d-flex align-center pa-6 pb-1">
      <h6 class="text-h6">
        {{ getTitle }}
      </h6>

      <VSpacer />

      <!-- 👉 Close btn -->
      <VBtn
        variant="tonal"
        color="default"
        icon
        size="32"
        class="rounded"
        @click="closeNavigationDrawer"
      >
        <VIcon
          size="18"
          icon="tabler-x"
        />
      </VBTn>
    </div>

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <v-row>
            <v-col cols="12" class="d-flex justify-center align-center">
                <VImg
                    v-if="avatar !== null"
                    :src="avatar"
                    :height="200"
                    aspect-ratio="16/9"
                    class="border-img"
                />
            </v-col>

            <VCol cols="12">
              <VFileInput
                v-model="filename"
                label="Imagen"
                class="mb-2"
                accept="image/png, image/jpeg, image/bmp"
                prepend-icon="tabler-camera"
                @click:clear="avatar = null"
              />
            </VCol>
          </v-row>

          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Name -->
              <VCol cols="12">
                <VTextField
                  v-model="title"
                  :rules="[requiredValidator]"
                  label="Titulo"
                />
              </VCol>

              <VCol cols="12">
                <VAutocomplete
                    id="selectFaq"
                    v-model="faq_id"
                    label="FAQs:"
                    :items="faqs"
                    :item-title="item => item.title"
                    :item-value="item => item.id"
                    autocomplete="off"
                    :menu-props="{ maxHeight: '300px' }">
                    <template v-slot:selection="{ item, index }">
                        <v-chip v-if="index < 2">
                            <span>{{ item.title }}</span>
                        </v-chip>
                        <span
                            v-if="index === 2"
                            class="text-grey text-caption align-self-center"
                        >
                            (+{{ faq_id.length - 2 }} otros)
                        </span>
                    </template>
                    <template v-slot:item="{ props, item }">
                        <v-list-item
                            v-bind="props"
                            :title="item?.raw?.title"
                            :style="{ 
                                paddingLeft: `${(item?.raw?.level) * 20}px`
                            }"
                        >
                            <template v-slot:prepend="{ isActive }">
                                <v-list-item-action start>
                                    <v-checkbox-btn :model-value="isActive"></v-checkbox-btn>
                                </v-list-item-action>
                            </template>
                        </v-list-item>
                    </template>
                    <template v-slot:append-item>
                        <v-divider class="mt-2"></v-divider>
                        <v-list-item title="Cerrar Opciones" class="text-right">
                        <template v-slot:append>
                            <VBtn
                            size="small"
                            variant="plain"
                            icon="tabler-x"
                            color="black"
                            :ripple="false"
                            @click="closeDropdown"/>
                        </template>
                        </v-list-item>
                    </template>
                </VAutocomplete>
              </VCol>

              <!-- 👉 description -->
              <VCol cols="12">
                <VTextarea
                  v-model="description"
                  rows="4"
                  label="Descripción"
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  {{ isEdit ? 'Actualizar': 'Agregar' }}
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="secondary"
                  @click="closeNavigationDrawer"
                >
                  Cancelar
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>

<style scoped>
    .border-img {
        border: 1.8px solid rgba(var(--v-border-color), var(--v-border-opacity));
        border-radius: 6px;
    }

    .border-img .v-img__img--contain {
        padding: 10px;
    }
</style>
