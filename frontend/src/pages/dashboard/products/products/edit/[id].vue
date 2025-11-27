<script setup>

import { inject } from "vue"
import { themeConfig } from '@themeConfig'
import { requiredValidator } from '@validators'
import { useProductsStores } from '@/stores/useProducts'
import { useColorsStores } from '@/stores/useColors'
import { useCategoriesStores } from '@/stores/useCategories'
import { useBrandsStores } from '@/stores/useBrands'
import { useTagsStores } from '@/stores/useTags'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { QuillEditor } from '@vueup/vue-quill'
import { Player, DefaultUi, Youtube, Vimeo, Video} from '@vime/vue-next';
import { Cropper } from 'vue-advanced-cropper'
import ImageUploader from 'quill-image-uploader'
import FileInput from "@/components/common/FileInput.vue";
import router from '@/router'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import 'vue-advanced-cropper/dist/style.css'

const productsStores = useProductsStores()
const colorsStores = useColorsStores()
const categoriesStores = useCategoriesStores()
const brandsStores = useBrandsStores() 
const tagsStores = useTagsStores() 
const suppliersStores = useSuppliersStores()
const route = useRoute()

const emitter = inject("emitter")
const isRequestOngoing = ref(true)

const optionCounter = ref(null)
const videoCounter = ref(1)

const categories = ref([])
const listColors = ref([])
const listBrands = ref([])
const listTags = ref([])
const listSuppliers = ref([])

const isValid =  ref(null)
const isFormValid = ref(false)
const refForm = ref()
const rol = ref(null)
const userData = ref(null)

const product = ref(null) 
const tag_id = ref()
const color_id = ref([])
const category_id = ref([])
const sku = ref([])
const video = ref([])
const product_files = ref([])
const brand_id = ref()
const user_id = ref(null)
const name = ref(null)
const single_description = ref(' ')
const description = ref(' ')
const price = ref('')
const price_for_sale = ref('')
const wholesale_price = ref('')
const wholesale_min = ref('')
const wholesale = ref(false)
const stock = ref([])
const cropper = ref()
const imageOld = ref('')
const imageCropped = ref(null)
const image = ref('')
const filename = ref([])
const width = ref([])
const height = ref([])
const deep = ref([])
const weigth = ref([])
const material = ref([])
const estimated_delivery_time = ref([])

const isConfirmChangeImageVisible = ref(false)

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


watchEffect(fetchData)

async function fetchData() {

    isRequestOngoing.value = true

    if(Number(route.params.id)) {

      let data = { 
        tag_type_id: 1,
        category_type_id: 1,
        limit: -1 
      }

      await colorsStores.all();
      await categoriesStores.fetchCategoriesOrder(data)
      await brandsStores.fetchBrands(data)
      await tagsStores.fetchTags(data)

      listColors.value = colorsStores.getColors
      categories.value = categoriesStores.getCategories
      listBrands.value = brandsStores.getBrands
      listTags.value = tagsStores.getTags

      userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
      rol.value = userData.value.roles[0].name

      if(rol.value !== 'Proveedor') {
        await suppliersStores.fetchSuppliers(data)
        listSuppliers.value = suppliersStores.getSuppliers
      }

      product.value = await productsStores.showProduct(Number(route.params.id))
      tag_id.value = product.value.tags.map(color => color.tag_id)
      color_id.value = product.value.colors.map(color => color.color_id)
      product.value.colors.forEach(function callback(value, index) { 
          category_id.value.push(value.categories.map(category => category.category_id))
      });
      sku.value = product.value.colors.map(color => color.sku)
      stock.value = product.value.colors.map(color => color.stock)
      video.value = product.value.videos.map(video => video.url)

      product.value.colors.forEach(async function callback(value, index) { 
          let files = []
          for(var i = 0; i < value.images.length; i++) {

              const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + themeConfig.settings.urlStorage + value.images[i].image);
              const blob = await response.blob();
              const file = new File([blob], value.images[i].image.replaceAll('products/gallery/',''), { type: blob.type });
            
              URL.createObjectURL(file)

              const blog = await resizeImage(file, 1200, 1200, 1)

              files.push({
                  file,
                  url: themeConfig.settings.urlStorage + value.images[i].image,
                  blob: blob
              })

          }

          product_files.value[index] = files
      });

      brand_id.value = product.value.brand_id
      user_id.value = (product.value.user_id === userData.value.id) ? null : product.value.user_id 
      name.value = product.value.name
      single_description.value = product.value.single_description ?? ' '
      description.value = product.value.description
      price.value = product.value.price
      price_for_sale.value = product.value.price_for_sale
      wholesale_price.value = product.value.wholesale_price
      wholesale_min.value = product.value.wholesale_min
      wholesale.value = product.value.wholesale === 1 ? true : false

      image.value = product.value.image === null ? '' : themeConfig.settings.urlStorage + product.value.image

      width.value = product.value.detail.width
      height.value = product.value.detail.height
      deep.value = product.value.detail.deep
      weigth.value = product.value.detail.weigth
      material.value = product.value.detail.material
      estimated_delivery_time.value = product.value.estimated_delivery_time
      
      optionCounter.value = product.value.colors.length
      videoCounter.value = product.value.videos.length === 0 ? 1 : product.value.videos.length
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

  resizeImage(file, 1200, 1200, 1)
    .then(async blob => {
        let r = await blobToBase64(blob)
        imageCropped.value = 'data:image/jpeg;base64,' + r
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
  document.getElementById("selectCategory_"+i).blur()
}

const getFiles = (files, i) => {
  product_files.value[i-1] = files
}

const removeColor = id => {
  if(optionCounter.value > 1) {
    optionCounter.value--
    category_id.value.splice(id, 1)
    color_id.value.splice(id, 1)
    sku.value.splice(id, 1)
    product_files.value.splice(id, 1)
  }
}

const removeVideo = id => {
  if(videoCounter.value > 1) {
    videoCounter.value--
    video.value.splice(id, 1)
  }
}

const providers = computed(() =>
  video.value.map((url) => {
    if (url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/)) {
      return 'youtube';
    }
    if (url.match(/vimeo\.com\/(\d+)/)) {
      return 'vimeo';
    }
    if (/\.(mp4|webm|ogg)(\?.*)?$/i.test(url)) {
      return 'file';
    }
    return null;
  })
);

const mediaIds = computed(() =>
  video.value.map((url) => {
    const yt = url.match(
      /(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/
    );
    if (yt) return yt[1];
    const vm = url.match(/vimeo\.com\/(\d+)/);
    if (vm) return vm[1];
    return '';
  })
);

const dataURLtoBlob = (dataURL) => {
  const [header, base64] = dataURL.split(',');
  const mimeMatch = header.match(/:(.*?);/);
  const mime = mimeMatch ? mimeMatch[1] : 'image/png'; 
  const binary = atob(base64);
  const len = binary.length;
  const u8arr = new Uint8Array(len);
  for (let i = 0; i < len; i++) {
    u8arr[i] = binary.charCodeAt(i);
  }
  return new Blob([u8arr], { type: mime });
}

const onCropChange = (coordinates) => {
  // console.log('coordinates', coordinates)
}

const resetImage = () => {
  imageCropped.value = null
  imageOld.value = null
}

const cropImage = async () => {
    if (cropper.value) {
        const result = cropper.value.getResult({
            mime: 'image/png',
            quality: 1,
            fillColor: 'transparent'
        });
        const blob = dataURLtoBlob(result.canvas.toDataURL("image/png"));

        imageOld.value = blob 
        image.value = result.canvas.toDataURL("image/png")
        isConfirmChangeImageVisible.value = false     
    }
}

const onSubmit = () => {

    refForm.value?.validate().then(({ valid }) => {
        isValid.value = valid
        if (valid) {     

            let formData = new FormData()

            //product
            formData.append('user_id', user_id.value ?? 0)
            formData.append('brand_id', brand_id.value)
            formData.append('name', name.value)
            formData.append('single_description', (single_description.value === ' ') ? null : single_description.value)
            formData.append('description', description.value)
            formData.append('price', price.value)
            formData.append('price_for_sale', price_for_sale.value)
            formData.append('wholesale', wholesale.value ? 1 : 0)
            formData.append('wholesale_price', wholesale_price.value)
            formData.append('wholesale_min', wholesale_min.value)
            formData.append('image', imageOld.value)

            //product_details
            formData.append('width', width.value)
            formData.append('height', height.value)
            formData.append('deep', deep.value)
            formData.append('weigth', weigth.value)
            formData.append('material', material.value)

            //product_tags
            formData.append('tag_id', tag_id.value)

            //product_categories
            formData.append('category_id', JSON.stringify(category_id.value))

            //product_images
            formData.append('color_id', color_id.value)
            formData.append('sku', sku.value)
            formData.append('product_files', product_files.value)
            formData.append('stock', stock.value)

            //product_videos
            formData.append('video', video.value)

            product_files.value.forEach(function callback(value, index) {
                value.forEach(function callback(image, i) {
                    formData.append('images_'+index+'[]', image.blob)
                });
            });

            formData.append('_method', 'PUT')

            let data = {
                data: formData, 
                id: Number(route.params.id)
            }

            productsStores.updateProduct(data)
                .then((res) => {
                if (res.data.success) {

                    let data = {
                        message: 'Producto actualizado!',
                        error: false
                    }

                    router.push({ name : 'dashboard-products-products'})
                    emitter.emit('toast', data)

                } else {

                    let data = {
                        message: 'ERROR',
                        error: true
                    }

                    router.push({ name : 'dashboard-products-products'})
                    emitter.emit('toast', data)
                }
                })
                .catch((err) => {
                    let data = {
                        message: err,
                        error: true
                    }

                    router.push({ name : 'dashboard-products-products'})
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
            Editar producto 😃🌟
          </h4>
          <span>Recarga tu fiesta de productos 🎉</span>
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
            prepend-icon="tabler-pencil"
            class="mb-2"
          type="submit">
          Actualizar
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

          <VCard
            title="Videos"
            class="mb-6"
          >
            <VCardText>
              <template
                v-for="i in videoCounter"
                :key="i"
              >
                <VRow class="mb-2">                  
                  <VCol
                    cols="12"
                    md="7"
                  >
                    <AppTextField
                      v-model="video[i-1]"
                      placeholder="ENLACE"
                    />
                  </VCol>  
                  
                  <VCol
                    cols="12"
                    md="4"
                    v-if="providers[i-1]"
                  >
                    <Player style="width: 100%;">
                      <Youtube
                        v-if="providers[i-1] === 'youtube'"
                        :video-id="mediaIds[i-1]"
                      />
                      <Vimeo
                        v-else-if="providers[i-1] === 'vimeo'"
                        :video-id="mediaIds[i-1]"
                      />
                      <Video
                        v-else-if="providers[i-1] === 'file'"
                      >
                        <source
                          :src="video[i-1]"
                          type="video/mp4"
                        />
                      </Video>
                      <DefaultUi />
                    </Player>
                  </VCol>
                  <VCol
                    cols="12"
                    md="1"
                    v-if="videoCounter > 1"
                  >
                    <!-- 👉 Item Actions -->
                    <div class="d-flex">
                      <VSpacer />
                      <VBtn
                        icon="tabler-x"
                        variant="tonal"
                        color="primary"
                        size="x-small"
                        @click="removeVideo(i-1)"
                      />
                    </div>
                  </VCol>
                </VRow>
              </template>

              <VBtn
                class="mt-2"
                v-if="video[video.length-1]"
                @click="videoCounter++"
              >
                Agregar enlace
              </VBtn>
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
                <VRow class="border-img mb-7">  
                  <VCol
                    cols="12"
                    md="12"
                    v-if="optionCounter > 1"
                  >
                    <!-- 👉 Item Actions -->
                    <div class="d-flex">
                      <VSpacer />
                      <VBtn
                        icon="tabler-x"
                        variant="tonal"
                        color="primary"
                        size="x-small"
                        @click="removeColor(i-1)"
                      />
                    </div>
                  </VCol>
                  <VCol
                    cols="12"
                    md="4"
                  >
                    <VSelect
                      v-model="color_id[i-1]"
                      label="Colores"
                      :items="listColors"
                      item-value="id"
                      item-title="name"
                      clearable
                      clear-icon="tabler-x"
                      no-data-text="No disponible"
                      :rules="[requiredValidator]"
                    />
                  </VCol>
                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppTextField
                      v-model="sku[i-1]"
                      placeholder="SKU"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppTextField
                      v-model="stock[i-1]"
                      type="number"
                      placeholder="Stock"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="12"
                  >
                    <VAutocomplete
                      :id="'selectCategory_' + i"
                      v-model="category_id[i-1]"
                      label="Categorías:"
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
                          (+{{ category_id[i-1].length - 2 }} otros)
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
                              @click="closeDropdown(i)"/>
                          </template>
                        </v-list-item>
                      </template>
                    </VAutocomplete>
                  </VCol>
                  <VCol cols="12" v-if="product_files.length > 0">
                    <FileInput 
                      :images="product_files[i-1]"
                      @files="getFiles($event, i)"/>
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
            title="Imagen Principal"
            class="mb-6"
          >
            <VCardText class="text-center">
              <VBadge 
                  @click="isConfirmChangeImageVisible = true"
                  class="cursor-pointer"
                  color="success">
                  <template #badge>
                      <VIcon icon="tabler-pencil" />
                  </template>
                  <VImg :src="image" class="store-img" cover/>
              </VBadge>
            </VCardText>
          </VCard>

          <VCard
            title="Precios"
            class="mb-6"
          >
            <VCardText>
              <AppTextField
                v-model="price"
                prefix="$"
                type="number"
                label="Costo"
                class="mb-6"
                :rules="[requiredValidator]"
              />

              <AppTextField
                v-model="price_for_sale"
                prefix="$"
                type="number"
                label="Precio al detal"
                class="mb-4"
                :rules="[requiredValidator]"
              />

              <label for="wholesale">¿Producto disponible al por mayor?</label>
              <VCheckbox
                v-model="wholesale"
              > 
                <template #label>
                  {{ wholesale ? 'SI' : 'NO' }}
                </template>
              </VCheckbox>

              <div class="d-flex">
                <AppTextField
                  v-model="wholesale_price"
                  v-if="wholesale === 1"
                  prefix="$"
                  type="number"
                  label="Precio por mayor"
                  class="mb-4 me-3"
                />

                <AppTextField
                  v-model="wholesale_min"
                  v-if="wholesale === 1"
                  type="number"
                  label="Cantidad mínima"
                  class="mb-4"
                  :min="1"
                />
              </div>

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
                  v-model="tag_id"
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
                
                <!-- <AppTextField
                  readonly
                  v-model="estimated_delivery_time"
                  label="Tiempo de entregada"
                  placeholder="Tiempo estimado de entrega"
                /> -->
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VForm>

    <!-- 👉 Confirm change image -->
    <VDialog
      v-model="isConfirmChangeImageVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmChangeImageVisible = !isConfirmChangeImageVisible" />

      <!-- Dialog Content -->
       <VForm
          ref="refVForm"
          @submit.prevent="cropImage"
        >
        <VCard title="Cambiar imagen">
          <VDivider />
          <VCardText>
            La imagen que selecciones y recortes aparecerá como imagen principal.
          </VCardText>
          <VCardText class="d-flex flex-column gap-2">
              <VRow>
                  <VCol cols="12" md="12">
                      <Cropper
                          v-if="imageCropped"
                          ref="cropper"
                          class="cropper-container"
                          preview-class="cropper-preview"
                          background-class="cropper-background"
                          :src="imageCropped"
                          :stencil-props="{
                              aspectRatio: 1/1,
                              previewClass: 'cropper-preview-circle'
                          }"
                          @change="onCropChange"
                      />

                  </VCol>
                  <VCol cols="12" md="12">
                      <VFileInput 
                          v-model="filename"
                          label="Imagen"
                          class="mb-2"
                          accept="image/png, image/jpeg, image/bmp, image/webp"
                          prepend-icon="tabler-camera"
                          @change="onImageSelected"
                          :rules="[requiredValidator]"
                          @click:clear="resetImage"
                      />
                  </VCol>
              </VRow>
          </VCardText>

          <VCardText class="d-flex justify-end gap-3 flex-wrap">
            <VBtn
              color="secondary"
              variant="tonal"
              @click="isConfirmChangeImageVisible = false">
                Cancelar
            </VBtn>
            <VBtn type="submit"> 
                Guardar
            </VBtn>
          </VCardText>
        </VCard>
      </VForm>
    </VDialog>

  </section>
</template>

<style lang="scss">

  .store-img {
    width: 300px;
    height: 300px;
    max-width: 300px;
    border-radius: 8px;
    background-color: #F5F5F5;
  }

  ::v-deep .vue-simple-handler {
    background: #9966FF !important;
  }
    
  ::v-deep .cropper-preview-circle {
    border: dashed 1px #9966FF
  }

  ::v-deep .cropper-background,
  ::v-deep .vue-advanced-cropper__foreground {
    background-color: transparent !important;
  }

  .cropper-container {
    width: 100%;
    height: 250px;
    background-color: #f5f5f5;
    border-radius: 8px;
    overflow: hidden;
  }

  .cropper-preview {
    width: 250px;
    height: 250px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 1rem;
  }

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
    subject: productos
</route>
