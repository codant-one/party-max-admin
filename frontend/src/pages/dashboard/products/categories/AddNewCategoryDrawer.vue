<script setup>

import { useCategoriesStores } from '@/stores/useCategories'
import { themeConfig } from '@themeConfig'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { requiredValidator } from '@validators'
import { avatarText } from '@/@core/utils/formatters'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  category: {
    type: Object,
    required: false,
  }

})

const emit = defineEmits([
  'update:isDrawerOpen',
  'categoryData',
])

const categoriesStores = useCategoriesStores()

const isFormValid = ref(false)
const refForm = ref()

const categories = ref([])
const id = ref(0)
const name = ref('')
const category_id = ref()
const description = ref('')
const image = ref('')
const avatar = ref('')
const filename = ref([])
const isEdit = ref(false)

const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar Categoría': 'Agregar Categoría'
})

watchEffect(async() => {
    if (props.isDrawerOpen) {
        let data = { limit: -1 }

        await categoriesStores.fetchCategoriesOrder(data)

        categories.value = categoriesStores.getCategories

        if (!(Object.entries(props.category).length === 0) && props.category.constructor === Object) {
            isEdit.value = true
            id.value = props.category.id
            name.value = props.category.name
            description.value = props.category.description
            avatar.value = props.category.image === null ? null : themeConfig.configuraciones.urlStorage + props.category.image
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
      formData.append('image', image.value ?? null)
      formData.append('name', name.value)
      formData.append('description', description.value)
      formData.append('category', category.value)


      emit('categoryData', { data: formData, id: id.value }, isEdit.value ? 'update' : 'create')
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

const onImageSelected = event => {
  const file = event.target.files[0]

  if (!file) return
  // image.value = file

  URL.createObjectURL(file)

  resizeImage(file, 400, 400, 0.9)
    .then(async blob => {
        image.value = blob
        let r = await blobToBase64(blob)
        avatar.value = 'data:image/jpeg;base64,' + r
    })
}

const resizeImage = function(file, maxWidth, maxHeight, quality) {
  return new Promise((resolve, reject) => {
    const img = new Image()

    img.src = URL.createObjectURL(file)
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')

      let width = img.width
      let height = img.height

      if (maxWidth && width > maxWidth) {
        height *= maxWidth / width
        width = maxWidth
      }

      if (maxHeight && height > maxHeight) {
        width *= maxHeight / height
        height = maxHeight
      }

      canvas.width = width
      canvas.height = height

      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(blob => {
        resolve(blob)
      }, file.type, quality)
    }
    img.onerror = error => {
      reject(error)
    }
  })
}

const blobToBase64 = blob => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.readAsDataURL(blob)
    reader.onload = () => {
      resolve(reader.result.split(',')[1])
    }
    reader.onerror = error => {
      reject(error)
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="400"
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
                  aspect-ratio="4/3"
                />
            </v-col>

            <VCol cols="12">
              <VFileInput
                v-model="filename"
                label="Imagen"
                class="mb-2"
                accept="image/png, image/jpeg, image/bmp"
                prepend-icon="tabler-camera"
                @change="onImageSelected"
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
                  v-model="name"
                  :rules="[requiredValidator]"
                  label="Nombre"
                />
              </VCol>

              <VCol cols="12">
                <VAutocomplete
                    id="selectFilterUsers"
                    v-model="category_id"
                    label="Categorías:"
                    :items="categories"
                    :item-title="item => item.name"
                    :item-value="item => item.id"
                    autocomplete="off"
                    multiple
                    :menu-props="{ maxHeight: '300px' }">
                    <template v-slot:selection="{ item, index }">
                        <v-chip v-if="index < 2">
                            <span>{{ item.title }}</span>
                        </v-chip>
                        <span
                            v-if="index === 2"
                            class="text-grey text-caption align-self-center"
                        >
                            (+{{ category_id.length - 2 }} otros)
                        </span>
                    </template>
                    <template v-slot:item="{ props, item }">
                        <v-list-item
                            v-bind="props"
                            :title="item?.raw?.name"
                            :style="{ 
                                paddingLeft: `${(item?.raw?.level) * 20}px`
                            }"
                        >
                            <template v-slot:prepend>
                              <v-checkbox-btn
                                :model-value="item?.raw?.id"
                              ></v-checkbox-btn>
                            </template>
                        </v-list-item>
                    </template>
                </VAutocomplete>
              </VCol>

              <!-- 👉 description -->
              <VCol cols="12">
                <VTextarea
                  v-model="description"
                  :rules="[requiredValidator]"
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
