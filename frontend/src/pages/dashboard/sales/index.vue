<script setup>

import { themeConfig } from '@themeConfig'
import { useClipboard } from '@vueuse/core'
import { useProductsStores } from '@/stores/useProducts'
import { useCategoriesStores } from '@/stores/useCategories'
import { useSuppliersStores } from '@/stores/useSuppliers'
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'
import show from "@/components/products/show.vue";

const productsStores = useProductsStores()
const categoriesStores = useCategoriesStores()
const suppliersStores = useSuppliersStores()
const cp = useClipboard()

const myProductsList = ref([])
const products = ref([])
const suppliers = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalProducts = ref(0)
const isRequestOngoing = ref(true)
const selectedProduct = ref({})
const totalSum = ref(0)

const date = ref(null)
const dateRangeArray = ref(null)

const isProductDetailDialog = ref(false)
const isConfirmDeleteDialogVisible = ref(false)

const favourite = ref(null)
const archived = ref(null)
const discarded = ref(null)

const supplier_id = ref(null)
const selectedStatus = ref(3)
const selectedCategory = ref()
const selectedDetail = ref()

const isConfirmApproveDialogVisible = ref(false)
const state_id = ref(3)

const rol = ref(null)
const userData = ref(null)

const resolveStatus = statusMsg => {
  if (statusMsg === 3)
    return {
      text: 'Publicado',
      color: 'success',
    }
  if (statusMsg === 5)
    return {
      text: 'Eliminado',
      color: 'warning',
    }
  if (statusMsg === 4)
    return {
      text: 'Pendiente',
      color: 'error',
    }

  if (statusMsg === 6)
    return {
      text: 'Rechazado',
      color: 'warning',
    }
}
const status = ref([
  {
    title: 'Publicado',
    value: 3
  },
  {
    title: 'Pendiente',
    value: 4
  },
  {
    title: 'Eliminado',
    value: 5
  },
  {
    title: 'Rechazado',
    value: 6
  },
])


const typesales = ref([
  {
    title: 'Venta Detal',
    value: 1
  },
  {
    title: 'Venta por mayor',
    value: 2
  }
])

const categories = ref([])

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
})

const loadSuppliers = () => {
  suppliers.value = suppliersStores.getSuppliers
}

onMounted(async () => {

  await suppliersStores.fetchSuppliers({limit: -1})

  loadSuppliers()
})

watchEffect(fetchData)

async function fetchData() {

    userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
    rol.value = userData.value.roles[0].name

    if(favourite.value === 0 && archived.value === 0 && discarded.value === 0) {
        favourite.value = null
        archived.value = null
        discarded.value = null
    }

    let data = {
        search: searchQuery.value,
        favourite: favourite.value,
        archived: archived.value,
        discarded: discarded.value,
        state_id: selectedStatus.value,
        type_sales: selectedDetail.value,
        category_id: selectedCategory.value,
        orderByField: 'count_sales',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value,
        isSales: '1',
        supplier_id: supplier_id.value,
        date: dateRangeArray.value
    }

    isRequestOngoing.value = true
    
    let info = { 
        limit: -1, 
        category_type_id: 1
    }

    await categoriesStores.fetchCategoriesOrder(info)

    categories.value = categoriesStores.getCategories

    await productsStores.fetchProducts(data)

    totalSum.value = productsStores.data.totalSum

    products.value =  []
    myProductsList.value =  []
    myProductsList.value = productsStores.getProducts

    myProductsList.value.forEach(element =>
        products.value.push({
        id: element.id,
        favourite: element.favourite,
        discarded: element.discarded,
        user: element.user,
        state: element.state,
        archived: element.archived,            
        title: element.name,
        image: element.image,
        price: element.price_for_sale,
        originalLink: themeConfig.settings.urlDomain + 'products/' + element.slug,
        categories: element.colors[0]?.categories.map(item => item.category.name),// Utiliza map para extraer los nombres de las categorías
        rating: element.rating,//agregar mas adelante informacion
        comments: 0,//agregar mas adelante informacion
        sales: element.sales,//agregar mas adelante informacion
        selling_price: 0,//agregar mas adelante informacion,
        likes: element.likes
        })
    );

    totalPages.value = productsStores.last_page
    totalProducts.value = productsStores.productsTotalCount

    isRequestOngoing.value = false
}

const changeDate = () => {

    if(date.value !== null) {
        dateRangeArray.value = date.value.split(' a ')
        fetchData()
    }
}

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: 'Y-m-d'
  }

  const currentDate = new Date();
  const year = currentDate.getFullYear();
  const month = String(currentDate.getMonth() + 1).padStart(2, '0');
  const day = String(currentDate.getDate()).padStart(2, '0');
  const formattedDate = `${year}-${month}-${day}`;

  config.mode = 'range'
  config.maxDate = formattedDate

  return config
})

const showStateDialog = (productData, id) => {
    isConfirmApproveDialogVisible.value = true
    state_id.value = id
    selectedProduct.value = { ...productData }
}

const showDeleteDialog = productData => {
  isConfirmDeleteDialogVisible.value = true
  selectedProduct.value = { ...productData }
}

const stateProduct = async state_id => {
    isConfirmApproveDialogVisible.value = false

    let data = {
        state_id: state_id
    }

    let res = await productsStores.updateState(data, selectedProduct.value.id)
    selectedProduct.value = {}

    advisor.value = {
        type: res.data.success ? 'success' : 'error',
        message: res.data.success ? 'Producto actualizado!' : res.data.message,
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


const getSuppliers = computed(() => {
    return suppliers.value.map((supplier) => {
        return {
          title: supplier.user.user_detail.store_name ?? (supplier.company_name ?? (supplier.user.name + ' ' + (supplier.user.last_name ?? ''))),
          value: supplier.user_id,
        }
    })
})

const editProduct = id => {
    router.push({ name : 'dashboard-products-products-edit-id', params: { id: id } })
}

const deleteProduct = id => {
  isConfirmDeleteDialogVisible.value = true
  selectedProduct.value = myProductsList.value.filter((element) => element.id === id )[0]
}

const updateLink = (data) => {

  let request = {}

  if(data.text === 'favourite')
      request = { favourite: data.value }
  else if(data.text === 'archived')
      request = { archived: data.value }
  else if(data.text === 'discarded')
      request = { discarded: data.value }

  productsStores.updateLink(request, data.id)
      .then(response => {

          window.scrollTo(0, 0)
                  
          advisor.value.show = true
          advisor.value.type = 'success'
          advisor.value.message = 'Producto actualizado!'

          closeAdvisor()
          fetchData()

      }).catch(error => {
          window.scrollTo(0, 0)
                  
          advisor.value.show = true
          advisor.value.type = 'error'

          if (error.feedback === 'params_validation_failed') {
              advisor.value.message = error.message
          } else {
              advisor.value.message = 'Se ha producido un error...! (Server Error)'
          }

          closeAdvisor()  
      })
}

const findArchived = () => {
  archived.value = (archived.value === 1) ? 0 : 1
  favourite.value = 0
  discarded.value = 0
}

const findFavourite = () => {
  favourite.value = (favourite.value === 1) ? 0 : 1
  archived.value = 0
  discarded.value = 0
}

const findDiscarded = () => {
    discarded.value = (discarded.value === 1) ? 0 : 1
    archived.value = 0
    favourite.value = 0
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
        const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + img);
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

const showProduct = async (id) => {
  isProductDetailDialog.value = true
  selectedProduct.value = myProductsList.value.filter((element) => element.id === id )[0]
}

const showAlert = function(alert) {
  advisor.value.show = alert.value.show
  advisor.value.type = alert.value.type
  advisor.value.message = alert.value.message
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
}

const removeProduct = async () => {
  isConfirmDeleteDialogVisible.value = false

  let res = await productsStores.deleteProduct({ ids: [selectedProduct.value.id] })
  selectedProduct.value = {}
  searchQuery.value = ''
  
  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Producto eliminado!' : res.data.message,
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
</script>

<template>
  <section>
    <VAlert
      v-if="advisor.show"
      :type="advisor.type"
      class="mb-6">  
      {{ advisor.message }}
    </VAlert>
    <Toaster />
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
    <VCard v-if="products"
      id="rol-list"
      title="Filtros">

      <VCardText>
        <VRow>
          <VCol cols="12" :sm="rol === 'Proveedor' ? '12' : '10'">
            <VTextField
              v-model="searchQuery"
              label="Buscar"
              placeholder="Buscar"
              density="compact"
              clearable
            />
          </VCol>
          <VCol cols="12" sm="2" v-if="rol !== 'Proveedor'">
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
                  icon="mdi-cart-remove"
                  class="me-1"
                  :color="discarded === 1 ? 'error' : 'default'"
                />
              </VBtn>
            </div> 
          </VCol>

          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            :sm="rol === 'Proveedor' ? '4' : '3'"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Estados"
              :items="status"
              clearable
              clear-icon="tabler-x"
              disabled
            />
          </VCol>

          <!-- 👉 Select Category -->
          <VCol
            cols="12"
            :sm="rol === 'Proveedor' ? '4' : '3'"
          >
            <VAutocomplete
              id="selectCategory"
              v-model="selectedCategory"
              label="Categoría"
              :items="categories"
              :item-title="item => item.name"
              :item-value="item => item.id"
              autocomplete="off"
              clearable
              :menu-props="{ maxHeight: '300px' }">
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
                      @click="closeDropdown"/>
                  </template>
                </v-list-item>
              </template>
            </VAutocomplete>
          </VCol>

          <!-- 👉 Select Detail  -->
          <VCol
            cols="12"
            :sm="rol === 'Proveedor' ? '4' : '3'"
          >
            <AppSelect
              v-model="selectedDetail"
              placeholder="Tipo de venta"
              :items="typesales"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <VCol
            cols="12"
            sm="3"
            v-if="rol !== 'Proveedor'"
          >
            <VAutocomplete
              v-model="supplier_id"
              placeholder="Proveedores"
              :items="getSuppliers"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex align-center flex-wrap gap-4">
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

        <VSpacer />

        <AppDateTimePicker
            :key="JSON.stringify(startDateTimePickerConfig)"
            v-model="date"
            label="Rango de fecha"
            :config="startDateTimePickerConfig"
            @change="changeDate"
            clearable
        />
      </VCardText>

      <VDivider />
            
      <VCardText class="px-0">   
            <v-table class="text-no-wrap">
              <thead>
                <tr class="text-no-wrap">
                  <th> #ID </th>
                  <th> PRODUCTO </th>
                  <th class="pe-4"> SKU </th>
                  <th class="pe-4"> TOTAL VENTAS </th>
                  <th class="pe-4"> VENTAS </th>
                  <th class="pe-4"> STATUS </th>
                  <th scope="pe-4" v-if="
                    $can('aprobar', 'productos') || 
                    $can('rechazar', 'productos') || 
                    $can('editar', 'productos') || 
                    $can('eliminar', 'productos')">
                    ACCIONES
                  </th>
                </tr>
            </thead>
            <tbody>
                <template  v-for="product in myProductsList" :key="product.id">
                    <tr style="height: 3.75rem;">
                        <td> {{ product.id }} </td>
                        <td> 
                        <div class="d-flex align-center gap-x-2">
                            <VAvatar
                            v-if="product.image"
                            size="38"
                            variant="outlined"
                            rounded
                            :image="themeConfig.settings.urlStorage + product.image"
                            />
                            <div class="d-flex flex-column">
                            <span class="text-body-1 font-weight-medium">{{ product.name }}</span>
                            <span class="text-sm text-disabled">Tienda: {{ product.user.user_detail.store_name ?? (product.user.supplier?.company_name ?? (product.user.name + ' ' + (product.user.last_name ?? ''))) }}</span>
                            </div>
                        </div>
                        </td>
                        <td> {{ product.colors[0]?.sku ?? '--' }} </td>
                        <td> {{ (parseFloat(product.sales_total)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}</td>
                        <td> {{ product.count_sales }}</td>
                        <td> 
                        <VChip
                            v-bind="resolveStatus(product.state_id)"
                            density="default"
                            label
                        />
                        </td>
                        <td class="text-center" style="width: 5rem;" 
                            v-if="$can('aprobar', 'productos') ||
                            $can('rechazar', 'productos') || 
                            $can('editar', 'productos') || 
                            $can('eliminar', 'productos')">          
                        <VBtn
                            v-if="$can('ver', 'productos')"
                            @click="showProduct(product.id)"
                            icon
                            variant="text"
                            color="default"
                            size="x-small">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Ver
                            </VTooltip>
                            <VIcon
                            size="28"
                            icon="tabler-eye"
                            class="me-1"
                            />
                        </VBtn>          
                        <VBtn
                            v-if="$can('aprobar', 'productos') && product.state_id === 4"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            @click="showStateDialog(product, 3)">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Aprobar
                            </VTooltip>      
                            <VIcon
                            size="22"
                            icon="mdi-cart-check" />
                        </VBtn>
                        <VBtn
                            v-if="$can('rechazar', 'productos') && product.state_id === 4"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            @click="showStateDialog(product, 6)">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Rechazar
                            </VTooltip>      
                            <VIcon
                            size="22"
                            icon="mdi-cart-off" />
                        </VBtn>
                        <!-- <VBtn
                            v-if="$can('editar', 'productos') && product.state_id !== 4"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            :disabled="product.state_id === 5"
                            @click="editProduct(product.id)">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Editar
                            </VTooltip>
                            <VIcon
                            size="22"
                            icon="tabler-edit" />
                        </VBtn> -->
                        <!-- <VBtn
                            v-if="$can('eliminar','productos')"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            :disabled="product.state_id === 5"
                            @click="showDeleteDialog(product)">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Eliminar
                            </VTooltip>  
                            <VIcon
                            size="22"
                            icon="tabler-trash" />
                        </VBtn> -->
                        </td>
                    </tr>
                </template>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!products.length">
              <tr>
                <td
                  colspan="8"
                  class="text-center">
                  Datos no disponibles
                </td>
              </tr>
            </tfoot>
            </v-table>
        
            <v-divider />

            <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
              <span class="text-sm text-disabled">
                {{ paginationData }}
              </span>

              <span class="text-sm text-disabled">
                <strong>TOTAL: COP {{ totalSum }}</strong>
              </span>

              <VPagination
                v-model="currentPage"
                size="small"
                :total-visible="5"
                :length="totalPages"/>
            </VCardText>
      </VCardText>
    </VCard>

    <show 
      v-model:isDrawerOpen="isProductDetailDialog"
      :product="selectedProduct"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Producto">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el producto de <strong>{{ selectedProduct.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeProduct">
              Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isConfirmApproveDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
            
      <DialogCloseBtn @click="isConfirmApproveDialogVisible = !isConfirmApproveDialogVisible" />

      <!-- Dialog Content -->
      <VCard :title=" (state_id === 3 ? 'Aprobar ': 'Rechazar ') + 'Producto'">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de {{ state_id === 3 ? 'aprobar': 'rechazar' }}  el producto <strong>{{ selectedProduct.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmApproveDialogVisible = false">
            Cancelar
          </VBtn>
          <VBtn @click="stateProduct(state_id)">
            Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
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
    subject: ventas
</route>