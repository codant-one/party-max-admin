<script setup>

import { themeConfig } from '@themeConfig'
import { requiredValidator } from '@validators'
import { useDropZone, useFileDialog, useObjectUrl } from '@vueuse/core'
import { useColorsStores } from '@/stores/useColors'
import { useCategoriesStores } from '@/stores/useCategories'
import { useBrandsStores } from '@/stores/useBrands'
import { useTagsStores } from '@/stores/useTags'
import { QuillEditor } from '@vueup/vue-quill'
import ImageUploader from 'quill-image-uploader'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const { open, onChange } = useFileDialog({ accept: 'image/*' })

const colorsStores = useColorsStores()
const categoriesStores = useCategoriesStores()
const brandsStores = useBrandsStores() 
const tagsStores = useTagsStores() 

const optionCounter = ref(1)
const dropZoneRef = ref()
const fileData = ref([])

const categories = ref([])
const listColors = ref([])
const listBrands = ref([])
const listTags = ref([])

const isValid =  ref(null)
const isFormValid = ref(false)
const refForm = ref()

const category_id = ref()
const brand_id = ref()
const name = ref(null)
const single_description = ref('')
const description = ref('')
const sku = ref('')
const price = ref('')
const price_for_sale = ref('')
const wholesale_price = ref('')
const stock = ref('')
const image = ref('')
const avatar = ref('')
const filename = ref([])
const width = ref([])
const height = ref([])
const deep = ref([])
const weigth = ref([])
const material = ref([])
const estimated_delivery_time = ref([])

const modules = {
  name: 'imageUploader',
  module: ImageUploader,
  options: {
    upload: file => {
    
      return new Promise((resolve, reject) => {
        
        const formData = new FormData()
              
        formData.append("image", file)

        axios.post('/products/upload-image', formData)
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


function onDrop(DroppedFiles) {
  DroppedFiles?.forEach(file => {
    if (file.type.slice(0, 6) !== 'image/') {
      alert('Only image files are allowed')
      
      return
    }
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? '',
    })
  })
}

onChange(selectedFiles => {
  if (!selectedFiles)
    return
  for (const file of selectedFiles) {
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? '',
    })
  }
})

useDropZone(dropZoneRef, onDrop)

watchEffect(fetchData)
  async function fetchData() {

  let data = { limit: -1 }

  await colorsStores.fetchColors();
  await categoriesStores.fetchCategoriesOrder(data)
  await brandsStores.fetchBrands(data)
  await tagsStores.fetchTags(data)

  listColors.value = colorsStores.getColors
  categories.value = categoriesStores.getCategories
  listBrands.value = brandsStores.getBrands
  listTags.value = tagsStores.getTags

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

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
}

const onSubmit = () => {

refForm.value?.validate().then(({ valid }) => {
    isValid.value = valid
    // if (valid) {
    //     error.value = undefined

    //     let formData = new FormData()

    //     formData.append('name', name.value)
    //     formData.append('description', description.value)
    //     formData.append('sku', sku.value)
    //     formData.append('price', price.value)
    //     formData.append('price_for_sale', price_for_sale.value)
    //     formData.append('stock', stock.value)
    //     formData.append('image', image.value)
    //     formData.append('category_id', category_id.value)
    //     formData.append('width', width.value)
    //     formData.append('height', height.value)
    //     formData.append('deep', deep.value)
    //     formData.append('weigth', weigth.value)
    //     formData.append('colors', JSON.stringify(colors.value))
    //     formData.append('gallery[]', colors.value[0].gallery)

    //     colors.value.forEach(function callback(value, index) {
    //         colors.value[index].gallery.forEach(function callback(value, i) {
    //             formData.append('color_'+colors.value[index]['color_id']+'[]', colors.value[index].gallery[i])
    //         });
    //     });

    //     productsStores.addProduct(formData)
    //         .then((res) => {
    //         if (res.data.success) {

    //             let data = {
    //                 message: 'Producto Creado!',
    //                 error: false
    //             }

    //             router.push({ name : 'dashboard-products-products'})
    //             emitter.emit('toast', data)

    //         } else {

    //             let data = {
    //                 message: 'ERROR',
    //                 error: true
    //             }

    //             router.push({ name : 'dashboard-products-products'})
    //             emitter.emit('toast', data)
    //         }
    //         })
    //         .catch((err) => {
    //             let data = {
    //                 message: err,
    //                 error: true
    //             }

    //             router.push({ name : 'dashboard-products-products'})
    //             emitter.emit('toast', data)
    //         })

    // }
})
}
</script>

<template>
  <VForm
    ref="refForm"
    v-model="isFormValid"
    @submit.prevent="onSubmit">
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 font-weight-medium">
          Añadir un nuevo producto
        </h4>
        <span>Recarga tu fiesta de productos</span>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
       <VBtn
          color="default"
          variant="tonal"
          class="mb-2"
          :to="{ name: 'dashboard-products-products' }">
          Regresar
       </VBtn>
       <VBtn
          prepend-icon="tabler-plus"
          class="mb-2"
         type="submit">
          Agregar
       </VBtn>
      </div>
    </div>

    <VRow>
      <VCol md="8">
        <VCard
          class="mb-6"
          title="Información del Producto"
        >
          <VCardText>
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="name"
                  label="Nombre"
                  placeholder="Nombre"
                  :rules="[requiredValidator]"
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

              <VCol cols="12">
                <span class="mb-1">Imagen</span>
                <VImg
                  v-if="avatar !== null"
                  :src="avatar"
                  :height="200"
                  aspect-ratio="16/9"
                  class="border-img"
                  :class="((filename.length === 0 && isValid === false)) ? 'border-error' : ''"
                />
              </VCol>
              <VCol cols="12">
                <VFileInput
                  v-model="filename"
                  label="Imagen"
                  class="mb-2"
                  accept="image/png, image/jpeg, image/bmp"
                  prepend-icon="tabler-camera"
                  @change="onImageSelected"
                  @click:clear="avatar = null"
                  :rules="[requiredValidator]"
                 />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <VCard
          title="Colores"
          class="mb-6"
        >
          <VCardText>
            <template
              v-for="i in optionCounter"
              :key="i"
            >
              <VRow>
                
                <VCol
                  cols="12"
                  md="3"
                >
                <VSelect
                  label="Colores"
                  :items="listColors"
                  item-value="id"
                  item-title="name"
                  clearable
                  clear-icon="tabler-x"
                  no-data-text="No disponible"
                 />
                </VCol>
                <VCol
                  cols="12"
                  md="3"
                >
                  <AppTextField
                    placeholder="SKU"
                    type="number"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VAutocomplete
                    id="selectCategory"
                    v-model="category_id"
                    label="Categorías:"
                    autocomplete="off"
                    multiple
                    :items="categories"
                    :item-title="item => item.name"
                    :item-value="item => item.id"
                    :rules="[requiredValidator]"
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
                <VCol cols="12">
                  <VCard class="mb-6">
                    <VCardItem>
                      <template #title>
                        Galería
                      </template>
                    </VCardItem>

                    <VCardText>
                      <div class="flex">
                        <div class="w-full h-auto relative">
                          <div
                            ref="dropZoneRef"
                            class="cursor-pointer"
                            @click="() => open()"
                          >
                            <div
                              v-if="fileData.length === 0"
                              class="d-flex flex-column justify-center align-center gap-y-3 px-6 py-10 border-dashed drop-zone"
                            >
                              <VBtn
                                variant="tonal"
                                class="rounded-sm"
                              >
                                <VIcon icon="tabler-upload" />
                              </VBtn>
                              <div class="text-base text-high-emphasis font-weight-medium">
                                Arrastra y suelta tu imagen aquí.
                              </div>
                            </div>

                            <div
                              v-else
                              class="d-flex justify-center align-center gap-3 pa-8 border-dashed drop-zone flex-wrap"
                            >
                              <VRow class="match-height w-100">
                                <template
                                  v-for="(item, index) in fileData"
                                  :key="index"
                                >
                                  <VCol
                                    cols="12"
                                    sm="4"
                                  >
                                    <VCard
                                      :ripple="false"
                                      border
                                    >
                                      <VCardText
                                        class="d-flex flex-column"
                                        @click.stop
                                      >
                                        <VImg
                                          :src="item.url"
                                          width="200px"
                                          height="150px"
                                          class="w-100 mx-auto"
                                        />
                                        <div class="mt-2">
                                          <span class="clamp-text text-wrap">
                                            {{ item.file.name }}
                                          </span>
                                          <span>
                                            {{ item.file.size / 1000 }} KB
                                          </span>
                                        </div>
                                      </VCardText>
                                      <VSpacer />
                                      <VCardActions>
                                        <VBtn
                                          variant="outlined"
                                          block
                                          @click.stop="fileData.splice(index, 1)"
                                        >
                                          Remove File
                                        </VBtn>
                                      </VCardActions>
                                    </VCard>
                                  </VCol>
                                </template>
                              </VRow>
                            </div>
                          </div>
                        </div>
                      </div>
                    </VCardText>
                  </VCard>
                </VCol>
              </VRow>
            </template>

            <VBtn
              class="mt-6"
              @click="optionCounter++"
            >
             Agregar Color
            </VBtn>
          </VCardText>
        </VCard>
        
      </VCol>

      <VCol
        md="4"
        cols="12"
      >
        <VCard
          title="Precios"
          class="mb-6"
        >
          <VCardText>
            <AppTextField
              v-model="price"
              prefix="COP"
              type="number"
              label="Costo"
              class="mb-6"
              :rules="[requiredValidator]"
            />

            <AppTextField
              v-model="price_for_sale"
              prefix="COP"
              type="number"
              label="Precio al detal"
              class="mb-4"
              :rules="[requiredValidator]"
            />

            <AppTextField
              v-model="wholesale_price"
              prefix="COP"
              type="number"
              label="Precio por mayor"
              class="mb-4"
              :rules="[requiredValidator]"
            />

            <AppTextField
              v-model="stock"
              type="number"
              label="Stock"
              class="mb-4"
              :rules="[requiredValidator]"
            />

          </VCardText>
        </VCard>

        <VCard title="Detalles">
          <VCardText>
            <div class="d-flex flex-column gap-y-4">

              <AppSelect
                v-model="brand_id"
                :items="listBrands"
                item-value="id"
                item-title="name"
                placeholder="Marca"
                :rules="[requiredValidator]"
              />

              <AppSelect
                chips
                multiple
                closable-chips
                :items="listTags"
                item-value="id"
                item-title="name"
                placeholder="Tags"
                color="primary"
                :rules="[requiredValidator]"
              />

              <AppTextField
                v-model="height"
                label="Alto"
                type="number"
                suffix="Cm"
                :rules="[requiredValidator]"
              />

              <AppTextField
                v-model="width"
                label="Ancho"
                type="number"
                suffix="Cm"
                :rules="[requiredValidator]"
              />

              <AppTextField
                v-model="weigth"
                label="Peso"
                type="number"
                suffix="Cm"
                :rules="[requiredValidator]"
              />

              <AppTextField
                v-model="deep"
                label="Profundo"
                type="number"
                suffix="Cm"
                :rules="[requiredValidator]"
              />
              
              <AppTextField
                v-model="material"
                label="Material"
                :rules="[requiredValidator]"
              />
              
              <AppTextField
                v-model="estimated_delivery_time"
                label="Tiempo de entregada"
                placeholder="Tiempo estimado de entrega"
                :rules="[requiredValidator]"
              />
              
              
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VForm>
</template>

<style lang="scss" scoped>
  .drop-zone {
    border: 2px dashed rgba(var(--v-theme-on-surface), 0.12);
    border-radius: 6px;
  }
</style>

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
