<script setup>

import { themeConfig } from '@themeConfig'
import { prefixWithPlus } from '@/@core/utils/formatters'
import { useProductsStores } from '@/stores/useProducts'
import { excelParser } from '@/plugins/csv/excelParser'
import router from '@/router'

const productsStores = useProductsStores()

const isMobile = ref(false)

const widgetData = ref([
  {
    title: 'In-Store Sales',
    value: '$5,345.43',
    icon: 'tabler-home',
    desc: '5k orders',
    change: 5.7,
  },
  {
    title: 'Website Sales',
    value: '$674,347.12',
    icon: 'tabler-device-laptop',
    desc: '21k orders',
    change: 12.4,
  },
  {
    title: 'Descuentos',
    value: '$14,235.12',
    icon: 'tabler-gift',
    desc: '6k orders',
  },
  {
    title: 'Afiliados',
    value: '$8,345.23',
    icon: 'tabler-wallet',
    desc: '150 orders',
    change: -3.5,
  },
])

const products = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalProducts = ref(0)
const isRequestOngoing = ref(true)
const selectedProduct = ref({})
const isConfirmDeleteDialogVisible = ref(false)
const isConfirmApproveDialogVisible = ref(false)
const state_id = ref(3)

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

watchEffect(fetchData)

async function fetchData() {

    products.value =  []

    let data = {
        search: searchQuery.value,
        state_id: 4,
        orderByField: 'id',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value
    }

    isRequestOngoing.value = true

    await productsStores.fetchProducts(data)

    products.value = productsStores.getProducts

    totalPages.value = productsStores.last_page
    totalProducts.value = productsStores.productsTotalCount

    isRequestOngoing.value = false
}

onMounted(() => {
    checkIfMobile();
    // Agregar un listener para la detección de cambios en el tamaño de la pantalla
    window.addEventListener('resize', checkIfMobile);
});

const checkIfMobile = () => {
    // Verificar si el ancho de la pantalla es menor que cierto valor
    isMobile.value = window.innerWidth < 768; // Por ejemplo, consideramos móvil si el ancho es menor a 768px
}

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
            color: 'info',
        }
}

const editProduct = data => {
    router.push({ name : 'dashboard-products-products-edit-id', params: { id: data.id } })
}

const showDeleteDialog = productData => {
    isConfirmDeleteDialogVisible.value = true
    selectedProduct.value = { ...productData }
}

const showStateDialog = (productData, id) => {
    isConfirmApproveDialogVisible.value = true
    state_id.value = id
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

const removeProduct = async () => {
    isConfirmDeleteDialogVisible.value = false

    let res = await productsStores.deleteProduct({ ids: [selectedProduct.value.id] })
    selectedProduct.value = {}

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

const downloadCSV = async () => {

    isRequestOngoing.value = true

    let data = { 
        state_id: 4,
        limit: -1 
    }

    await productsStores.fetchProducts(data)

    let dataArray = [];
        
    productsStores.getProducts.forEach(element => {

    let data = {
        ID: element.id,
        PRODUCTO: element.name,
        TIENDA: element.user.name + '' + (element.user.last_name ?? ''),
        STOCK: element.in_stock === 0 ? 'NO' : 'SI',
        SKU: element.colors[0].sku,
        PRECIO: element.price_for_sale,
        QTY:  element.stock
    }
            
    dataArray.push(data)
    })

    excelParser()
    .exportDataFromJSON(dataArray, "pending-products", "csv");

    isRequestOngoing.value = false

}

</script>

<template>
  <section>
    <div>
        <VAlert
            v-if="advisor.show"
            :type="advisor.type"
            class="mb-6">  
            {{ advisor.message }}
        </VAlert>
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

        <!-- 👉 widgets -->
        <VCard class="mb-6">
            <VCardText>
                <VRow>
                    <template
                        v-for="(data, id) in widgetData"
                        :key="id" >
                        <VCol cols="12" sm="6" md="3" class="px-6">
                            <div 
                                class="d-flex justify-space-between"
                                :class="{ 'product-widget': isMobile && id < 3 }">
                                <div class="d-flex flex-column gap-y-1">
                                    <div class="text-body-1 font-weight-medium text-capitalize">
                                        {{ data.title }}
                                    </div>

                                    <span class="text-h5 my-1">
                                        {{ data.value }}
                                    </span>

                                    <div class="d-flex">
                                        <div class="me-2 text-disabled text-no-wrap">
                                            {{ data.desc }}
                                        </div>

                                        <VChip
                                            v-if="data.change"
                                            label
                                            :color="data.change > 0 ? 'success' : 'error'"
                                        >
                                            {{ prefixWithPlus(data.change) }}%
                                        </VChip>
                                    </div>
                                </div>

                                <VAvatar
                                    variant="tonal"
                                    rounded
                                    size="38"
                                >
                                    <VIcon
                                        :icon="data.icon"
                                        size="28"
                                    />
                                </VAvatar>
                            </div>
                        </VCol>
                        <VDivider
                            v-if="!isMobile && id < 3"
                            vertical
                            inset
                            length="95"
                        />
                    </template>
                </VRow>
            </VCardText>
        </VCard>

        <!-- 👉 products -->
        <VCard
            title="Productos Pendientes"
            class="mb-6" >

            <div class="d-flex flex-wrap gap-4 mx-5">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
                    <AppTextField
                        v-model="searchQuery"
                        placeholder="Buscar"
                        density="compact"
                        style="inline-size: 700px;"
                        class="me-3"
                    />
                </div>

                <VSpacer />

                <div class="d-flex gap-4 flex-wrap align-center">
                    <AppSelect
                        v-model="rowPerPage"
                        :items="[5, 10, 20, 25, 50]"
                    />
                    <!-- 👉 Export button -->
                    <VBtn
                        variant="tonal"
                        color="secondary"
                        prepend-icon="tabler-file-export"
                        @click="downloadCSV"
                    >
                        Exportar
                    </VBtn>
                </div>
            </div>

            <VDivider class="mt-4" />

            <!-- 👉 Datatable  -->

            <v-table class="text-no-wrap">
                <thead>
                    <tr class="text-no-wrap">
                        <th> #ID </th>
                        <th> PRODUCTO </th>
                        <th class="pe-4"> STOCK </th>
                        <th class="pe-4"> SKU </th>
                        <th class="pe-4"> PRECIO </th>
                        <th class="pe-4"> QTY </th>
                        <th class="pe-4"> STATUS </th>
                        <th scope="pe-4" v-if="$can('aprobar', 'productos') || $can('rechazar', 'productos') || $can('eliminar', 'productos')">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="product in products"
                        :key="product.id"
                        style="height: 3.75rem;">
                        <td> {{ product.id }} </td>
                        <td> 
                            <div class="d-flex align-center gap-x-2">
                                <VAvatar
                                    v-if="product.image"
                                    size="38"
                                    variant="tonal"
                                    rounded
                                    :image="themeConfig.settings.urlStorage + product.image"
                                />
                                <div class="d-flex flex-column">
                                    <span class="text-body-1 font-weight-medium">{{ product.name }}</span>
                                    <span class="text-sm text-disabled">Store: {{ product.user.name + ' ' + (product.user.last_name ?? '') }}</span>
                                </div>
                            </div>
                        </td>
                        <td>   
                            <VSwitch 
                                :model-value="product.in_stock === 1 ? true : false"
                                readonly
                            /> 
                        </td>
                        <td> {{ product.colors[0].sku }} </td>
                        <td> {{ product.price_for_sale }} </td>
                        <td> {{ product.stock }} </td>
                        <td> 
                            <VChip
                                v-bind="resolveStatus(product.state_id)"
                                density="default"
                                label
                            />
                        </td>
                        <td class="text-center" style="width: 5rem;" v-if="$can('aprobar', 'productos') || $can('rechazar', 'productos') || $can('eliminar', 'productos')">      
                            <!-- <VBtn
                                v-if="$can('editar', 'productos')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="editProduct(product)">
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
                            <VBtn
                                v-if="$can('aprobar', 'productos')"
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
                                v-if="$can('rechazar', 'productos')"
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

                            <VBtn
                                v-if="$can('eliminar','productos')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
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
                            </VBtn>
                        </td>
                    </tr>
                </tbody>
                <!-- 👉 table footer  -->
                <tfoot v-show="!products.length">
                    <tr>
                        <td
                        colspan="7"
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

                <VPagination
                    v-model="currentPage"
                    size="small"
                    :total-visible="5"
                    :length="totalPages"/>
            
            </VCardText>
        </VCard>

        <!-- 👉 Confirm Delete -->
        <VDialog
            v-model="isConfirmDeleteDialogVisible"
            persistent
            class="v-dialog-sm" >
            <!-- Dialog close btn -->
            
            <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

            <!-- Dialog Content -->
            <VCard title="Eliminar Producto">
                <VCardText>
                    Está seguro de eliminar el producto <strong>{{ selectedProduct.name }}</strong>?.
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

        <!-- 👉 Confirm Approve -->
        <VDialog
            v-model="isConfirmApproveDialogVisible"
            persistent
            class="v-dialog-sm" >
            <!-- Dialog close btn -->
            
            <DialogCloseBtn @click="isConfirmApproveDialogVisible = !isConfirmApproveDialogVisible" />

            <!-- Dialog Content -->
            <VCard :title=" (state_id === 3 ? 'Aprobar ': 'Rechazar ') + 'Producto'">
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
        
    </div>
    <!--FIN TEMPLATE-->
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

    @media(min-width: 991px) {
        .search {
            width: 30rem;
        }
    }

    .product-widget {
        border-block-end: 1px solid rgba(var(--v-theme-on-surface), var(--v-border-opacity));
        padding-block-end: 1rem;
    }
</style>
<route lang="yaml">
    meta:
      action: ver
      subject: productos-pendientes
</route>
