<script setup>

import { inject } from "vue"
import { themeConfig } from '@themeConfig'
import { requiredValidator, nonNegativeValidator } from '@validators'
import { useServicesStores } from '@/stores/useServices'
import { useCategoriesStores } from '@/stores/useCategories'
import { useBrandsStores } from '@/stores/useBrands'
import { useTagsStores } from '@/stores/useTags'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { useMiscellaneousStores } from '@/stores/useMiscellaneous'
import { QuillEditor } from '@vueup/vue-quill'
import { Player, DefaultUi, Youtube, Vimeo, Video} from '@vime/vue-next';
import { Cropper } from 'vue-advanced-cropper'
import ImageUploader from 'quill-image-uploader'
import FileInput from "@/components/common/FileInput.vue";
import router from '@/router'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import 'vue-advanced-cropper/dist/style.css'

const servicesStores = useServicesStores()
const categoriesStores = useCategoriesStores()
const brandsStores = useBrandsStores() 
const tagsStores = useTagsStores() 
const suppliersStores = useSuppliersStores()
const miscellaneousStores = useMiscellaneousStores()
const route = useRoute()

const emitter = inject("emitter")
const isRequestOngoing = ref(true)

const optionCounter = ref(1)
const videoCounter = ref(1)
const listCakeTypes = ref([])
const listCakeSizes = ref([])
const listSizesByTypes = ref([])
const cake_type = ref([])
const cake_type_id = ref([])
const cake_size = ref([])
const cake_size_id = ref([])
const prices = ref([])
const is_simple = ref([])

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
const is_full = ref(true)
const sku = ref([])
const video = ref([])
const service_files = ref([])
const brand_id = ref()
const user_id = ref(null)
const name = ref(null)
const single_description = ref(' ')
const description = ref(' ')
const price = ref(null)
const cropper = ref()
const imageOld = ref('')
const imageCropped = ref(null)
const image = ref('')
const avatar = ref('')
const filename = ref([])
const isCupcake = ref(false)
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

const loadData = () => {
  listCakeTypes.value = miscellaneousStores.getData.cakeTypes
  listCakeSizes.value = miscellaneousStores.getData.cakeSizes
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
      await miscellaneousStores.fetchDataCupcake();
      loadData()

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
      video.value = service.value.videos.map(video => video.url)
      is_full.value = service.value.is_full === 1 ? true : false
      estimated_delivery_time.value = service.value.estimated_delivery_time

      category_id.value = service.value.categories.map(item => item.category_id)
      selectCategory(category_id.value)

      optionCounter.value = service.value.cupcakes.length
      videoCounter.value = service.value.videos.length === 0 ? 1 : service.value.videos.length

      service.value.cupcakes.forEach(async function callback(value, index) { 
        prices.value[index] = value.price
        is_simple.value[index] = value.is_simple.toString()
        cake_type.value[index] = value.cake_size.cake_type.name
        cake_type_id.value[index] = value.cake_size.cake_type_id
        selectCakeType(cake_type.value[index], index)
        cake_size.value[index] = value.cake_size.name
        cake_size_id.value[index] = value.cake_size.id
      })

      service.value.images.forEach(async function callback(value, index) { 
        const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + themeConfig.settings.urlStorage + value.image);
        const blob = await response.blob();
        const file = new File([blob], value.image.replaceAll('services/gallery/',''), { type: blob.type });
            
        URL.createObjectURL(file)

        const blog = await resizeImage(file, 1200, 1200, 1)

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

      image.value = service.value.image === null ? '' : themeConfig.settings.urlStorage + service.value.image

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
  document.getElementById("selectCategory").blur()
}

const selectCategory = category => {
  isCupcake.value = category.find(item => item === 176) ? true : false
}

const selectCakeType = (cakeType, i) => {
  if (cakeType) {
    let _cakeType = listCakeTypes.value.find(item => item.name === cakeType)
    cake_type.value[i] = _cakeType.name
    cake_type_id.value[i] = _cakeType.id
    cake_size.value[i] = ''
    cake_size_id.value[i] = ''

    listSizesByTypes.value = listCakeSizes.value.filter(item => item.cake_type_id === _cakeType.id)
  }
}

const selectCakeSize = (cakeSize, i) => {
  if (cakeSize) {
    let _cakeSize = getCakeSizes.value.find(item => item.value === cakeSize)
    cake_size_id.value[i] = _cakeSize.value
  }
}

const getCakeSizes = computed(() => {
  return listSizesByTypes.value.map((state) => {
    return {
      title: state.name,
      value: state.id,
    }
  })
})

const removeType = id => {
  if(optionCounter.value > 1) {
    optionCounter.value--
    cake_type.value.splice(id, 1)
    cake_type_id.value.splice(id, 1)
    cake_size.value.splice(id, 1)
    cake_size_id.value.splice(id, 1)
    is_simple.value.splice(id, 1)
    prices.value.splice(id, 1)
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

            //service
            formData.append('user_id', user_id.value ?? 0)
            formData.append('brand_id', brand_id.value)
            formData.append('name', name.value)
            formData.append('single_description', (single_description.value === ' ') ? null : single_description.value)
            formData.append('description', description.value)
            formData.append('sku', sku.value)
            formData.append('price', price.value)
            formData.append('image', imageOld.value)
            formData.append('is_full', is_full.value ? 1 : 0)
            formData.append('estimated_delivery_time', estimated_delivery_time.value)
            
            //service_tags
            formData.append('tag_id', tag_id.value)

            //service_categories
            formData.append('category_id', category_id.value)

            //service_images
            service_files.value.forEach(function callback(image, index) {
              formData.append('images[]', image.blob)
            });

            //service_videos
            formData.append('video', video.value)

            //cupcakes
            formData.append('isCupcake', isCupcake.value)
            formData.append('cake_size_id', cake_size_id.value)
            formData.append('is_simple', is_simple.value)
            formData.append('prices', prices.value)

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
            title="Tipos"
            class="mb-6"
            :style="{
              display: !isCupcake ? 'none' : 'block'
            }"
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
                        @click="removeType(i-1)"
                      />
                    </div>
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VAutocomplete
                      v-model="cake_type[i-1]"
                      label="Tipo de tortas"
                      :items="listCakeTypes"
                      item-title="name"
                      item-value="name"
                      autocomplete="off"
                      @update:model-value="selectCakeType(cake_type[i-1],i-1)"
                      clearable
                      :rules="!isCupcake ? [] : [requiredValidator]" />
                  </VCol>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VAutocomplete
                      v-model="cake_size[i-1]"
                      label="Tamaño de tortas"
                      :items="getCakeSizes"
                      autocomplete="off"
                      clearable
                      @update:model-value="selectCakeSize(cake_size[i-1],i-1)"
                      :rules="!isCupcake ? [] : [requiredValidator]" />
                  </VCol>
                  <VCol 
                    cols="12"
                    md="6">
                    <VRadioGroup
                      v-model="is_simple[i-1]"
                      inline
                      :rules="!isCupcake ? [] : [requiredValidator]"
                    >
                      <div>
                        <VRadio
                          key="1"
                          label="Diseño Estándar"
                          color="primary"
                          value="1"
                        />
                        <VRadio
                          key="2"
                          label="Diseño Personalizado"
                          color="primary"
                          value="0"
                        />
                      </div>
                    </VRadioGroup>
                  </VCol>
                  <VCol 
                    cols="12"
                    md="6">
                    <VTextField
                      v-model="prices[i-1]"
                      prefix="$"
                      type="number"
                      label="Costo"
                      :rules="!isCupcake ? [] : [requiredValidator]"
                    />
                  </VCol>
                </VRow>
              </template>

              <VBtn
                class="mt-6"
                @click="optionCounter++"
              >
                Agregar Tipo
              </VBtn>
            </VCardText>
          </VCard>

          <FileInput 
            :images="service_files"
            @files="service_files" />
          
        </VCol>

        <VCol md="4" cols="12">
          <VCard title="Imagen Principal" class="mb-6">
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

                <AppTextField
                  v-model="sku"
                  label="SKU"
                  :rules="[requiredValidator]"
                />   

                <div class="app-select flex-grow-1">
                  <VLabel
                    class="mb-1 text-body-2 text-high-emphasis"
                    text="Categoría"
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
                    :menu-props="{ maxHeight: '300px' }"
                    @update:model-value="selectCategory">
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

                <label for="wholesale" v-if="isCupcake">¿Servicio disponible para rellenos y sabores?</label>
                <VCheckbox
                  v-if="isCupcake"
                  v-model="is_full"
                > 
                  <template #label>
                    {{ is_full ? 'SI' : 'NO' }}
                  </template>
                </VCheckbox>

                <VTextField
                  v-model="price"
                  prefix="$"
                  type="number"
                  label="Costo"
                  :rules="isCupcake ? [] : [requiredValidator]"
                  :style="{
                    display: isCupcake ? 'none' : 'block'
                  }"
                />         
                
                <VTextField
                  type="number"
                  v-model="estimated_delivery_time"
                  label="Tiempo de entrega (días)"
                  placeholder="Tiempo estimado de entrega"
                  min="1"
                  :rules="[requiredValidator, nonNegativeValidator]"
                />

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
    subject: servicios
</route>
