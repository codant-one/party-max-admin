<script setup>

import { themeConfig } from '@themeConfig'
import { useInvoicesStores } from '@/stores/useInvoices'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { avatarText } from '@core/utils/formatters'
import { ref } from "vue"
import router from '@/router'

const invoicesStores = useInvoicesStores()
const suppliersStores = useSuppliersStores()

const suppliers = ref([])
const supplier_id = ref(null)
const invoices = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalInvoices = ref(0)
const isRequestOngoing = ref(true)

const rol = ref(null)
const userData = ref(null)

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = invoices.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = invoices.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalInvoices.value } registros`
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

    userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
    rol.value = userData.value.roles[0].name
    
    if(rol.value !== 'Proveedor') {
        await suppliersStores.fetchSuppliers({limit: -1})
        loadSuppliers()
    }
})

watchEffect(fetchData)

async function fetchData() {

    let data = {
        search: searchQuery.value,
        orderByField: 'users.id',
        orderBy: 'desc',
        status: 6,
        limit: rowPerPage.value,
        page: currentPage.value,
        invoices: true,
        user_id: supplier_id.value,
    }

    isRequestOngoing.value = true
    await invoicesStores.fetchPending(data)
    invoices.value = invoicesStores.getInvoices
    totalPages.value = invoicesStores.last_page
    totalInvoices.value = invoicesStores.invoicesTotalCount

    isRequestOngoing.value = false
}

const getSuppliers = computed(() => {
    return suppliers.value.map((supplier) => {
        return {
          title: supplier.user.user_detail.store_name ?? (supplier.company_name ?? (supplier.user.name + ' ' + (supplier.user.last_name ?? ''))),
          value: supplier.user_id,
        }
    })
})


const resolveInvoiceStatusVariantAndIcon = status => {

    let variant = 'secondary'
    let icon = 'tabler-x'

    switch(status) {
        case 3: 
            variant = 'warning'
            icon = 'mdi-progress-close'
        break;
        case 6: 
            variant = 'error'
            icon = 'tabler-chart-pie'
        break;
        case 12: 
            variant = 'success'
            icon = 'tabler-check'
        break;
        case 13: 
            variant = 'primary'
            icon = 'tabler-device-floppy'
        break;
        case 14: 
            variant = 'info'
            icon = 'tabler-alert-circle'
        break;
        default:
            variant = 'warning'
            icon = 'tabler-chart-pie'
        break; 
    }

    return {
        variant: variant,
        icon: icon
    }
}

const seeSupplier = supplierData => {
  router.push({ name : 'dashboard-admin-suppliers-id', params: { id: supplierData.id } })
}

const addInvoiceByUser = invoiceData => {
    router.push({ name : 'dashboard-invoices-add-id', params: { id: invoiceData.id } })
}

</script>

<template>
    <section>
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
        <VCard title="Facturas pendientes">
            <VCardText class="d-flex align-center flex-wrap gap-4">
                <div class="d-flex align-center">
                    <span class="text-no-wrap me-3">Ver:</span>
                    <VSelect
                        v-model="rowPerPage"
                        density="compact"
                        :items="[10, 20, 30, 50]"
                        class="me-3"
                    />
                </div>
                
                <VSpacer />

                <div class="d-flex align-center flex-wrap gap-4">
                    <VAutocomplete
                        v-if="rol !== 'Proveedor'"
                        v-model="supplier_id"
                        placeholder="Proveedores"
                        :items="getSuppliers"
                        clearable
                        clear-icon="tabler-x"
                        style="width: 20rem;"
                    />
                    <!-- 👉 Search  -->
                    <div style="width: 30rem;">
                        <v-text-field
                            v-model="searchQuery"
                            placeholder="Buscar"
                            density="compact"
                            clearable />
                    </div>
                </div>
            </VCardText>

            <v-divider />

            <v-table class="text-no-wrap">
                <!-- 👉 table head -->
                <thead>
                    <tr>
                        <th scope="col"> ESTATUS </th>
                        <th scope="col"> EMPRESA </th>
                        <th scope="col"> CONTACTO </th>
                        <th scope="col"> PRODUCTOS </th>
                        <th scope="col"> SERVICIOS </th>
                        <th scope="col"> TOTAL </th>
                        <th scope="col" v-if="$can('editar','facturas') || $can('eliminar','facturas')">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <!-- 👉 table body -->
                <tbody>
                    <tr 
                        v-for="invoice in invoices"
                        :key="invoice.id"
                        style="height: 3.75rem;">
                        <td class="text-center">
                            <VTooltip>
                                <template #activator="{ props }">
                                    <VAvatar
                                        v-bind="props"
                                        :size="30"
                                        :color="resolveInvoiceStatusVariantAndIcon(6).variant"
                                        variant="tonal"
                                    >
                                        <VIcon
                                            :size="20"
                                            :icon="resolveInvoiceStatusVariantAndIcon(6).icon"
                                        />
                                    </VAvatar>
                                </template>
                                <p class="mb-0"> Pendiente </p>
                                <p class="mb-0"> Total: {{ (parseFloat(invoice.products_total + invoice.services_total)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}</p>
                            </VTooltip>
                        </td>
                        <td class="text-wrap">
                            <div class="d-flex align-center gap-x-3">
                                <VAvatar
                                :variant="invoice.avatar ? 'outlined' : 'tonal'"
                                size="38"
                                >
                                    <VImg
                                        v-if="invoice.avatar"
                                        style="border-radius: 50%;"
                                        :src="themeConfig.settings.urlStorage + invoice.avatar"
                                    />
                                    <span v-else>{{ avatarText(invoice.name) }}</span>
                                </VAvatar>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-medium cursor-pointer text-primary" @click="seeSupplier(invoice.supplier)">
                                        {{ invoice.supplier?.company_name }}
                                    </span>
                                    <span class="text-sm text-disabled" v-if="invoice.supplier?.document">
                                        {{  invoice.supplier.document?.type.code }}: {{  invoice.supplier.document?.main_document }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="text-wrap">
                            <div class="d-flex flex-column">
                                <span class="font-weight-medium">
                                {{ invoice.name }} {{ invoice.last_name ?? '' }} 
                                </span>
                                <span class="text-sm text-disabled">{{ invoice.email }}</span>
                            </div>
                        </td>
                        <td class="text-wrap" style="width: 150px;">
                            <div class="d-flex flex-column">
                                <span class="font-weight-medium">
                                    {{ (parseFloat(invoice.products_total ?? '0.00')).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}
                                </span>
                                <span class="text-sm text-disabled">{{ invoice.products_count }} (Q)</span>
                            </div>
                        </td>
                        <td class="text-wrap" style="width: 150px;">
                            <div class="d-flex flex-column">
                                <span class="font-weight-medium">
                                    {{ (parseFloat(invoice.services_total ?? '0.00')).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}
                                </span>
                                <span class="text-sm text-disabled">{{ invoice.services_count }} (Q)</span>
                            </div>
                        </td>
                        <td> 
                            {{ (parseFloat(invoice.products_total + invoice.services_total)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}
                        </td>
                        <!-- 👉 Acciones -->
                        <td class="text-center" style="width: 5rem;" v-if="$can('editar','facturas') || $can('eliminar','facturas')">      
                            <VBtn
                                v-if="$can('crear','facturas')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="addInvoiceByUser(invoice)">
                                       
                                <VTooltip
                                    open-on-focus
                                    location="top"
                                    activator="parent"
                                >
                                    Generar
                                </VTooltip>

                                <VIcon
                                    size="22"
                                    icon="mdi-file-document-edit-outline" />
                            </VBtn>
                        </td>
                    </tr>
                </tbody>
                <!-- 👉 table footer  -->
                <tfoot v-show="!invoices.length">
                    <tr>
                        <td
                            colspan="11"
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
    </section>
</template>

<route lang="yaml">
    meta:
      action: ver
      subject: facturas
</route>
