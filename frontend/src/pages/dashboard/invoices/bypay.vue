<script setup>

import { themeConfig } from '@themeConfig'
import { useInvoicesStores } from '@/stores/useInvoices'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { avatarText } from '@core/utils/formatters'
import { ref } from 'vue'
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

const paginationData = computed(() => {
  const firstIndex = invoices.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = invoices.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalInvoices.value } registros`
})

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
  let params = {
    search: searchQuery.value,
    orderByField: 'invoices.id',
    orderBy: 'desc',
    status: 6,
    limit: rowPerPage.value,
    page: currentPage.value,
    invoices: true,
    user_id: supplier_id.value,
  }

  isRequestOngoing.value = true
  await invoicesStores.fetchByPay(params)
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

const resolveInvoiceStatusVariantAndIcon = (invoice) => {
  // Return status code to reuse the same map as pending.vue
  if (invoice.unpaid_invoices_count > 0) return 14
  if (invoice.paid_invoices_count > 0) return 12
  return 6
}

const resolveInvoiceProducts = (invoice) => {
  // Return status code to reuse the same map as pending.vue
  if (invoice.unpaid_invoices_count > 0) {
    return [
        invoice.products_bypay_total ?? 0,
        parseFloat( (invoice.products_bypay_total ?? 0) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }),
        invoice.products_invoice_bypay_count
    ]
  } else if (invoice.paid_invoices_count > 0) {
    return [
        invoice.products_paid_total ?? 0,
        parseFloat( (invoice.products_paid_total ?? 0) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }),
        invoice.products_invoice_paid_count
    ]
  } else {
    return [
        invoice.products_total ?? 0,
        parseFloat( (invoice.products_total ?? 0) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }),
        invoice.products_count
    ]
  }
}

const resolveInvoiceServices = (invoice) => {
  // Return status code to reuse the same map as pending.vue
  if (invoice.unpaid_invoices_count > 0) {
    return [
        invoice.services_bypay_total ?? 0,
        parseFloat( (invoice.services_bypay_total ?? 0) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }),
        invoice.services_invoice_bypay_count
    ]
  } else if (invoice.paid_invoices_count > 0) {
    return [
        invoice.services_paid_total ?? 0,
        parseFloat( (invoice.services_paid_total ?? 0) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }),
        invoice.services_invoice_paid_count
    ]
  } else {
    return [
        invoice.services_total ?? 0,
        parseFloat( (invoice.services_total ?? 0) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }),
        invoice.services_count
    ]
  }
}

const statusMap = (code) => {
  switch(code) {
    case 3: return { variant: 'warning', icon: 'mdi-progress-close', label: 'En ajuste' }
    case 6: return { variant: 'error', icon: 'tabler-chart-pie', label: 'Pendiente' }
    case 12: return { variant: 'success', icon: 'tabler-check', label: 'Pagada' }
    case 14: return { variant: 'info', icon: 'tabler-alert-circle', label: 'Facturada (sin pago)' }
    default: return { variant: 'warning', icon: 'tabler-chart-pie', label: 'Pendiente' }
  }
}

const addInvoiceByUser = invoiceData => {
  router.push({ name : 'dashboard-invoices-pay-id', params: { id: invoiceData.user.id }, query: {invoice: invoiceData.id } })
}

</script>

<template>
  <section>
    <VDialog
      v-model="isRequestOngoing"
      width="300"
      persistent>
      <VCard color="primary" width="300">
        <VCardText class="pt-3">
          Cargando
          <VProgressLinear indeterminate color="white" class="mb-0" />
        </VCardText>
      </VCard>
    </VDialog>

    <VCard title="Facturas pendientes por pagar">
      <VCardText class="d-flex align-center flex-wrap gap-4">
        <div class="d-flex align-center">
          <span class="text-no-wrap me-3">Ver:</span>
          <VSelect v-model="rowPerPage" density="compact" :items="[10,20,30,50]" class="me-3" />
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
          <div style="width: 30rem;">
            <v-text-field v-model="searchQuery" placeholder="Buscar" density="compact" clearable />
          </div>
        </div>
      </VCardText>

      <v-divider />

      <v-table class="text-no-wrap">
        <thead>
          <tr>
            <th scope="col"> ESTATUS </th>
            <th scope="col"> FACTURA # </th>
            <th scope="col"> EMPRESA </th>
            <th scope="col"> CONTACTO </th>
            <th scope="col"> PRODUCTOS </th>
            <th scope="col"> SERVICIOS </th>
            <th scope="col"> TOTAL </th>
            <th scope="col" v-if="$can('editar','facturas') || $can('eliminar','facturas')"> ACCIONES </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in invoices" :key="invoice.id" style="height: 3.75rem;">
            <td class="text-center">
              <VTooltip>
                <template #activator="{ props }">
                  <VAvatar v-bind="props" :size="30" :color="statusMap(resolveInvoiceStatusVariantAndIcon(invoice)).variant" variant="tonal">
                    <VIcon :size="20" :icon="statusMap(resolveInvoiceStatusVariantAndIcon(invoice)).icon" />
                  </VAvatar>
                </template>
                <p class="mb-0"> {{ statusMap(resolveInvoiceStatusVariantAndIcon(invoice)).label }} </p>
                <p class="mb-0"> Total: {{ ( parseFloat( resolveInvoiceProducts(invoice)[0] + resolveInvoiceServices(invoice)[0] ) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }) }}</p>
              </VTooltip>
            </td>
            <td class="text-wrap" >
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ invoice.invoice_id }}</span>
              </div>
            </td>
            <td class="text-wrap">
              <div class="d-flex align-center gap-x-3">
                <VAvatar :variant="invoice.user.avatar ? 'outlined' : 'tonal'" size="38">
                  <VImg v-if="invoice.user.avatar" style="border-radius: 50%;" :src="themeConfig.settings.urlStorage + invoice.user.avatar" />
                  <span v-else>{{ avatarText(invoice.user.name) }}</span>
                </VAvatar>
                <div class="d-flex flex-column">
                  <span class="font-weight-medium cursor-pointer text-primary" @click="seeSupplier(invoice.user.supplier)">
                    {{ invoice.user.supplier?.company_name }}
                  </span>
                  <span class="text-sm text-disabled" v-if="invoice.user.supplier?.document">
                    {{  invoice.user.supplier.document?.type.code }}: {{  invoice.user.supplier.document?.main_document }}
                  </span>
                </div>
              </div>
            </td>
            <td class="text-wrap">
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ invoice.user.name }} {{ invoice.user.last_name ?? '' }}</span>
                <span class="text-sm text-disabled">{{ invoice.user.email }}</span>
              </div>
            </td>
            <td class="text-wrap" style="width: 150px;">
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ resolveInvoiceProducts(invoice)[1] }}</span>
                <span class="text-sm text-disabled">{{ resolveInvoiceProducts(invoice)[2] }} (Q)</span>
              </div>
            </td>
            <td class="text-wrap" style="width: 150px;">
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ resolveInvoiceServices(invoice)[1] }}</span>
                <span class="text-sm text-disabled">{{ resolveInvoiceServices(invoice)[2] }} (Q)</span>
              </div>
            </td>
            <td>
              {{ ( parseFloat( resolveInvoiceProducts(invoice)[0] + resolveInvoiceServices(invoice)[0] ) ).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2, style: 'currency', currency: 'COP' }) }}
            </td>
            <td class="text-center" style="width: 5rem;" v-if="$can('editar','facturas') || $can('eliminar','facturas')">
              <VBtn v-if="$can('crear','facturas')" icon size="x-small" color="default" variant="text" @click="addInvoiceByUser(invoice)">
                <VTooltip open-on-focus location="top" activator="parent"> Generar </VTooltip>
                <VIcon size="22" icon="mdi-file-document-edit-outline" />
              </VBtn>
            </td>
          </tr>
        </tbody>
        <tfoot v-show="!invoices.length">
          <tr>
            <td colspan="11" class="text-center"> Datos no disponibles </td>
          </tr>
        </tfoot>
      </v-table>

      <v-divider />

      <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
        <span class="text-sm text-disabled">{{ paginationData }}</span>
        <VPagination v-model="currentPage" size="small" :total-visible="5" :length="totalPages" />
      </VCardText>
    </VCard>
  </section>
</template>

<route lang="yaml">
    meta:
      action: ver
      subject: facturas
</route>
