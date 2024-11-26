<script setup>

import { themeConfig } from '@themeConfig'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { requiredValidator } from '@validators'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  image: {
    type: Object,
    required: false,
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'imageData',
])

const isFormValid = ref(false)
const refForm = ref()

const id = ref(0)
const avatar = ref('')
const image = ref('')
const is_slider = ref(null)
const filename = ref([])
const url = ref(null)
const isValid =  ref(null)
const isEdit = ref(false)

const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar Imagen': 'Agregar Imagen'
})

watchEffect(async() => {

    if (props.isDrawerOpen) {

        if (!(Object.entries(props.image).length === 0) && props.image.constructor === Object) {
            isEdit.value = true
            id.value = props.image.id
            avatar.value = themeConfig.settings.urlStorage + props.image.image
            url.value = props.image.url
            is_slider.value = props.image.is_slider === 1 ? true : false
        }
    }
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()

    avatar.value = ''
    image.value = ''
    is_slider.value = 0
    url.value = ''
    filename.value = []

    isEdit.value = false
    id.value = 0
  })
}

const onImageSelected = event => {
  const file = event.target.files[0]

  if (!file) return
  // image.value = file

  URL.createObjectURL(file)

  resizeImage(file, 1200, 1200, 1)
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

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    isValid.value = valid
    if (valid) {
      let formData = new FormData()

      if(image.value !== '')
        formData.append('image', image.value)

      formData.append('id', id.value)
      formData.append('is_slider', (is_slider.value === true) ? 1 : 0)
      formData.append('url', url.value)

      emit('imageData', { data: formData, id: id.value }, isEdit.value ? 'update' : 'create')
      emit('update:isDrawerOpen', false)

      closeNavigationDrawer()
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
    <VDivider class="mt-4"/>
    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VRow>
            <VCol cols="12">
              <VImg
                  v-if="avatar !== null"
                  :src="avatar"
                  :height="200"
                  aspect-ratio="16/9"
                  class="border-img mb-2"
                  :class="((filename.length === 0 && isValid === false)) ? 'border-error' : ''"
                />
            </VCol>
          </VRow>
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol cols="12">
                <VFileInput
                  v-model="filename"
                  label="Imagen"
                  class="mb-2"
                  accept="image/png, image/jpeg, image/bmp"
                  prepend-icon="tabler-camera"
                  @change="onImageSelected"
                  @click:clear="avatar = null"
                   :rules="isEdit ? [] : [requiredValidator]"
                />
              </VCol>
              <VCol cols="12">
                <AppTextField
                  v-model="url"
                  label="Enlace"
                  placeholder="URL Destino"
                  :rules="[requiredValidator]"
                />
              </VCol>
              <VCol cols="7"></VCol>
              <VCol cols="5">
                <VCheckbox
                  v-model="is_slider"
                  :label="capitalizedLabel('Pertenece al Slider?')"
                  class="ms-5"
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
                  type="button"
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

    .border-error {
        border: 1.8px solid rgb(var(--v-theme-error));
    }
</style>
