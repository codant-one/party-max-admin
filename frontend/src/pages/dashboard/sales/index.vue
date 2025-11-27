<script setup>

import { themeConfig } from '@themeConfig'
import { useProductsStores } from '@/stores/useProducts'
import { useCategoriesStores } from '@/stores/useCategories'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { useServicesStores } from '@/stores/useServices'
import { formatNumber } from '@/@core/utils/formatters'
import Toaster from "@/components/common/Toaster.vue";

const servicesStores = useServicesStores()
const productsStores = useProductsStores()
const categoriesStores = useCategoriesStores()
const suppliersStores = useSuppliersStores()

const myList = ref([])
const items = ref([])
const suppliers = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const isRequestOngoing = ref(true)
const selectedItem = ref({})
const totalSum = ref(0)

const date = ref(null)
const dateRangeArray = ref(null)

const isDetailDialog = ref(false)

const supplier_id = ref(null)
const selectedStatus = ref(3)
const selectedCategory = ref()
const selectedDetail = ref()

const rol = ref(null)
const userData = ref(null)
const type = ref(0)

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

const types = ref([
  { title: 'Producto', value: 0 },
  { title: 'Servicio', value: 1 }
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

const components = {
  0: defineAsyncComponent(() => import('@/components/products/show.vue')),
  1: defineAsyncComponent(() => import('@/components/services/show.vue'))
}

const currentComponent = computed(() => components[type.value])

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = items.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = items.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalItems.value } registros`
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

    let data = {
        search: searchQuery.value,
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
        category_type_id: type.value === 0 ? 1 : 2
    }

    await categoriesStores.fetchCategoriesOrder(info)

    categories.value = categoriesStores.getCategories

    if(type.value === 0) {
      await productsStores.fetchProducts(data)

      totalSum.value = productsStores.data.totalSum

      items.value =  []
      myList.value =  []
      myList.value = productsStores.getProducts
    } else {
      await servicesStores.fetchServices(data)

      totalSum.value = servicesStores.data.totalSum

      items.value =  []
      myList.value =  []
      myList.value = servicesStores.getServices
    }

    myList.value.forEach(element =>
      items.value.push({
        id: element.id,
        discarded: element.discarded,
        user: element.user,
        state: element.state,
        archived: element.archived,            
        title: element.name,
        image: element.image,
        price: element.price_for_sale,
        originalLink: themeConfig.settings.urlDomain + 'items/' + element.slug,
        categories: type.value === 0 ? 
          element.colors[0]?.categories.map(item => item.category.name) :
          element.categories.map(item => item.category.name),// Utiliza map para extraer los nombres de las categorías
        rating: element.rating,//agregar mas adelante informacion
        comments: 0,//agregar mas adelante informacion
        sales: element.sales,//agregar mas adelante informacion
        selling_price: 0,//agregar mas adelante informacion,
        likes: element.likes
      })
    );

    totalPages.value = productsStores.last_page
    totalItems.value = productsStores.productsTotalCount

    isRequestOngoing.value = false
}

const changeDate = () => {
    if(date.value !== null) {
        dateRangeArray.value = date.value.split(' a ')
        dateRangeArray.value = dateRangeArray.value[0] === '' ? null : dateRangeArray.value
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

const getSuppliers = computed(() => {
    return suppliers.value.map((supplier) => {
        return {
          title: supplier.user.user_detail.store_name ?? (supplier.company_name ?? (supplier.user.name + ' ' + (supplier.user.last_name ?? ''))),
          value: supplier.user_id,
        }
    })
})

const showService = async (id) => {
  isDetailDialog.value = true
  selectedItem.value = myList.value.filter((element) => element.id === id )[0]
}

const showProduct = async (id) => {
  isDetailDialog.value = true
  selectedItem.value = myList.value.filter((element) => element.id === id )[0]
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
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
    <VCard v-if="items"
      id="rol-list"
      title="Filtros">

      <VCardText>
        <VRow>
          <VCol cols="12" sm="12">
            <VTextField
              v-model="searchQuery"
              label="Buscar"
              placeholder="Buscar"
              density="compact"
              clearable
            />
          </VCol>

          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            :sm="rol === 'Proveedor' ? (type === 0 ? '4': '6') : (type === 0 ? '3': '4')"
          >
            <AppSelect
              v-model="type"
              placeholder="Tipo"
              :items="types"
            />
          </VCol>

          <!-- 👉 Select Category -->
          <VCol
            cols="12"
            :sm="rol === 'Proveedor' ? (type === 0 ? '4': '6') : (type === 0 ? '3': '4')"
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
            v-if="type === 0"
            cols="12"
            :sm="rol === 'Proveedor' ? (type === 0 ? '4': '6') : (type === 0 ? '3': '4')"
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
            :sm="type === 0 ? '3': '4'"
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
                  <th> {{ type === 0 ? 'PRODUCTO' : 'SERVICIO' }}</th>
                  <th class="pe-4"> SKU </th>
                  <th class="pe-4"> TOTAL VENTAS </th>
                  <th class="pe-4"> VENTAS </th>
                  <th class="pe-4"> STATUS </th>
                  <th scope="pe-4" v-if="$can('ver', 'productos')">
                    ACCIONES
                  </th>
                </tr>
            </thead>
            <tbody>
                <template  v-for="item in myList" :key="item.id">
                    <tr style="height: 3.75rem;">
                        <td> {{ item.id }} </td>
                        <td> 
                        <div class="d-flex align-center gap-x-2">
                            <VAvatar
                            v-if="item.image"
                            size="38"
                            variant="outlined"
                            rounded
                            :image="themeConfig.settings.urlStorage + item.image"
                            />
                            <div class="d-flex flex-column">
                            <span class="text-body-1 font-weight-medium">{{ item.name }}</span>
                            <span class="text-sm text-disabled">Tienda: {{ item.user.user_detail.store_name ?? (item.user.supplier?.company_name ?? (item.user.name + ' ' + (item.user.last_name ?? ''))) }}</span>
                            </div>
                        </div>
                        </td>
                        <td>
                          <template v-if="type === 0">
                            {{ item.colors?.[0]?.sku ?? '--' }}
                          </template>
                          <template v-else>
                            {{ item.sku ?? '--' }}
                          </template>
                        </td>
                        <td> ${{ formatNumber(item.sales_total ?? 0) }}</td>
                        <td> {{ item.count_sales }}</td>
                        <td> 
                        <VChip
                            v-bind="resolveStatus(item.state_id)"
                            density="default"
                            label
                        />
                        </td>
                        <td class="text-center" style="width: 5rem;" v-if="$can('ver', 'productos')">          
                          <VBtn
                              v-if="$can('ver', 'productos') && type === 0"
                              @click="showProduct(item.id)"
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
                            v-if="$can('ver', 'servicios') && type === 1"
                            @click="showService(item.id)"
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
                      </td>
                    </tr>
                </template>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!items.length">
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
                <strong>TOTAL: ${{ formatNumber(totalSum) }}</strong>
              </span>

              <VPagination
                v-model="currentPage"
                size="small"
                :total-visible="5"
                :length="totalPages"/>
            </VCardText>
      </VCardText>
    </VCard>

    <component 
      v-model:isDrawerOpen="isDetailDialog"
      :is="currentComponent"
      :product="selectedItem"
      :service="selectedItem"/>

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