<script setup>

import router from '@/router'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { useCategoriesStores } from '@/stores/useCategories'

const categoriesStores = useCategoriesStores()

const isRequestOngoing = ref(true)

const categories = ref([])
const refForm = ref()
const isFormValid = ref(false)
const error = ref(undefined)

const emitter = inject("emitter")

const name = ref('')
const category_id = ref()
const banner_category_id = ref()
const banner2_category_id = ref()
const banner3_category_id = ref()
const banner4_category_id = ref()
const banners = ref([])
const avatars = ref([])
const filename = ref([])
const filename2 = ref([])
const filename3 = ref([])
const filename4 = ref([])

const isValid =  ref(null)

watchEffect(fetchData)

async function fetchData() {

    isRequestOngoing.value = true

    let data = { limit: -1 }

    await categoriesStores.fetchCategoriesOrder(data)

    categories.value = categoriesStores.getCategories

    isRequestOngoing.value = false
}

const onImageSelected = (event, id) => {
  const file = event.target.files[0]

  if (!file) return
  // banners.value = file

  URL.createObjectURL(file)

  resizeImage(file, 400, 400, 0.9)
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

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
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
            formData.append('is_category', (typeof category_id.value === 'undefined' || category_id.value === null) ? 0 : 1)
            formData.append('category_id', category_id.value)
            // formData.append('banner_category_id', banner_category_id.value)
            // formData.append('banner2_category_id', banner2_category_id.value)
            // formData.append('banner3_category_id', banner3_category_id.value)
            // formData.append('banner4_category_id', banner4_category_id.value)

            categoriesStores.addCategory(formData)
                .then((res) => {
                if (res.data.success) {

                    let data = {
                        message: 'Categoría Creada!',
                        error: false
                    }

                    router.push({ name : 'dashboard-products-categories'})
                    emitter.emit('toast', data)

                } else {

                    let data = {
                        message: 'ERROR',
                        error: true
                    }

                    router.push({ name : 'dashboard-products-categories'})
                    emitter.emit('toast', data)
                }
                })
                .catch((err) => {
                    let data = {
                        message: err,
                        error: true
                    }

                    router.push({ name : 'dashboard-products-categories'})
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
                                <VCol cols="12" md="6">
                                    <VTextField
                                    v-model="name"
                                    :rules="[requiredValidator]"
                                    label="Nombre"
                                    />
                                </VCol>
                                <VCol cols="12"  md="6">
                                    <VAutocomplete
                                        id="selectCategory"
                                        v-model="category_id"
                                        label="Categoría"
                                        :items="categories"
                                        :item-title="item => item.name"
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
                            </VRow>
                        </VCardText>
                        <!-- <VDivider />
                        <VCardText>
                            <VRow no-gutters>
                                <VCol cols="12"  md="6"></VCol>
                                <VCol cols="12"  md="3">
                                    <VFileInput
                                        v-model="filename"
                                        label="Banner Principal"
                                        class="mb-2 me-2"
                                        accept="image/png, image/jpeg, image/bmp"
                                        prepend-icon="tabler-camera"
                                        @change="onImageSelected($event, 0)"
                                        @click:clear="avatars[0] = null"
                                        :rules="[requiredValidator]"
                                    />
                                </VCol>

                                <VCol cols="12"  md="3">
                                    <VAutocomplete
                                        id="selectBanner1Category"
                                        v-model="banner_category_id"
                                        label="Categoría"
                                        :items="categories"
                                        :item-title="item => item.name"
                                        :item-value="item => item.id"
                                        autocomplete="off"
                                        :menu-props="{ maxHeight: '300px' }">
                                        <template v-slot:item="{ props, item }">
                                            <v-list-item
                                                v-bind="props"
                                                :title="item?.raw?.name"
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
                                <VCol cols="12"  md="6"></VCol>
                                <VCol cols="12"  md="3">
                                    <VFileInput
                                        v-model="filename2"
                                        label="Banner 2"
                                        class="mb-2 me-2"
                                        accept="image/png, image/jpeg, image/bmp"
                                        prepend-icon="tabler-camera"
                                        @change="onImageSelected($event, 1)"
                                        @click:clear="avatars[1] = null"
                                        :rules="[requiredValidator]"
                                    />
                                </VCol>
                   
                                <VCol cols="12" md="3">
                                    <VAutocomplete
                                        id="selectBanner2Category"
                                        v-model="banner2_category_id"
                                        label="Categoría"
                                        :items="categories"
                                        :item-title="item => item.name"
                                        :item-value="item => item.id"
                                        autocomplete="off"
                                        :menu-props="{ maxHeight: '300px' }">
                                        <template v-slot:item="{ props, item }">
                                            <v-list-item
                                                v-bind="props"
                                                :title="item?.raw?.name"
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
                                    </VAutocomplete>
                                </VCol>
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
                                <VCol cols="12"  md="6"></VCol>
                                <VCol cols="12"  md="3">
                                    <VFileInput
                                        v-model="filename3"
                                        label="Banner 3"
                                        class="mb-2 me-2"
                                        accept="image/png, image/jpeg, image/bmp"
                                        prepend-icon="tabler-camera"
                                        @change="onImageSelected($event, 2)"
                                        @click:clear="avatars[2] = null"
                                        :rules="[requiredValidator]"
                                    />
                                </VCol>
                       
                                <VCol cols="12" md="3">
                                    <VAutocomplete
                                        id="selectBanner3Category"
                                        v-model="banner3_category_id"
                                        label="Categoría"
                                        :items="categories"
                                        :item-title="item => item.name"
                                        :item-value="item => item.id"
                                        autocomplete="off"
                                        :menu-props="{ maxHeight: '300px' }">
                                        <template v-slot:item="{ props, item }">
                                            <v-list-item
                                                v-bind="props"
                                                :title="item?.raw?.name"
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
                                    </VAutocomplete>
                                </VCol>
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
                                <VCol cols="12"  md="6"></VCol>
                                <VCol cols="12"  md="3">
                                    <VFileInput
                                        v-model="filename4"
                                        label="Banner 4"
                                        class="mb-2 me-2"
                                        accept="image/png, image/jpeg, image/bmp"
                                        prepend-icon="tabler-camera"
                                        @change="onImageSelected($event, 3)"
                                        @click:clear="avatars[3] = null"
                                        :rules="[requiredValidator]"
                                    />
                                </VCol>
                              
                                <VCol cols="12" md="3">
                                    <VAutocomplete
                                        id="selectBanner4Category"
                                        v-model="banner4_category_id"
                                        label="Categoría"
                                        :items="categories"
                                        :item-title="item => item.name"
                                        :item-value="item => item.id"
                                        autocomplete="off"
                                        :menu-props="{ maxHeight: '300px' }">
                                        <template v-slot:item="{ props, item }">
                                            <v-list-item
                                                v-bind="props"
                                                :title="item?.raw?.name"
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
                                    </VAutocomplete>
                                </VCol>
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
                        </VCardText> -->
                    </VCard>
                </VCol>

                <VCol cols="12" md="3">
                    <VCard class="mb-8">
                        <VCardText>
                            <!-- 👉 Send Category -->
                            <VBtn
                                block
                                prepend-icon="tabler-plus"
                                class="mb-2"
                                type="submit">
                                Agregar
                            </VBtn>

                            <!-- 👉 Preview -->
                            <v-btn
                                block
                                color="default"
                                variant="tonal"
                                class="mb-2"
                                :to="{ name: 'dashboard-products-categories' }">
                                Regresar
                            </v-btn>
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
        subject: productos
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
