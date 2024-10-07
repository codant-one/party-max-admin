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
import ImageUploader from 'quill-image-uploader'
import FileInput from "@/components/common/FileInput.vue";
import router from '@/router'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const productsStores = useProductsStores()
const colorsStores = useColorsStores()
const categoriesStores = useCategoriesStores()
const brandsStores = useBrandsStores() 
const tagsStores = useTagsStores() 
const suppliersStores = useSuppliersStores()

const emitter = inject("emitter")
const isRequestOngoing = ref(true)

const optionCounter = ref(1)

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

const tag_id = ref()
const color_id = ref([])
const category_id = ref([])
const sku = ref([])
const product_files = ref([])
const brand_id = ref()
const user_id = ref(null)
const name = ref(null)
const single_description = ref(' ')
const description = ref(' ')
const price = ref('')
const price_for_sale = ref('')
const wholesale_price = ref('')
const wholesale_min = ref(6)
const wholesale = ref(false)
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


watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

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

const onSubmit = () => {

  // refForm.value?.validate().then(({ valid }) => {
  //     isValid.value = valid
  //     if (valid) {

  //         let formData = new FormData()

  //         //product
  //         formData.append('user_id', user_id.value ?? 0)
  //         formData.append('brand_id', brand_id.value)
  //         formData.append('name', name.value)
  //         formData.append('single_description', single_description.value)
  //         formData.append('description', description.value)
  //         formData.append('price', price.value)
  //         formData.append('price_for_sale', price_for_sale.value)
  //         formData.append('wholesale', wholesale.value ? 1 : 0)
  //         formData.append('wholesale_price', wholesale_price.value)
  //         formData.append('wholesale_min', wholesale_min.value)
  //         formData.append('stock', stock.value)
  //         formData.append('image', image.value)

  //         //product_details
  //         formData.append('width', width.value)
  //         formData.append('height', height.value)
  //         formData.append('deep', deep.value)
  //         formData.append('weigth', weigth.value)
  //         formData.append('material', material.value)

  //         //product_tags
  //         formData.append('tag_id', tag_id.value)

  //         //product_categories
  //         formData.append('category_id', JSON.stringify(category_id.value))

  //         //product_images
  //         formData.append('color_id', color_id.value)
  //         formData.append('sku', sku.value)
  //         formData.append('product_files', product_files.value)

  //         product_files.value.forEach(function callback(value, index) {
  //           value.forEach(function callback(image, i) {
  //             formData.append('images_'+index+'[]', image.blob)
  //           });
  //         });

  //         productsStores.addProduct(formData)
  //           .then((res) => {
  //             if (res.data.success) {

  //                 let data = {
  //                     message: 'Producto Creado!',
  //                     error: false
  //                 }

  //                 router.push({ name : 'dashboard-products-products'})
  //                 emitter.emit('toast', data)

  //             } else {

  //                 let data = {
  //                     message: 'ERROR',
  //                     error: true
  //                 }

  //                 router.push({ name : 'dashboard-products-products'})
  //                 emitter.emit('toast', data)
  //             }
  //           })
  //           .catch((err) => {
  //                 let data = {
  //                     message: err,
  //                     error: true
  //                 }

  //                 router.push({ name : 'dashboard-products-products'})
  //                 emitter.emit('toast', data)
  //           })

  //     }
  // })
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
            Añadir items a tus productos 😃🌟
          </h4>
          <span>Recarga tu fiesta de productos 🎉</span>
        </div>

        <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn
            color="default"
            variant="tonal"
            class="mb-2"
            :to="{ name: 'dashboard-products-products-inventory' }">
            Regresar
        </VBtn>
        <VBtn
          prepend-icon="tabler-plus"
          class="mb-2"
          type="submit">
            Cargar
        </VBtn>
        </div>
      </div>

      <VRow>
        <VCol md="8">
        

         
          
        </VCol>

        <VCol
          md="4"
          cols="12"
        >

        
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
    action: crear
    subject: productos
</route>
