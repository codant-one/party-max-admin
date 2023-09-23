<script setup>

import { useClipboard } from '@vueuse/core'
import { useProductsStores } from '@/stores/useProducts'
import { ref } from "vue"
import detailsProduct from "@/components/products/detailsProduct.vue";
// import AddNewCategoryDrawer from './AddNewCategoryDrawer.vue' 

const productsStores = useProductsStores()
const cp = useClipboard()

const myProductsList = ref([])
const products = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalProducts = ref(0)
const isRequestOngoing = ref(true)
const isAddNewProductDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedProduct = ref({})

const favourite = ref(0)
const archived = ref(0)
const discarded = ref(0)

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = products.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = products.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalProducts.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewProductDrawerVisible.value)
        selectedProduct.value = {}
})

watchEffect(fetchData)

async function fetchData() {

  let data = {
    search: searchQuery.value,
    orderByField: 'id',
    orderBy: 'desc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  isRequestOngoing.value = true

  await productsStores.fetchProducts(data)

  myProductsList.value = productsStores.getProducts

  myProductsList.value.forEach(element =>
    products.value.push({
      id: element.id,
      favourite: element.favourite,
      discarded: element.discarded,
      link: '',
      description: element.description,
      archived: element.archived,            
      title: element.name,
      image: element.image,
      sku: element.sku,
      price:element.price_for_sale,
      currency: 'COP',
      originalLink: 'https://dominioreal.com/' + element.slug,
      rating: 0,//agregar mas adelante informacion
      comments: 0,//agregar mas adelante informacion
      sales: 0,//agregar mas adelante informacion
      selling_price: 0//agregar mas adelante informacion
    })
  );

  totalPages.value = productsStores.last_page
  totalProducts.value = productsStores.productsTotalCount

  isRequestOngoing.value = false
}

const editProduct = productData => {
    isAddNewProductDrawerVisible.value = true
    selectedProducty.value = { ...productData }
}

const showDeleteDialog = productData => {
  isConfirmDeleteDialogVisible.value = true
  selectedProduct.value = { ...productData }
}

const removeProduct = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await productsStores.deleteProduct({ ids: [selectedProduct.value.id] })
  selectedProduct.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Producto eliminado con éxito!' : res.data.message,
    show: true
  }

  await fetchData()

  setTimeout(() => {
    advisor.value = {
      type: '',
      message: '',
      show: false
    }
  }, 3000)

  return true
}

const submitForm = async (product, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    product.data.append('_method', 'PUT')
    submitUpdate(product)
    return
  }

  submitCreate(product.data)
}


const submitCreate = productData => {

    productsStores.addProduct(productData)
        .then((res) => {
            if (res.data.success) {
                advisor.value = {
                    type: 'success',
                    message: 'Producto creado con éxito',
                    show: true
                }
                fetchData()
            }
            isRequestOngoing.value = false
        })
        .catch((err) => {
            advisor.value = {
                type: 'error',
                message: err.message,
                show: true
            }
            isRequestOngoing.value = false
        })

    setTimeout(() => {
        advisor.value = {
            type: '',
            message: '',
            show: false
        }
    }, 3000)
}

const submitUpdate = productData => {

    productsStores.updateProduct(productData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'Producto actualizado con éxito',
                    show: true
                }
                fetchData()
            }
            isRequestOngoing.value = false
        })
        .catch((err) => {
            advisor.value = {
                type: 'error',
                message: err.message,
                show: true
            }
            isRequestOngoing.value = false
        })

    setTimeout(() => {
        advisor.value = {
            type: '',
            message: '',
            show: false
        }
    }, 3000)
}

const updateLink = (data) => {

let request = {}

if(data.text === 'favourite')
    request = { favourite: data.value }
else if(data.text === 'archived')
    request = { archived: data.value }
else if(data.text === 'discarded')
    request = { discarded: data.value }

// userlinksStores.updateLink(request, data.id)
//     .then(response => {

//         window.scrollTo(0, 0)
                
//         advisor.value.show = true
//         advisor.value.type = 'success'
//         advisor.value.message = 'Enlace Actualizado'

//         closeAdvisor()
//         fetchData()

//     }).catch(error => {
//         closeRoleEditDialog()
//         window.scrollTo(0, 0)
                
//         advisor.value.show = true
//         advisor.value.type = 'error'

//         if (error.feedback === 'params_validation_failed') {
//             advisor.value.message = error.message
//         } else {
//             advisor.value.message = 'Ocurrió un error, intente nuevamente o contacte con el administrador...!'
//         }

//         closeAdvisor()  
//     })
}

const closeAdvisor = () => {
    setTimeout(() => {
    advisor.value = {
      type: '',
      message: '',
      show: false
    }
  }, 3000)
}

const copy = (data) => {
  cp.copy(data)
  advisor.value.type = 'success'
  advisor.value.show = true
  advisor.value.message = 'Enlace copiado!'
  closeAdvisor()
}

const open = (url) => {
    window.open(url, '_blank', 'noreferrer');

    advisor.value.type = 'success'
    advisor.value.show = true
    advisor.value.message = 'Enlace Abierto Exitosamente!'
    closeAdvisor()
}

const download = async (img) => {

    try {
        const response = await fetch(img);
        const blob = await response.blob();

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        link.setAttribute('download', 'image.jpg');

        document.body.appendChild(link);
        link.click();

        window.URL.revokeObjectURL(url);

        advisor.value.type = 'success'
        advisor.value.show = true
        advisor.value.message = 'Descarga Exitosa!'

      } catch (error) {

        advisor.value.type = 'error'
        advisor.value.show = true
        advisor.value.message = 'Error al descargar la imagen:' + error
      }

    closeAdvisor()
}

const showAlert = function(alert) {
  advisor.value.show = alert.value.show
  advisor.value.type = alert.value.type
  advisor.value.message = alert.value.message
}
</script>

<template>
  <section>
    <v-alert
      v-if="advisor.show"
      :type="advisor.type"
      class="mb-6">  
      {{ advisor.message }}
    </v-alert>

    <VCard v-if="products"
      id="rol-list"
      title="Filtros">
      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <v-btn
              v-if="$can('crear','productos')"
              prepend-icon="tabler-plus">
              Agregar Producto
            </v-btn>
          </VCol>
          <VCol cols="12" sm="6">
            <VTextField
              v-model="searchQuery"
              label="Buscar"
              placeholder="Buscar"
              density="compact"
            />
          </VCol>
          <VCol cols="12" sm="2">
            <div class="d-flex justify-content-end flex-wrap gap-4">
              <VBtn
                @click="findArchived()"
                icon
                variant="text"
                color="default"
                size="x-small">
                <VTooltip
                  open-on-focus
                  location="top"
                  activator="parent">
                  Archivados
                </VTooltip>
                <VIcon
                  size="20"
                  icon="tabler-building-store"
                  class="me-1"
                  :color="archived === 1 ? 'warning' : 'default'"
                />
              </VBtn>
              <VBtn
                @click="findFavourite()"
                icon
                variant="text"
                color="default"
                size="x-small">
                <VTooltip
                  open-on-focus
                  location="top"
                  activator="parent">
                  Favoritos
                </VTooltip>
                <VIcon
                  size="20"
                  icon="tabler-star-filled"
                  class="me-1"
                  :color="favourite === 1 ? 'info' : 'default'"
                />
              </VBtn>
              <VBtn
                @click="findDiscarded()"
                icon
                variant="text"
                color="default"
                size="x-small">
                <VTooltip
                  open-on-focus
                  location="top"
                  activator="parent">
                  Descartados
                </VTooltip>
                <VIcon
                  size="20"
                  icon="tabler-trash"
                  class="me-1"
                  :color="discarded === 1 ? 'error' : 'default'"
                />
              </VBtn>
            </div> 
          </VCol>
        </VRow>
      </VCardText>

      <v-row>
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

        <v-col cols="12">
          <v-card title="">
            <v-card-text class="d-flex align-center flex-wrap gap-4">
              <div
                class="d-flex align-center"
                style="width: 135px;">
                <span class="text-no-wrap me-3">Ver:</span>
                <VSelect
                  v-model="rowPerPage"
                  density="compact"
                  variant="outlined"
                  :items="[10, 20, 30, 50]"
                />
              </div>
            </v-card-text>

            <v-divider />
                            
            <VCardText>
              <VRow  class="gap-y-4">
                <VCol
                  v-for="product in products"
                    cols="12"
                    md="6"
                    sm="12"
                    class="ps-6">
                  <detailsProduct
                    :isShowComponent="true"
                    :product="product"
                    @alert="showAlert"
                    @copy="copy"
                    @open="open"
                    @download="download"
                    @updateLink="updateLink"
                    />
                </VCol>
              </VRow>
            </VCardText>

            <v-divider />

            <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
              <span class="text-sm text-disabled">
                {{ paginationData }}
              </span>

              <VPagination
                v-model="currentPage"
                size="small"
                :total-visible="5"
                :length="totalPages"
              />
            </VCardText>
          </v-card>
        </v-col>
      </v-row>
    </VCard>
  </section>
</template>

<style scope>
    .align-right {
      text-align: right !important;
    }

    .justify-content-end {
        justify-content: end !important;
    }

    .search {
        width: 100%;
    }

    @media(min-width: 991px){
        .search {
            width: 30rem;
        }
    }
</style>
<route lang="yaml">
  meta:
    action: ver
    subject: productos
</route>