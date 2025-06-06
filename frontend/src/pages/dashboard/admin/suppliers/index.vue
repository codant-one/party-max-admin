<script setup>

import { useSuppliersStores } from '@/stores/useSuppliers'
import { useCountriesStores } from '@/stores/useCountries'
import { excelParser } from '@/plugins/csv/excelParser'
import { themeConfig } from '@themeConfig'
import { avatarText, formatNumber } from '@/@core/utils/formatters'
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'

const suppliersStores = useSuppliersStores()
const countriesStores = useCountriesStores()

const suppliers = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalSuppliers = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const selectedSupplier = ref({})
const listCountries = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = suppliers.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = suppliers.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalSuppliers.value } registros`
})

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
})

onMounted(async () => {

  await countriesStores.fetchCountries()

  loadCountries()
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

  await suppliersStores.fetchSuppliers(data)

  suppliers.value = suppliersStores.getSuppliers
  totalPages.value = suppliersStores.last_page
  totalSuppliers.value = suppliersStores.suppliersTotalCount

  isRequestOngoing.value = false
}

const editSupplier = supplierData => {
  router.push({ name : 'dashboard-admin-suppliers-edit-id', params: { id: supplierData.id } })
}

const showDeleteDialog = supplierData => {
  isConfirmDeleteDialogVisible.value = true
  selectedSupplier.value = { ...supplierData }
}

const seeSupplier = supplierData => {
  router.push({ name : 'dashboard-admin-suppliers-id', params: { id: supplierData.id } })
}

const removeSupplier = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await suppliersStores.deleteSupplier(selectedSupplier.value.id)
  selectedSupplier.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Proveedor eliminado!' : res.data.message,
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

  let data = { limit: -1 }

  await suppliersStores.fetchSuppliers(data)

  let dataArray = [];
      
  suppliersStores.getSuppliers.forEach(element => {

    let data = {
      ID: element.id,
      CONTACTO: element.user.name + ' ' + (element.user.last_name ?? ''),
      EMAIL: element.user.email,
      EMPRESA: element.company_name ?? '',
      DOCUMENTO: (element.document === null) ? '' : (element.document?.type.code + ': ' + element.document?.main_document),
      PAÍS:  element.user.user_detail.province.country.name,
      PRODUCTOS_PUBLICADOS:  element.product_count,
      SERVICIOS_PUBLICADOS:  element.service_count,
      TOTAL_VENTAS:  formatNumber(element.sales) ?? '0.00',
      TOTAL_SERVICES:  formatNumber(element.services) ?? '0.00'
    }

    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "suppliers", "csv");

  isRequestOngoing.value = false

}

const getFlagCountry = country => {
  let val = listCountries.value.find(item => {
    return item.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") === country.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  })

  if(val)
    return 'https://hatscripts.github.io/circle-flags/flags/'+val.iso.toLowerCase()+'.svg'
  else
    return ''
}
</script>

<template>
  <section>
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
              class="mb-0"/>
          </VCardText>
        </VCard>
      </VDialog>

      <v-col cols="12">
        <v-alert
          v-if="advisor.show"
          :type="advisor.type"
          class="mb-6">
            
          {{ advisor.message }}
        </v-alert>

        <Toaster />

        <v-card title="">
          <v-card-text class="d-flex flex-wrap py-4 gap-4">
            <div
              class="me-3"
              style="width: 80px;">
              
              <VSelect
                v-model="rowPerPage"
                density="compact"
                variant="outlined"
                :items="[10, 20, 30, 50]"/>
            </div>

            <div class="d-flex align-center">
              <VBtn
                variant="tonal"
                color="secondary"
                prepend-icon="tabler-file-export"
                @click="downloadCSV">
                Exportar
              </VBtn>
            </div>

            <v-spacer />

            <div class="d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div class="search">
                <VTextField
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"
                  clearable
                />
              </div>

              <!-- 👉 Add user button -->
              <v-btn
                v-if="$can('crear','proveedores')"
                prepend-icon="tabler-plus"
                :to="{ name: 'dashboard-admin-suppliers-add' }">
                  Agregar Proveedor
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <!-- <th scope="col"> #ID </th> -->
                <th scope="col"> EMPRESA </th>
                <th scope="col"> CONTACTO </th>
                <!-- <th scope="col"> PAÍS </th> -->
                <th scope="col"> PRODUCTOS PUB. </th>
                <th scope="col"> SERVICIOS PUB. </th>
                <th scope="col"> TOTAL PRODUCTOS </th>
                <th scope="col"> TOTAL SERVICIOS </th>
                <th scope="col" v-if="$can('editar', 'proveedores') || $can('eliminar', 'proveedores')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="supplier in suppliers"
                :key="supplier.id"
                style="height: 3.75rem;">

                <!-- <td> {{ supplier.id }} </td> -->
                <td class="text-wrap w-25">
                  <div class="d-flex align-center gap-x-3">
                    <VAvatar
                      :variant="supplier.user.avatar ? 'outlined' : 'tonal'"
                      size="38"
                      >
                      <VImg
                        v-if="supplier.user.avatar"
                        style="border-radius: 50%;"
                        :src="themeConfig.settings.urlStorage + supplier.user.avatar"
                      />
                        <span v-else>{{ avatarText(supplier.user.name) }}</span>
                    </VAvatar>
                    <div class="d-flex flex-column">
                      <span class="font-weight-medium cursor-pointer text-primary" @click="seeSupplier(supplier)">
                        {{ supplier.company_name }}
                      </span>
                      <span class="text-sm text-disabled" v-if="supplier.document">
                        {{ supplier.document?.type.code }}: {{ supplier.document?.main_document }}
                      </span>
                    </div>
                  </div>
                </td>
                <td class="text-wrap w-25">
                  <div class="d-flex flex-column">
                    <span class="font-weight-medium">
                      {{ supplier.user.name }} {{ supplier.user.last_name ?? '' }} 
                    </span>
                    <span class="text-sm text-disabled">{{ supplier.user.email }}</span>
                  </div>
                </td>
                <!-- <td class="text-wrap w-15"> 
                  <VAvatar
                    start
                    size="25"
                    :image="getFlagCountry(supplier.user.user_detail.province.country.name)"
                  />
                  <span class="text-body-2 ms-2">
                    {{ supplier.user.user_detail.province.country.name }} 
                  </span>
                </td> -->
                <td class="text-wrap w-15">
                  {{ supplier.product_count }}
                </td>
                <td class="text-wrap w-15">
                  {{ supplier.service_count }}
                </td>
                <td>
                  <span class="text-body-1 font-weight-medium text-high-emphasis w-15">
                    ${{ formatNumber(supplier.sales) ?? '0.00' }}
                  </span>
                </td>
                <td>
                  <span class="text-body-1 font-weight-medium text-high-emphasis w-15">
                    ${{ formatNumber(supplier.services) ?? '0.00' }}
                  </span>
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'proveedores') || $can('eliminar', 'proveedores')">      
                  <VBtn
                    v-if="$can('ver', 'proveedores')"
                    icon
                    variant="text"
                    color="default"
                    size="x-small"
                    @click="seeSupplier(supplier)">
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
                    v-if="$can('editar', 'proveedores')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editSupplier(supplier)"
                    >
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
                    v-if="$can('eliminar','proveedores')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(supplier)">
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
            <tfoot v-show="!suppliers.length">
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
        </v-card>
      </v-col>
    </v-row>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Proveedor">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el Proveedor <strong>{{ selectedSupplier.user.name }} {{ selectedSupplier.user.last_name ?? '' }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeSupplier">
              Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<style scope>
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
    subject: proveedores
</route>