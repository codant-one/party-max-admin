<script setup>

import { themeConfig } from '@themeConfig'
import { useQuotesStores } from '@/stores/useQuotes'
import Toaster from "@/components/common/Toaster.vue";

const quotesStores = useQuotesStores()

const quotes = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalQuotes = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const selectedQuote = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = quotes.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = quotes.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalQuotes.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
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
    
    await quotesStores.fetchQuotes(data)

    quotes.value = quotesStores.getQuotes

    totalPages.value = quotesStores.last_page
    totalQuotes.value = quotesStores.quotesTotalCount

    isRequestOngoing.value = false
}

const showDeleteDialog = quoteData => {
  isConfirmDeleteDialogVisible.value = true
  selectedQuote.value = { ...quoteData }
}

const removeQuote = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await quotesStores.deleteQuote({ ids: [selectedQuote.value.id] })
  selectedQuote.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Cotización eliminada!' : res.data.message,
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

const printQuote = async(quote) => {
  try {
    const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + themeConfig.settings.urlStorage + quote.file);
    const blob = await response.blob();
    
    const blobUrl = URL.createObjectURL(blob);
    
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = blobUrl;
    
    iframe.onload = () => {
      iframe.contentWindow.print();
    };
    
    document.body.appendChild(iframe);
  } catch (error) {
    console.error('Error:', error);
  }
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
    <VCard v-if="quotes" id="rol-list" title="Cotizaciones">
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

        <VTextField
          v-model="searchQuery"
          label="Buscar"
          placeholder="Buscar"
          density="compact"
          clearable
        />
      </VCardText>

      <VDivider />
            
      <VCardText class="px-0">   
        <v-table class="text-no-wrap">
          <thead>
            <tr class="text-no-wrap">
              <th> COTIZACIÓN </th>
              <th> NOMBRE </th>
              <th> TELÉFONO </th>
              <th class="pe-4"> DOCUMENTO </th>
              <th class="pe-4"> E-MAIL </th>
              <th class="pe-4"> FECHA SOLICITUD </th>
              <th class="pe-4"> FECHA VENCIMIENTO </th>
              <th class="pe-4"> TOTAL </th>
              <th class="pe-4"> ACCIONES </th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="quote in quotes" :key="quote.id"
              style="height: 3.75rem;">
              <td> {{ quote.id }} </td>
              <td class="text-wrap">
                {{ quote.name }}
              </td>
              <td class="text-wrap">
                {{ quote.phone.includes('+57') ? '' : '(+57)'}} {{ quote.phone }}
              </td>
              <td>
                {{ quote.document_type.code }} - {{ quote.document }}
              </td>
              <td class="text-wrap">
                {{ quote.email }}
              </td>
              <td class="text-wrap">
                {{ quote.start_date }}
              </td>
              <td class="text-wrap">
                <span :class="new Date(quote.due_date) < new Date() ? 'text-warning' : ''">{{ quote.due_date }}</span>
              </td>
              <td class="text-wrap">
                {{ quote.total }}
              </td>
              <!-- 👉 Acciones -->
              <td class="text-center" style="width: 5rem;" v-if="$can('ver', 'cotizaciones')">      
                  <VBtn
                    v-if="$can('ver', 'cotizaciones')"
                    icon
                    variant="text"
                    color="default"
                    size="x-small"
                    @click="printQuote(quote)">
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
                    v-if="$can('eliminar','cotizaciones')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(quote)">
                    <VTooltip
                      open-on-focus
                      location="top"
                      activator="parent"
                      >
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
          <tfoot v-show="!quotes.length">
            <tr>
              <td
                colspan="9"
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
      <VCard title="Eliminar Cotización">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar la cotización de <strong>{{ selectedQuote.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeQuote">
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
    subject: cotizaciones
</route>