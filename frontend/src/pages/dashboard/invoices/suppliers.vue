<script setup>

import { themeConfig } from '@themeConfig'
import { useInvoicesStores } from '@/stores/useInvoices'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { formatNumber } from '@/@core/utils/formatters'
import { ref } from 'vue'
import router from '@/router'

const invoicesStores = useInvoicesStores()
const suppliersStores = useSuppliersStores()

const suppliers = ref([])
const invoices = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalInvoices = ref(0)
const isRequestOngoing = ref(true)
const invoicePendingData = ref([])

const rol = ref(null)
const userData = ref(null)

const advisor = ref({
  type: '',
  message: '',
  show: false
})

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
  }

  isRequestOngoing.value = true
  await invoicesStores.fetchSuppliers(params)
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
  if (invoice.payment_date === null) return 14
  else if (invoice.payment_date !== null) return 12
  return 6
}

const resolveInvoiceProducts = (invoice) => {
  // Return status code to reuse the same map as pending.vue
  if (invoice.products_invoice_bypay_count > 0) {
    return [
        invoice.products_bypay_total ?? 0,
        '$' + formatNumber( (invoice.products_bypay_total ?? 0) ),
        invoice.products_invoice_bypay_count
    ]
  } else if (invoice.products_invoice_paid_count > 0) {
    return [
        invoice.products_paid_total ?? 0,
        '$' + formatNumber( (invoice.products_paid_total ?? 0) ),
        invoice.products_invoice_paid_count
    ]
  } else {
    return [
        invoice.products_total ?? 0,
        '$' + formatNumber( (invoice.products_total ?? 0) ),
        invoice.products_count
    ]
  }
}

const resolveInvoiceServices = (invoice) => {
  // Return status code to reuse the same map as pending.vue
  if (invoice.services_invoice_bypay_count > 0) {
    return [
        invoice.services_bypay_total ?? 0,
        '$' + formatNumber( (invoice.services_bypay_total ?? 0) ),
        invoice.services_invoice_bypay_count
    ]
  } else if (invoice.services_invoice_paid_count > 0) {
    return [
        invoice.services_paid_total ?? 0,
        '$' + formatNumber( (invoice.services_paid_total ?? 0) ),
        invoice.services_invoice_paid_count
    ]
  } else {
    return [
        invoice.services_total ?? 0,
        '$' + formatNumber( (invoice.services_total ?? 0) ),
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

const openLink = function (invoiceData) {
  window.open(themeConfig.settings.urlStorage + invoiceData.pdf)
}

const paymentReference = function (invoiceData) {
  window.open(themeConfig.settings.urlStorage + invoiceData.image)
}

const download = async(invoiceData) => {
  try {
    const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + themeConfig.settings.urlStorage + invoiceData.pdf);
    const blob = await response.blob();
    
    const blobUrl = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = invoiceData.pdf.replace('pdfs/', '');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

  } catch (error) {
    console.error('Error:', error);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '-'
  
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  //return `${year}-${month}-${day} ${hours}:${minutes}`
  return `${year}-${month}-${day}`
}

const generateInvoice = async () => {   

  isRequestOngoing.value = true
  const response = await invoicesStores.invoicesByUser(Number(userData.value.id))
  isRequestOngoing.value = false

  if(response.success) {   
    if (response.data.payments.length > 0) {
      router.push({ name: 'dashboard-invoices-add-id', params: { id: userData.value.id } })
    } else {
      advisor.value.show = true
      advisor.value.type = 'warning'
      advisor.value.message = 'No existen productos o servicios por facturar'

      setTimeout(() => {
        advisor.value = {
            type: '',
            message: '',
            show: false
        }
      }, 3000)
    }
  }
}
</script>

<template>
  <section v-if="userData">
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

    <VAlert
      v-if="advisor.show"
      :type="advisor.type"
      class="mb-6">       
      {{ advisor.message }}
    </VAlert>

    <VCard title="Facturación">
      <VCardText class="d-flex align-center flex-wrap gap-4">
        <div class="d-flex align-center">
          <span class="text-no-wrap me-3">Ver:</span>
          <VSelect v-model="rowPerPage" density="compact" :items="[10,20,30,50]" class="me-3" />
        </div>

        <VSpacer />

        <VBtn
          v-if="$can('crear','facturas')"
          prepend-icon="tabler-plus"
          @click="generateInvoice()">
          Generar Factura
        </VBtn>
      </VCardText>

      <v-divider />

      <v-table class="text-no-wrap">
        <thead>
          <tr>
            <th scope="col"> # </th>
            <th scope="col"> FECHA DE SOLICITUD </th>
            <th scope="col"> FECHA DE PAGO </th>
            <th scope="col"> TIPO DE PAGO </th>
            <th scope="col"> REFERENCIA DEL PAGO </th>
            <th scope="col" class="text-end"> PRODUCTOS </th>
            <th scope="col" class="text-end"> SERVICIOS </th>
            <th scope="col" class="text-end"> TOTAL </th>
            <th scope="col" class="text-center"> ESTATUS </th>
            <th scope="col" class="text-center" v-if="$can('editar','facturas') || $can('eliminar','facturas')"> ACCIONES </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in invoices" :key="invoice.id" style="height: 3.75rem;">      
            <td class="text-wrap" >
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ invoice.invoice_id }}</span>
              </div>
            </td>
            <td class="text-wrap text-center">
              {{ formatDate(invoice.created_at) }}
            </td>
            <td class="text-wrap text-center">
              {{ formatDate(invoice.payment_date) }}
            </td>
            <td class="text-wrap text-center">
              {{ (invoice.payment_type !== "null") ? invoice.payment_type : '-' }}
            </td>
            <td class="text-wrap text-center">
              {{ (invoice.reference !== "null") ? invoice.reference : '-' }}
            </td>
            <td class="text-wrap text-end" style="width: 150px;">
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ resolveInvoiceProducts(invoice)[1] }}</span>
                <span class="text-sm text-disabled">{{ resolveInvoiceProducts(invoice)[2] }} (Q)</span>
              </div>
            </td>
            <td class="text-wrap text-end" style="width: 150px;">
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ resolveInvoiceServices(invoice)[1] }}</span>
                <span class="text-sm text-disabled">{{ resolveInvoiceServices(invoice)[2] }} (Q)</span>
              </div>
            </td>
            <td class="text-end">
              ${{ formatNumber( resolveInvoiceProducts(invoice)[0] + resolveInvoiceServices(invoice)[0] ) }}
            </td>
            <td class="text-center">
              <VTooltip>
                <template #activator="{ props }">
                  <VAvatar v-bind="props" :size="30" :color="statusMap(resolveInvoiceStatusVariantAndIcon(invoice)).variant" variant="tonal">
                    <VIcon :size="20" :icon="statusMap(resolveInvoiceStatusVariantAndIcon(invoice)).icon" />
                  </VAvatar>
                </template>
                <p class="mb-0"> {{ statusMap(resolveInvoiceStatusVariantAndIcon(invoice)).label }} </p>
                <p class="mb-0"> Total: ${{ formatNumber( resolveInvoiceProducts(invoice)[0] + resolveInvoiceServices(invoice)[0] ) }}</p>
              </VTooltip>
            </td>
            <!-- 👉 Acciones -->
            <td class="text-center" style="width: 3rem;" v-if="$can('ver', 'facturas') || $can('eliminar', 'facturas')">      
                <VMenu>
                <template #activator="{ props }">
                    <VBtn v-bind="props" icon variant="text" color="default" size="x-small">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                        <path d="M12.52 20.924c-.87 .262 -1.93 -.152 -2.195 -1.241a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.088 .264 1.502 1.323 1.242 2.192"></path>
                        <path d="M19 16v6"></path>
                        <path d="M22 19l-3 3l-3 -3"></path>
                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                    </svg>
                    </VBtn>
                </template>
                <VList>
                    <VListItem @click="openLink(invoice)">
                        <template #prepend>
                            <VIcon icon="mdi-file-pdf-box" />
                        </template>
                        <VListItemTitle>Ver PDF</VListItemTitle>
                    </VListItem>
                    <VListItem @click="download(invoice)">
                        <template #prepend>
                          <VIcon icon="mdi-cloud-download-outline"/>
                        </template>
                        <VListItemTitle>Descargar</VListItemTitle>
                    </VListItem>  
                    <VListItem 
                      v-if="resolveInvoiceStatusVariantAndIcon(invoice) == 12"
                      @click="paymentReference(invoice)">
                        <template #prepend>
                            <VIcon icon="mdi-eye" />
                        </template>
                        <VListItemTitle>Comprobante de pago</VListItemTitle>
                    </VListItem>              
                </VList>
                </VMenu>
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
