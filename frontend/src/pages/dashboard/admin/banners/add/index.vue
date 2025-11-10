<script setup>

import router from '@/router'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { useBannersStores } from '@/stores/useBanners'

const bannersStores = useBannersStores()

const isRequestOngoing = ref(true)

const banners = ref([])
const refForm = ref()
const isFormValid = ref(false)
const error = ref(undefined)

const emitter = inject("emitter")

const name = ref('')
const selectedTags = ref([])
const existingTags = ref([])
const isValid =  ref(null)
const avatars = ref([])
const avatarsOld = ref([])
const filename = ref([])
const filename2 = ref([])
const filename3 = ref([])
const filename4 = ref([])

watchEffect(fetchData)

async function fetchData() {
    isRequestOngoing.value = false
    avatars.value[0] = '';
    avatarsOld.value[0] = '';
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
}

const addTag = (event) => {
    const newTag = event.target.value.trim();
      
    if (newTag && !selectedTags.value.includes(newTag)) {
        selectedTags.value.push(newTag);
        
        if (!existingTags.value.includes(newTag)) {
          existingTags.value.push(newTag);
        }
        
       event.target.value = '';
    }
}

const onImageSelected = (event, id) => {
  const file = event.target.files[0]

  if (!file) return
  // banners.value = file

  URL.createObjectURL(file)

  resizeImage(file, 1170, 400, 0.9)
    .then(async blob => {
        banners.value[id] = blob
        let r = await blobToBase64(blob)
        avatars.value[id] = 'data:image/jpeg;base64,' + r
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

const onSubmit = () => {

    refForm.value?.validate().then(({ valid }) => {
        isValid.value = valid
        if (valid) {
            error.value = undefined

            let formData = new FormData()

            formData.append('banner', banners.value[0] ?? null)
            formData.append('banner_2', banners.value[1] ?? null)
            formData.append('banner_3', banners.value[2] ?? null)
            formData.append('banner_4', banners.value[3] ?? null)
            formData.append('name', name.value)

            bannersStores.addBanner(formData)
                .then((res) => {
                if (res.data.success) {

                    let data = {
                        message: 'Banner Creado!',
                        error: false
                    }

                    router.push({ name : 'dashboard-admin-banners'})
                    emitter.emit('toast', data)

                } else {

                    let data = {
                        message: 'ERROR',
                        error: true
                    }

                    router.push({ name : 'dashboard-admin-banners'})
                    emitter.emit('toast', data)
                }
                })
                .catch((err) => {
                    let data = {
                        message: err,
                        error: true
                    }

                    router.push({ name : 'dashboard-admin-banners'})
                    emitter.emit('toast', data)
                })

        }
  })
}
</script>

<template>
    <section>
        <VRow>
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
                            class="mb-0"
                        />
                    </VCardText>
                </VCard>
            </VDialog>
        </VRow>
        <!-- 👉 Form -->
        <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit">
            <VRow>
                <VCol cols="12" md="9">
                    <VCard class="mb-8">
                        <VCardText>
                            <VRow>
                                <VCol cols="12" md="8">
                                    <VTextField
                                        v-model="name"
                                        :rules="[requiredValidator]"
                                        label="Nombre"
                                    />
                                </VCol>
                            </VRow>
                        </VCardText>
                    </VCard>

                    <div >
                            <VDivider />
                            <VCardText>
                                <VRow no-gutters>
                                    <VCol cols="12"  md="2"></VCol>
                                    <VCol cols="12"  md="5">
                                        <VFileInput
                                            v-model="filename"
                                            label="Banner Principal"
                                            class="mb-2 me-2"
                                            accept="image/png, image/jpeg, image/bmp, image/webp"
                                            prepend-icon="tabler-camera"
                                            :rules="[requiredValidator]"
                                            @change="onImageSelected($event, 0)"
                                            @click:clear="avatars[0] = avatarsOld[0]"
                                        />
                                    </VCol>
                                    <!-- 👉 Banner Category 1 -->
                                    <VCol cols="12" class="d-flex justify-center align-center">
                                        <VImg
                                            :class="((filename.length === 0 && isValid === false)) ? 'border-error' : ''"
                                            v-if="avatars[0] !== null"
                                            :src="avatars[0]"
                                            :height="300"
                                            aspect-ratio="1/1"
                                            class="border-img"
                                            cover
                                        />
                                    </VCol>
                                </VRow>
                            </VCardText>

                            <VDivider />

                            <VCardText>
                                <VRow no-gutters>
                                    <VCol cols="12"  md="2"></VCol>
                                    <VCol cols="12"  md="5">
                                        <VFileInput
                                            v-model="filename2"
                                            label="Banner 2"
                                            class="mb-2 me-2"
                                            accept="image/png, image/jpeg, image/bmp, image/webp"
                                            prepend-icon="tabler-camera"
                                            :rules="[requiredValidator]"
                                            @change="onImageSelected($event, 1)"
                                            @click:clear="avatars[1] = avatarsOld[1]"
                                        />
                                    </VCol>
                                    <!-- 👉 Banner Category 2 --> 
                                    <VCol cols="12" class="d-flex justify-center align-center">
                                        <VImg
                                            :class="((filename2.length === 0 && isValid === false)) ? 'border-error' : ''"
                                            v-if="avatars[1] !== null"
                                            :src="avatars[1]"
                                            :height="300"
                                            aspect-ratio="1/1"
                                            class="border-img"
                                            cover
                                        />
                                    </VCol>
                                </VRow>
                            </VCardText>

                            <VDivider />

                            <VCardText>
                                <VRow no-gutters>
                                    <VCol cols="12"  md="2"></VCol>
                                    <VCol cols="12"  md="5">
                                        <VFileInput
                                            v-model="filename3"
                                            label="Banner 3"
                                            class="mb-2 me-2"
                                            accept="image/png, image/jpeg, image/bmp, image/webp"
                                            prepend-icon="tabler-camera"
                                            :rules="[requiredValidator]"
                                            @change="onImageSelected($event, 2)"
                                            @click:clear="avatars[2] = avatarsOld[2]"
                                        />
                                    </VCol>
                                    <!-- 👉 Banner Category 3 -->
                                    <VCol cols="12" class="d-flex justify-center align-center">
                                        <VImg
                                            :class="((filename3.length === 0 && isValid === false)) ? 'border-error' : ''"
                                            v-if="avatars[2] !== null"
                                            :src="avatars[2]"
                                            :height="300"
                                            aspect-ratio="1/1"
                                            class="border-img"
                                            cover
                                        />
                                    </VCol>
                                </VRow>
                            </VCardText>

                            <VDivider />

                            <VCardText>
                                <VRow no-gutters>
                                    <VCol cols="12"  md="2"></VCol>
                                    <VCol cols="12"  md="5">
                                        <VFileInput
                                            v-model="filename4"
                                            label="Banner 4"
                                            class="mb-2 me-2"
                                            accept="image/png, image/jpeg, image/bmp, image/webp"
                                            prepend-icon="tabler-camera"
                                            :rules="[requiredValidator]"
                                            @change="onImageSelected($event, 3)"
                                            @click:clear="avatars[3] = avatarsOld[3]"
                                        />
                                    </VCol>
                                    <!-- 👉 Banner Category 4 -->
                                    <VCol cols="12" class="d-flex justify-center align-center">
                                        <VImg
                                            :class="((filename4.length === 0 && isValid === false)) ? 'border-error' : ''"
                                            v-if="avatars[3] !== null"
                                            :src="avatars[3]"
                                            :height="300"
                                            aspect-ratio="1/1"
                                            class="border-img"
                                            cover
                                        />
                                    </VCol>
                                </VRow>
                            </VCardText>
                        </div>
                </VCol>

                <VCol cols="12" md="3">
                    <VCard class="mb-8">
                        <VCardText>
                            <!-- 👉 Send Banner -->
                            <VBtn
                                block
                                prepend-icon="tabler-plus"
                                class="mb-2"
                                type="submit">
                                Agregar
                            </VBtn>

                            <!-- 👉 Preview -->
                            <VBtn
                                block
                                color="default"
                                variant="tonal"
                                class="mb-2"
                                :to="{ name: 'dashboard-admin-banners' }">
                                Regresar
                            </VBtn>
                        </VCardText>
                    </VCard>  
                </VCol>
            </VRow>
        </VForm>
    </section>
</template>

<route lang="yaml">
    meta:
        action: crear
        subject: banners
</route>

<style>
    .p-0 {
        padding: 0;
    }

    .p-1 {
        padding: 1rem;
    }

    .button-icon {
        height: 60px !important; 
        border-radius: 8px !important;
        margin: 2px;
    }

    .button-color {
        height: 40px !important; 
        border-radius: 8px !important;
        margin: 1px !important;
        font-size: 10px !important;
        padding: 5px !important;
    } 

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
<route lang="yaml">
    meta:
      action: crear
      subject: banners
</route>