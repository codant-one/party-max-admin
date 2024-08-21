<script setup>

import { inject } from "vue"
import { themeConfig } from '@themeConfig'
import { requiredValidator } from '@validators'
import { useServicesStores } from '@/stores/useServices'
import { useCategoriesStores } from '@/stores/useCategories'
import { useBrandsStores } from '@/stores/useBrands'
import { useTagsStores } from '@/stores/useTags'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { QuillEditor } from '@vueup/vue-quill'
import ImageUploader from 'quill-image-uploader'
import FileInput from "@/components/common/FileInput.vue";
import router from '@/router'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const servicesStores = useServicesStores()
const categoriesStores = useCategoriesStores()
const brandsStores = useBrandsStores() 
const tagsStores = useTagsStores() 
const suppliersStores = useSuppliersStores()
const route = useRoute()

const emitter = inject("emitter")
const isRequestOngoing = ref(true)

const categories = ref([])
const listBrands = ref([])
const listTags = ref([])
const listSuppliers = ref([])

const isValid =  ref(null)
const isFormValid = ref(false)
const refForm = ref()
const rol = ref(null)
const userData = ref(null)

const service = ref(null) 
const tag_id = ref()
const category_id = ref([])
const sku = ref([])
const service_files = ref([])
const brand_id = ref()
const user_id = ref(null)
const name = ref(null)
const single_description = ref(' ')
const description = ref(' ')
const price = ref('')
const image = ref('')
const avatar = ref('')
const filename = ref([])

const modules = {
  name: 'imageUploader',
  module: ImageUploader,
  options: {
    upload: file => {
    
      return new Promise((resolve, reject) => {
        
        const formData = new FormData()
              
        formData.append("image", file)

        axios.post('/services/upload-image', formData)
          .then(res => {
            resolve(themeConfig.settings.urlStorage + res.data.url)
          })
          .catch(err => {
            reject("Upload failed")
            console.error("Error:", err)
          })
      })
    },
  },
}

watchEffect(fetchData)

async function fetchData() {

    isRequestOngoing.value = true

    if(Number(route.params.id)) {

      let data = { 
        tag_type_id: 3,
        category_type_id: 2,
        brand_type_id: 2,
        limit: -1 
      }

      await categoriesStores.fetchCategoriesOrder(data)
      await brandsStores.fetchBrands(data)
      await tagsStores.fetchTags(data)

      categories.value = categoriesStores.getCategories
      listBrands.value = brandsStores.getBrands
      listTags.value = tagsStores.getTags

      userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
      rol.value = userData.value.roles[0].name

      if(rol.value !== 'Proveedor') {
        await suppliersStores.fetchSuppliers(data)
        listSuppliers.value = suppliersStores.getSuppliers
      }

      service.value = await servicesStores.showService(Number(route.params.id))
      tag_id.value = service.value.tags.map(item => item.tag_id)

      sku.value = service.value.sku
      category_id.value = service.value.categories.map(item => item.category_id)
    
      service.value.images.forEach(async function callback(value, index) { 
        const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + themeConfig.settings.urlStorage + value.image);
        const blob = await response.blob();
        const file = new File([blob], value.image.replaceAll('services/gallery/',''), { type: blob.type });
            
        URL.createObjectURL(file)

        const blog = await resizeImage(file, 400, 400, 0.9)

        service_files.value.push({
          file,
          url: themeConfig.settings.urlStorage + value.image,
          blob: blob
        })
      });

      brand_id.value = service.value.brand_id
      user_id.value = (service.value.user_id === userData.value.id) ? null : service.value.user_id 
      name.value = service.value.name
      single_description.value = service.value.single_description ?? ' '
      description.value = service.value.description
      price.value = service.value.price

      avatar.value = service.value.image === null ? '' : themeConfig.settings.urlStorage + service.value.image

    }
    
    isRequestOngoing.value = false
}

const getSuppliers = computed(() => {
  return listSuppliers.value.map((supplier) => {
    return {
      title: supplier.company_name,
      value: supplier.user.id,
    }
  })
})

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

const closeDropdown = (i) => { 
  document.getElementById("selectCategory").blur()
}

const onSubmit = () => {

    refForm.value?.validate().then(({ valid }) => {
        isValid.value = valid
        if (valid) {

            let formData = new FormData()

            //service
            formData.append('user_id', user_id.value ?? 0)
            formData.append('brand_id', brand_id.value)
            formData.append('name', name.value)
            formData.append('single_description', (single_description.value === ' ') ? null : single_description.value)
            formData.append('description', description.value)
            formData.append('sku', sku.value)
            formData.append('price', price.value)
            formData.append('image', image.value)

            //service_tags
            formData.append('tag_id', tag_id.value)

            //service_categories
            formData.append('category_id', category_id.value)

            //service_images
            service_files.value.forEach(function callback(image, index) {
              formData.append('images[]', image.blob)
            });

            formData.append('_method', 'PUT')

            let data = {
                data: formData, 
                id: Number(route.params.id)
            }

            servicesStores.updateService(data)
                .then((res) => {
                if (res.data.success) {

                    let data = {
                        message: 'Servicio actualizado!',
                        error: false
                    }

                    router.push({ name : 'dashboard-services-services'})
                    emitter.emit('toast', data)

                } else {

                    let data = {
                        message: 'ERROR',
                        error: true
                    }

                    router.push({ name : 'dashboard-services-services'})
                    emitter.emit('toast', data)
                }
                })
                .catch((err) => {
                    let data = {
                        message: err,
                        error: true
                    }

                    router.push({ name : 'dashboard-services-services'})
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
    <VForm
      ref="refForm"
      v-model="isFormValid"
      @submit.prevent="onSubmit">

      <div class="d-flex mt-5 flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
        <div class="d-flex flex-column justify-center">
          <h4 class="text-h4 font-weight-medium">
            Editar Servicio 😃🌟
          </h4>
          <span>Recarga tu fiesta de servicios 🎉</span>
        </div>

        <div class="d-flex gap-4 align-center flex-wrap">
          <VBtn
              color="default"
              variant="tonal"
              class="mb-2"
              :to="{ name: 'dashboard-services-services' }">
              Regresar
          </VBtn>
          <VBtn
            prepend-icon="tabler-pencil"
            class="mb-2"
            type="submit">
            Actualizar
          </VBtn>
        </div>
      </div>

      <VRow>
        <VCol md="8">
          <VCard class="mb-6" title="Información del Servicio">
            <VCardText>
              <VRow>
                <VCol :cols="rol === 'Proveedor' ? 12 : 8">
                  <AppTextField
                    v-model="name"
                    label="Nombre"
                    placeholder="Nombre"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="4" v-if="rol !== 'Proveedor'">
                  <VAutocomplete
                    class="mt-6 mb-1"
                    v-model="user_id"
                    :items="getSuppliers"
                    label="Proveedores"
                    clearable
                  />
                </VCol>
                <VCol cols="12">
                  <span class="mb-1">Descripción (simple)</span>
                  <TiptapEditor
                    placeholder=" "
                    v-model="single_description"
                    class="border rounded"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="12">
                  <span class="mb-1">Descripción (detallada)</span>
                  <QuillEditor
                      v-model:content="description" 
                      :modules="modules" 
                      content-type="html"
                      toolbar="full"
                      :rules="[requiredValidator]"
                    />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

          <FileInput 
            :images="service_files"
            @files="service_files" />
          
        </VCol>

        <VCol md="4" cols="12">
          <VCard title="Imagen Principal" class="mb-6">
            <VCardText>
              <VImg
                v-if="avatar !== null"
                :src="avatar"
                :height="200"
                aspect-ratio="16/9"
                class="border-img mb-2"
              />

              <VFileInput
                v-model="filename"
                label="Imagen"
                class="mb-2"
                accept="image/png, image/jpeg, image/bmp"
                prepend-icon="tabler-camera"
                @change="onImageSelected"
                @click:clear="avatar = null"
              />
            </VCardText>
          </VCard>

          <VCard title="Detalles" class="mb-6 pb-5">            
            <VCardText>
              <div class="d-flex flex-column gap-y-4">
                <AppSelect
                  v-model="brand_id"
                  :items="listBrands"
                  item-value="id"
                  item-title="name"
                  placeholder="Marca"
                  label="Marca"
                  :rules="[requiredValidator]"
                />

                <AppSelect
                  v-model="tag_id"
                  chips
                  multiple
                  closable-chips
                  :items="listTags"
                  item-value="id"
                  item-title="name"
                  label="Tags"
                  placeholder="Tags"
                  color="primary"
                  :rules="[requiredValidator]"
                />

                <div class="app-select flex-grow-1">
                  <VLabel
                    class="mb-1 text-body-2 text-high-emphasis"
                    text="Categorías"
                  />
                  <VAutocomplete
                    id="selectCategory"
                    v-model="category_id"
                    autocomplete="off"
                    multiple
                    :items="categories"
                    :item-title="item => item.name"
                    :item-value="item => item.id"
                    :rules="[requiredValidator]"
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
                          paddingLeft: `${(item?.raw?.level) * 20}px !important`,
                          paddingTop: `0 !important`,
                          height: `10px !important`
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
                            @click="closeDropdown()"/>
                        </template>
                      </v-list-item>
                    </template>
                  </VAutocomplete>
                </div>

                <AppTextField
                  v-model="price"
                  prefix="COP"
                  type="number"
                  label="Costo"
                  :rules="[requiredValidator]"
                />

                <AppTextField
                  v-model="sku"
                  label="SKU"
                  :rules="[requiredValidator]"
                />                
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VForm>
  </section>
</template>

<style lang="scss">
  .inventory-card{
    .v-radio-group,
    .v-checkbox {
      .v-selection-control {
        align-items: start !important;

        .v-selection-control__wrapper{
          margin-block-start: -0.375rem !important;
        }
      }

      .v-label.custom-input {
        border: none !important;
      }
    }

    .v-tabs.v-tabs-pill {
      .v-slide-group-item--active.v-tab--selected.text-primary {
        h6{
          color: #fff !important
        }
      }
    }

  }

  .ProseMirror{
    p{
      margin-block-end: 0;
    }

    padding: 0.5rem;
    outline: none;

    p.is-editor-empty:first-child::before {
      block-size: 0;
      color: #adb5bd;
      content: attr(data-placeholder);
      float: inline-start;
      pointer-events: none;
    }
  }

  .justify-content-end{
    justify-content: end !important;
  }
    .editor {
        min-block-size: 450px;
        padding-block-end: 100px;
    }

    .ql-container {
        block-size: 300px !important;
        overflow-y: auto !important;
    }

    .p-0 {
        padding: 0;
    }

    .ql-editor .ql-video {
        block-size: inherit !important;
        inline-size: 100% !important;
        padding-block: 0 !important;
        padding-inline: 15% !important;
    }

    .ql-snow .ql-tooltip {
        inset-inline-start: 25% !important;
    }

    .ql-snow .ql-tooltip input[type="text"] {
        inline-size: 300px !important;
    }

    .border-img {
        border: 1.8px solid rgba(var(--v-border-color), var(--v-border-opacity));
        border-radius: 6px;
    }

    .border-error {
        border: 1.8px solid rgb(var(--v-theme-error));
    }

    .border-img .v-img__img--contain {
        padding: 10px;
    }
</style>
<route lang="yaml">
  meta:
    action: editar
    subject: servicios
</route>
