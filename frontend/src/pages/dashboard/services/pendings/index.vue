<script setup>

import { themeConfig } from '@themeConfig'
import { useServicesStores } from '@/stores/useServices'
import { excelParser } from '@/plugins/csv/excelParser'
import router from '@/router'

const servicesStores = useServicesStores()
const isMobile = ref(false)

const services = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalServices = ref(0)
const isRequestOngoing = ref(true)
const selectedService = ref({})
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
  const firstIndex = services.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = services.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalServices.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
})

watchEffect(fetchData)

async function fetchData() {

    services.value =  []

    let data = {
        search: searchQuery.value,
        state_id: 4,
        orderByField: 'id',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value
    }

    isRequestOngoing.value = true

    await servicesStores.fetchServices(data)

    services.value = servicesStores.getServices

    totalPages.value = servicesStores.last_page
    totalServices.value = servicesStores.servicesTotalCount

    data.value = servicesStores.data

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
            color: 'warning'
        }
}

const showDeleteDialog = serviceData => {
    isConfirmDeleteDialogVisible.value = true
    selectedService.value = { ...serviceData }
}

const showStateDialog = (serviceData, id) => {
    isConfirmApproveDialogVisible.value = true
    state_id.value = id
    selectedService.value = { ...serviceData }
}

const stateService = async state_id => {
    isConfirmApproveDialogVisible.value = false

    let data = {
        state_id: state_id
    }

    let res = await servicesStores.updateState(data, selectedService.value.id)
    selectedService.value = {}

    advisor.value = {
        type: res.data.success ? 'success' : 'error',
        message: res.data.success ? 'Servicio actualizado!' : res.data.message,
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

const removeService = async () => {
    isConfirmDeleteDialogVisible.value = false

    let res = await servicesStores.deleteService({ ids: [selectedService.value.id] })
    selectedService.value = {}

    advisor.value = {
        type: res.data.success ? 'success' : 'error',
        message: res.data.success ? 'Servicio eliminado!' : res.data.message,
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

    await servicesStores.fetchServices(data)

    let dataArray = [];
        
    servicesStores.getServices.forEach(element => {

    let data = {
        ID: element.id,
        SERVICIO: element.name,
        TIENDA: element.user.user_detail.store_name ?? (element.user.supplier?.company_name ?? (element.user.name + ' ' + (element.user.last_name ?? ''))),
        SKU: element.sku,
        PRECIO: element.price
    }
            
    dataArray.push(data)
    })

    excelParser()
    .exportDataFromJSON(dataArray, "pending-services", "csv");

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

        <!-- 👉 services -->
        <VCard
            title="Servicios Pendientes"
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
                        clearable
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
                        <th> SERVICIO </th>
                        <th class="pe-4"> SKU </th>
                        <th class="pe-4"> PRECIO </th>
                        <th class="pe-4"> STATUS </th>
                        <th scope="pe-4" v-if="$can('aprobar', 'servicios') || $can('rechazar', 'servicios') || $can('eliminar', 'servicios')">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="service in services"
                        :key="service.id"
                        style="height: 3.75rem;">
                        <td> {{ service.id }} </td>
                        <td> 
                            <div class="d-flex align-center gap-x-2">
                                <VAvatar
                                    v-if="service.image"
                                    size="38"
                                    :variant="service.image ? 'outlined' : 'tonal'"
                                    rounded
                                    :image="themeConfig.settings.urlStorage + service.image"
                                />
                                <div class="d-flex flex-column">
                                    <span class="text-body-1 font-weight-medium text-uppercase">{{ service.name }}</span>
                                    <span class="text-sm text-disabled">Tienda: {{ service.user.user_detail.store_name ?? (service.user.supplier?.company_name ?? (service.user.name + ' ' + (service.user.last_name ?? ''))) }}</span>
                                </div>
                            </div>
                        </td>
                        <td> {{ service.sku }} </td>
                        <td> {{ (parseFloat(service.price)).toLocaleString("en-IN", { style: "currency", currency: 'COP' }) }}</td>
                        <td> 
                            <VChip
                                v-bind="resolveStatus(service.state_id)"
                                density="default"
                                label
                            />
                        </td>
                        <td class="text-center" style="width: 5rem;" v-if="$can('aprobar', 'servicios') || $can('rechazar', 'servicios') || $can('eliminar', 'servicios')">
                            <VBtn
                                v-if="$can('aprobar', 'servicios')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="showStateDialog(service, 3)">
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
                                v-if="$can('rechazar', 'servicios')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="showStateDialog(service, 6)">
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
                                v-if="$can('eliminar','servicios')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="showDeleteDialog(service)">
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
                <tfoot v-show="!services.length">
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
            <VCard title="Eliminar Servicio">
                <VDivider class="mt-4"/>
                <VCardText>
                    Está seguro de eliminar el servicio <strong>{{ selectedService.name }}</strong>?.
                </VCardText>

                <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn
                    color="secondary"
                    variant="tonal"
                    @click="isConfirmDeleteDialogVisible = false">
                    Cancelar
                </VBtn>
                <VBtn @click="removeService">
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
            <VCard :title=" (state_id === 3 ? 'Aprobar ': 'Rechazar ') + 'Servicio'">
                <VDivider class="mt-4"/>
                <VCardText>
                    Está seguro de {{ state_id === 3 ? 'aprobar': 'rechazar' }}  el servicio <strong>{{ selectedService.name }}</strong>?.
                </VCardText>

                <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn
                    color="secondary"
                    variant="tonal"
                    @click="isConfirmApproveDialogVisible = false">
                    Cancelar
                </VBtn>
                <VBtn @click="stateService(state_id)">
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

    .service-widget {
        border-block-end: 1px solid rgba(var(--v-theme-on-surface), var(--v-border-opacity));
        padding-block-end: 1rem;
    }
</style>
<route lang="yaml">
    meta:
      action: ver
      subject: servicios-pendientes
</route>
