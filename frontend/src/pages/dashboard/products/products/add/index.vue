<script setup>
import {
  useDropZone,
  useFileDialog,
  useObjectUrl,
} from '@vueuse/core'

import { ref } from 'vue'
import { useColorsStores } from '@/stores/useColors'
import { useCategoriesStores } from '@/stores/useCategories'
import { useTagsStores } from '@/stores/useTags'
import tags from '@/api/tags'

const optionCounter = ref(1)
const dropZoneRef = ref()
const fileData = ref([])

const { open, onChange } = useFileDialog({ accept: 'image/*' })



const colorsStores = useColorsStores()
const categoriesStores = useCategoriesStores()
const tagsStores = useTagsStores() 

const categories = ref([])
const listColors = ref([])
const listtags = ref([])

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

const content = ref('')
const activeTab = ref('Restock')
const isTaxChargeToProduct = ref(true)

const shippingList = [
  {
    desc: 'You\'ll be responsible for product delivery.Any damage or delay during shipping may cost you a Damage fee',
    title: 'Fulfilled by Seller',
    value: 'Fulfilled by Seller',
  },
  {
    desc: 'Your product, Our responsibility.For a measly fee, we will handle the delivery process for you.',
    title: 'Fulfilled by Company name',
    value: 'Fulfilled by Company name',
  },
]

const shippingType = ref('Fulfilled by Company name')
const deliveryType = ref('Worldwide delivery')

const selectedAttrs = ref([
  'Biodegradable',
  'Expiry Date',
])

const inventoryTabsData = [
  {
    icon: 'tabler-cube',
    title: 'Restock',
    value: '0',
  },
  {
    icon: 'tabler-car',
    title: 'Shipping',
    value: '1',
  },
  {
    icon: 'tabler-map-pin',
    title: 'Global Delivery',
    value: '2',
  },
  {
    icon: 'tabler-world',
    title: 'Attributes',
    value: '3',
  },
  {
    icon: 'tabler-lock',
    title: 'Advanced',
    value: '4',
  },
]

watchEffect(fetchData)
async function fetchData() {

  let data = { limit: -1 }

await colorsStores.fetchColors();
await categoriesStores.fetchCategoriesOrder(data)

listColors.value = colorsStores.getColors
categories.value = categoriesStores.getCategories
listtags.value = tagsStores.getTags

console.log(listtags.value)
}
</script>

<template>
  <div>
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 font-weight-medium">
          Añadir un nuevo producto
        </h4>
        <span>Recarga tu fiesta de productos</span>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn
          block
          color="default"
          variant="tonal"
          class="mb-2"
          :to="{ name: 'dashboard-products-products' }"
          >
          Regresar
       </VBtn>
      </div>
    </div>

    <VRow>
      <VCol md="8">
        <!-- 👉 Product Information -->
        <VCard
          class="mb-6"
          title="Información del Producto"
        >
          <VCardText>
            <VRow>
              <VCol cols="12">
                <AppTextField
                  label="Nombre"
                  placeholder="Nombre"
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
              >
                <AppTextField
                  label="Alto"
                  placeholder="Alto"
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
              >
                <AppTextField
                  label="Ancho"
                  placeholder="Ancho"
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
              >
                <AppTextField
                  label="Peso"
                  placeholder="Peso"
                />
              </VCol>

              <VCol
                cols="12"
                md="3"
              >
                <AppTextField
                  label="Profundo"
                  placeholder="Profundo"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  label="Material"
                  placeholder="Material"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  label="Tiempo de entregada"
                  placeholder="Tiempo estimado de entrega"
                />
              </VCol>

              <VCol cols="12">
                <span class="mb-1">Description (simple)</span>
                <TiptapEditor
                  v-model="content"
                  placeholder="Product Description"
                  class="border rounded"
                />
              </VCol>
              <VCol cols="12">
                <span class="mb-1">Description (detallada)</span>
                <TiptapEditor
                  v-model="content"
                  placeholder="Product Description"
                  class="border rounded"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- 👉 Media -->
        <VCard class="mb-6">
          <VCardItem>
            <template #title>
              Media
            </template>
            <template #append>
              <span class="text-primary font-weight-medium text-sm cursor-pointer">Add Media from URL</span>
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
                      Drag and Drop Your Image Here.
                    </div>
                    <span class="text-disabled">or</span>

                    <VBtn variant="tonal">
                      Browse Images
                    </VBtn>
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

        <!-- 👉 Variants -->
        <VCard
          title="Variants"
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
                  <AppSelect
                    :items="listColors"
                    item-value="id"
                    item-title="name"
                    placeholder="Color"
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
                  <AppSelect
                    :items="categories"
                    item-value="id"
                    item-title="name"
                    placeholder="Categoría"
                  />
                </VCol>
              </VRow>
            </template>

            <VBtn
              class="mt-6"
              @click="optionCounter++"
            >
              Add another option
            </VBtn>
          </VCardText>
        </VCard>

        <!-- 👉 Inventory -->
        
      </VCol>

      <VCol
        md="4"
        cols="12"
      >
        <!-- 👉 Pricing -->
        <VCard
          title="Precios"
          class="mb-6"
        >
          <VCardText>
            <AppTextField
              label="Costo"
              placeholder="Costo"
              class="mb-6"
            />
            <AppTextField
              label="Precio al detal"
              placeholder="Precio por unidad"
              class="mb-4"
            />

            <AppTextField
              label="Precio por mayor"
              placeholder="Precio por mayor"
              class="mb-4"
            />

            <AppTextField
              label="Stock"
              placeholder="Stock"
              class="mb-4"
            />
            <!--
            <VCheckbox
              v-model="isTaxChargeToProduct"
              label="Charge Tax on this product"
            />
              -->
            <VDivider class="my-2" />

            <!--<div class="d-flex flex-raw align-center justify-space-between ">
              <span>In stock</span>
              <VSwitch density="compact" />
            </div>-->
          </VCardText>
        </VCard>

        <!-- 👉 Organize -->
        <VCard title="Detalles">
          <VCardText>
            <div class="d-flex flex-column gap-y-4">
              <AppSelect
                placeholder="Marca"
                label="Marca"
                :items="['Apple', 'Samsung', 'Google']"
              />
              
              <AppSelect
                placeholder="Tags"
                label="Tags"
                :items="listtags"
                item-value="id"
                item-title="name"
              />
              <AppSelect
                placeholder="Estado"
                label="Seleccione estado"
                :items="['Públicado', 'Eliminado', 'Pendiente']"
              />
              
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
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
</style>
