<script setup>

import { useServicesStores } from '@/stores/useServices'
import { themeConfig } from '@themeConfig'
import show from "@/components/services/show.vue";
import router from '@/router'

const props = defineProps({
  id: {
    type: Number,
    required: true
  }
})

const emitter = inject("emitter")
const servicesStores = useServicesStores()

const myServicesList = ref([])
const services = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalServices = ref(0)

const isServiceDetailDialog = ref(false)
const selectedService = ref({})
const isConfirmApproveDialogVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const state_id = ref(3)

const paginationData = computed(() => {
  const firstIndex = services.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = services.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalServices.value } registros`
})

watchEffect(fetchData)

async function fetchData() {

    let data = {
        search: searchQuery.value,
        orderByField: 'id',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value,
        supplierId: props.id
    }

    await servicesStores.fetchServices(data)

    myServicesList.value = servicesStores.getServices

    myServicesList.value.forEach(element =>
        services.value.push({
            id: element.id,
            favourite: element.favourite,
            discarded: element.discarded,
            user: element.user,
            state: element.state,
            archived: element.archived,            
            title: element.name,
            image: element.image,
            price: element.cupcakes.length > 0 ? element.cupcakes[0].price : element.price,
            originalLink: themeConfig.settings.urlDomain + 'services/' + element.slug,
            categories: element.categories.map(item => item.category.name),// Utiliza map para extraer los nombres de las categorías
            rating: element.rating,//agregar mas adelante informacion
            comments: 0,//agregar mas adelante informacion
            sales: element.sales_price,
            selling_price: element.selling_price,
            likes: element.likes
        })
    );

    totalPages.value = servicesStores.last_page
    totalServices.value = servicesStores.servicesTotalCount

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

const showService = async (id) => {
  isServiceDetailDialog.value = true
  selectedService.value = myServicesList.value.filter((element) => element.id === id )[0]
}

const showStateDialog = (serviceData, id) => {
    isConfirmApproveDialogVisible.value = true
    state_id.value = id
    selectedService.value = { ...serviceData }
}

const showDeleteDialog = serviceData => {
  isConfirmDeleteDialogVisible.value = true
  selectedService.value = { ...serviceData }
}

const stateService = async state_id => {
    isConfirmApproveDialogVisible.value = false

    let data = { state_id: state_id }
    let res = await servicesStores.updateState(data, selectedService.value.id)
    selectedService.value = {}

    let info = {
        message: res.data.success ? 'Servicio actualizado!' : res.data.message,
        error: false
    }

    emitter.emit('toast', info)

    await fetchData()

    return true

}

const removeService = async () => {
    isConfirmDeleteDialogVisible.value = false

    let res = await servicesStores.deleteService({ ids: [selectedService.value.id] })
    selectedService.value = {}

    let info = {
        message: res.data.success ? 'Servicio eliminado!' : res.data.message,
        error: false
    }

    emitter.emit('toast', info)

    await fetchData()

    return true
}

const editService = id => {
    router.push({ name : 'dashboard-services-services-edit-id', params: { id: id } })
}

</script>

<template>
    <section>
        <VCard title="Servicios publicados">
            <VCardText class="d-flex flex-wrap py-4 gap-4">
            <div
                class="me-3"
                style="width: 80px;">
                    
                <VSelect
                v-model="rowPerPage"
                density="compact"
                variant="outlined"
                :items="[10, 20, 30, 50]"/>
            </div>

            <VSpacer />

            <div class="d-flex align-center flex-wrap gap-4">
                <!-- 👉 Search  -->
                <div style="width: 10rem;">
                <VTextField
                    v-model="searchQuery"
                    placeholder="Buscar"
                    density="compact"
                    clearable
                />
                </div>
            </div>
            </VCardText>

            <VDivider />

            <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
                <tr>
                    <th class="px-2"> SERVICIO </th>
                    <th class="px-2"> SKU </th>
                    <th class="px-2"> PRECIO </th>
                    <th class="px-2"> STATUS </th>
                    <th class="px-2 text-end" v-if="
                        $can('aprobar', 'servicios') || 
                        $can('rechazar', 'servicios') || 
                        $can('editar', 'servicios') || 
                        $can('eliminar', 'servicios')">
                        ACCIONES
                    </th>
                </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
                <tr 
                    v-for="service in myServicesList"
                    :key="service.id"
                    style="height: 3.75rem;">
                    <td class="text-wrap px-1">  
                        <div class="d-flex align-center gap-x-2">
                            <VAvatar
                            v-if="service.image"
                            size="38"
                            variant="outlined"
                            rounded
                            :image="themeConfig.settings.urlStorage + service.image"
                            />
                            <div class="d-flex flex-column">
                            <span class="text-body-1 font-weight-medium">{{ service.name }}</span>
                            <span class="text-sm text-disabled">Tienda: {{ service.user.user_detail.store_name ?? (service.user.name + ' ' + (service.user.last_name ?? '')) }}</span>
                            </div>
                        </div>
                    </td>
                    <td> {{ service.sku }} </td>
                    <td> {{ (parseFloat(service.cupcakes.length > 0 ? service.cupcakes[0].price : service.price)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}</td>
                    <td> 
                        <VChip
                            v-bind="resolveStatus(service.state_id)"
                            density="default"
                            label
                        />
                    </td>
                    <!-- 👉 Acciones -->
                    <td class="text-center" style="width: 5rem;" 
                        v-if="$can('aprobar', 'servicios') ||
                        $can('rechazar', 'servicios') || 
                        $can('editar', 'servicios') || 
                        $can('eliminar', 'servicios')">          
                        <VBtn
                            v-if="$can('ver', 'servicios')"
                            @click="showService(service.id)"
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
                            v-if="$can('aprobar', 'servicios') && service.state_id === 4"
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
                            v-if="$can('rechazar', 'servicios') && service.state_id === 4"
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
                            v-if="$can('editar', 'servicios') && service.state_id !== 4"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            :disabled="service.state_id === 5"
                            @click="editService(service.id)">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Editar
                            </VTooltip>
                            <VIcon
                            size="22"
                            icon="tabler-edit" />
                        </VBtn>
                        <VBtn
                            v-if="$can('eliminar','servicios')"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            :disabled="service.state_id === 5"
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
                    colspan="7"
                    class="text-center">
                    Datos no disponibles
                </td>
                </tr>
            </tfoot>
            </VTable>
                
            <VDivider />

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

        <show 
            v-model:isDrawerOpen="isServiceDetailDialog"
            :service="selectedService"/>
  
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
                Está seguro de eliminar el servicio de <strong>{{ selectedService.name }}</strong>?.
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
    </section>
</template>
