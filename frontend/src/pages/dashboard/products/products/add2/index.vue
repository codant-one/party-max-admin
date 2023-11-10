<script setup>

import router from '@/router'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { useCategoriesStores } from '@/stores/useCategories'
import { useColorsStores } from '@/stores/useColors'
import { useProductsStores } from '@/stores/useProducts'

const categoriesStores = useCategoriesStores()
const colorsStores = useColorsStores()
const productsStores = useProductsStores()

const isRequestOngoing = ref(true)

const categories = ref([])
const refForm = ref()
const isFormValid = ref(false)
const error = ref(undefined)

const emitter = inject("emitter")

const category_id = ref()
const name = ref(null)
const description = ref('')
const sku = ref('')
const price = ref('')
const price_for_sale = ref('')
const stock = ref('')
const image = ref('')
const avatar = ref('')
const filename = ref([])
const width = ref([])
const height = ref([])
const deep = ref([])
const weigth = ref([])

const listColors = ref([])
const colors = ref([{
  color_id: '',
  filenameImages: [],
  images: [],
  gallery: []
}])

const activeTab = ref(0)

watchEffect(fetchData)

async function fetchData() {

    isRequestOngoing.value = true

    let data = { limit: -1 }

    await colorsStores.fetchColors();
    await categoriesStores.fetchCategoriesOrder(data)

    listColors.value = colorsStores.getColors
    categories.value = categoriesStores.getCategories

    isRequestOngoing.value = false
}

const handleFileChange = (event, color_id) => {
    const findColor = colors.value.find((color) => color.color_id === color_id)

    findColor.images = []
    findColor.gallery = []

    for (let i = 0; i < findColor.filenameImages.length; i++) {
        const reader = new FileReader();

        reader.onload = (e) => {
            const file = event.target.files[i]

            if (findColor) {
                URL.createObjectURL(file)
                findColor.images.push(e.target.result)

                resizeImage(file, 400, 400, 0.9)
                    .then(async blob => {
                        findColor.gallery.push(blob)
                    })
            }
        };
       
        reader.readAsDataURL(findColor.filenameImages[i]);
    }  
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

const addItem = () => {

  // eslint-disable-next-line vue/no-mutating-props
  colors.value.push({
    color_id: '',
    filenameImages: [],
    images: [],
    gallery: []
  })
}

const onSubmit = () => {

    refForm.value?.validate().then(({ valid }) => {
        
        if (valid) {
            error.value = undefined

            let formData = new FormData()

            formData.append('name', name.value)
            formData.append('description', description.value)
            formData.append('sku', sku.value)
            formData.append('price', price.value)
            formData.append('price_for_sale', price_for_sale.value)
            formData.append('stock', stock.value)
            formData.append('image', image.value)
            formData.append('category_id', category_id.value)
            formData.append('width', width.value)
            formData.append('height', height.value)
            formData.append('deep', deep.value)
            formData.append('weigth', weigth.value)
            formData.append('colors', JSON.stringify(colors.value))
            formData.append('gallery[]', colors.value[0].gallery)

            colors.value.forEach(function callback(value, index) {
                colors.value[index].gallery.forEach(function callback(value, i) {
                    formData.append('color_'+colors.value[index]['color_id']+'[]', colors.value[index].gallery[i])
                });
            });

            productsStores.addProduct(formData)
                .then((res) => {
                if (res.data.success) {

                    let data = {
                        message: 'Producto Creado!',
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

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
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
        <div>
            <VTabs
                v-model="activeTab"
                class="v-tabs-pill my-5"
            >
                <VTab> Detalles </VTab>
                <VTab> Galería </VTab>
            </VTabs>
        </div>
        <!-- 👉 Form -->
        <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit">
            <VRow>
                <VCol cols="12" md="9">
                    <VCard class="mb-8">
                        <VWindow v-model="activeTab">
                            <VWindowItem>
                                <VCardText>
                                    <VRow>
                                        <VCol cols="12">
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
                                                <template v-slot:selection="{ item, index }">
                                                    <v-chip v-if="index < 4">
                                                        <span>{{ item.title }}</span>
                                                    </v-chip>
                                                    <span
                                                        v-if="index === 4"
                                                        class="text-grey text-caption align-self-center"
                                                    >
                                                        (+{{ category_id.length - 4 }} otros)
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
                                        <VCol cols="12" md="8">
                                            <VRow>
                                                <VCol cols="12">
                                                    <VTextField
                                                        v-model="name"
                                                        :rules="[requiredValidator]"
                                                        label="Nombre"
                                                    />
                                                </VCol>
                                                <VCol cols="12">
                                                    <VTextarea
                                                        v-model="description"
                                                        rows="4"                                                
                                                        label="Descripción"
                                                    />
                                                </VCol>
                                                <VCol cols="12">
                                                    <VImg
                                                        v-if="avatar !== null"
                                                        :src="avatar"
                                                        :height="200"
                                                        aspect-ratio="16/9"
                                                        class="border-img"
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
                                        </VCol>

                                        <VCol cols="12" md="4">
                                            <VRow>
                                                <VCol cols="12">
                                                    <VTextField
                                                        v-model="sku"
                                                        clearable
                                                        clear-icon="tabler-circle-x"
                                                        label="SKU"
                                                        type="text"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                                <VCol cols="12" class="pb-0">
                                                    <VTextField
                                                        v-model="price"
                                                        label="Precio"
                                                        prefix="COP"
                                                        type="number"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                                <VCol cols="12" class="pb-0">
                                                    <VTextField
                                                        v-model="price_for_sale"
                                                        label="Precio a la venta"
                                                        prefix="COP"
                                                        type="number"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                                <VCol cols="12">
                                                    <VTextField
                                                        v-model="stock"
                                                        label="STOCK"
                                                        type="number"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                                <VCol cols="12">
                                                    <VTextField
                                                        v-model="width"
                                                        label="Ancho"
                                                        type="number"
                                                        suffix="Cm"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                                <VCol cols="12">
                                                    <VTextField
                                                        v-model="height"
                                                        label="Alto"
                                                        type="number"
                                                        suffix="Cm"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                                <VCol cols="12">
                                                    <VTextField
                                                        v-model="deep"
                                                        label="Profundo"
                                                        type="number"
                                                        suffix="Cm"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                                <VCol cols="12">
                                                    <VTextField
                                                        v-model="weigth"
                                                        label="Peso"
                                                        type="number"
                                                        suffix="gr"
                                                        :rules="[requiredValidator]">
                                                    </VTextField>
                                                </VCol>
                                            </VRow>
                                        </VCol>
                                    
                                    </VRow>
                                </VCardText>
                            </VWindowItem>
                            <VWindowItem>
                                <VCardText>
                                    <VCardTitle class="text-h6 title-truncate"> Galería de imágenes </VCardTitle>
                                    <VRow>
                                        <VCol cols="12">
                                            <div
                                                v-for="(color, index) in colors"
                                                :key="index"
                                                class="mb-3 border-img p-1"
                                            >             
                                                <VRow>
                                                    <VCol cols="12" md="4">
                                                        <VSelect
                                                            v-model="color.color_id"
                                                            label="Colores"
                                                            :items="listColors"
                                                            item-value="id"
                                                            item-title="name"
                                                            clearable
                                                            clear-icon="tabler-x"
                                                            no-data-text="No disponible"
                                                            />
                                                    </VCol>
                                                    <VCol cols="12" md="8">
                                                        <VFileInput
                                                            v-model="color.filenameImages"
                                                            label="Imágenes"
                                                            class="mb-2"
                                                            chips
                                                            accept="image/png, image/jpeg, image/bmp"
                                                            prepend-icon="tabler-camera"
                                                            @change="handleFileChange($event, color.color_id)"
                                                            multiple
                                                        />
                                                    </VCol>
                                                    <VCol 
                                                        v-for="(image, index) in color.images"
                                                        :key="index"
                                                        cols="4"
                                                        class="text-center">
                                                        <v-img
                                                            class="border-img"
                                                            :src="image"
                                                            :alt="`Imagen ${index + 1}`"
                                                            width="200"
                                                            height="200"
                                                        />
                                                    </VCol>  
                                                </VRow>
                                            </div>

                                            <div class="my-4">
                                                <VBtn @click="addItem">
                                                Agregar color
                                                </VBtn>
                                            </div>
                                        </VCol>
                                        
                                                            
                                    </VRow>
                                </VCardText>
                            </VWindowItem>
                        </VWindow>
                        
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
                                :to="{ name: 'dashboard-products-products' }">
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
</style>
