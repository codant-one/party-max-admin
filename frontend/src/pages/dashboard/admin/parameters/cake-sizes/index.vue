<script setup>

import { useCakeSizesStores } from '@/stores/useCakeSizes'
import { useCakeTypesStores } from '@/stores/useCakeTypes'
import { excelParser } from '@/plugins/csv/excelParser'
import AddNewCakeSizeDrawer from './AddNewCakeSizeDrawer.vue' 

const cakeSizesStores = useCakeSizesStores()
const cakeTypesStores = useCakeTypesStores()

const types = ref([])
const selectedType = ref('')
const cakeSizes = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalCakeSizes = ref(0)
const isRequestOngoing = ref(true)
const isAddNewCakeSizeDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedCakeSize = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = cakeSizes.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = cakeSizes.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalCakeSizes.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewCakeSizeDrawerVisible.value)
        selectedCakeSize.value = {}
})

watchEffect(fetchData)

async function fetchData() {

  let data = {
    search: searchQuery.value,
    orderByField: 'id',
    orderBy: 'desc',
    cake_type_id: selectedType.value,
    limit: rowPerPage.value,
    page: currentPage.value
  }

  isRequestOngoing.value = true

  await cakeSizesStores.fetchCakeSizes(data)
  await cakeTypesStores.fetchCakeTypes(data)
  
  types.value = cakeTypesStores.getCakeTypes
  cakeSizes.value = cakeSizesStores.getCakeSizes
  totalPages.value = cakeSizesStores.last_page
  totalCakeSizes.value = cakeSizesStores.cakeSizesTotalCount

  isRequestOngoing.value = false
}

const editCakeSize = cakeSizeData => {
    isAddNewCakeSizeDrawerVisible.value = true
    selectedCakeSize.value = { ...cakeSizeData }
}


const showDeleteDialog = cakeSizeData => {
  isConfirmDeleteDialogVisible.value = true
  selectedCakeSize.value = { ...cakeSizeData }
}

const removeCakeSize = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await cakeSizesStores.deleteCakeSize(selectedCakeSize.value.id)
  selectedCakeSize.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Tamaño de torta eliminado!' : res.data.message,
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

const submitForm = async (cakeSize, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    cakeSize.data.append('_method', 'PUT')
    submitUpdate(cakeSize)
    return
  }

  submitCreate(cakeSize.data)
}


const submitCreate = cakeSizeData => {

    cakeSizesStores.addCakeSize(cakeSizeData)
        .then((res) => {
            if (res.data.success) {
                advisor.value = {
                    type: 'success',
                    message: 'Tamaño de torta creado!',
                    show: true
                }
                fetchData()
            }
            isRequestOngoing.value = false
        })
        .catch((err) => {
            advisor.value = {
                type: 'error',
                message: err.message,
                show: true
            }
            isRequestOngoing.value = false
        })

    setTimeout(() => {
        advisor.value = {
            type: '',
            message: '',
            show: false
        }
    }, 3000)
}

const submitUpdate = cakeSizeData => {

    cakeSizesStores.updateCakeSize(cakeSizeData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'Tamaño de torta actualizado!',
                    show: true
                }
                fetchData()
            }
            isRequestOngoing.value = false
        })
        .catch((err) => {
            advisor.value = {
                type: 'error',
                message: err.message,
                show: true
            }
            isRequestOngoing.value = false
        })

    setTimeout(() => {
        advisor.value = {
            type: '',
            message: '',
            show: false
        }
    }, 3000)
}

const downloadCSV = async () => {

  isRequestOngoing.value = true

  let data = { 
    cake_type_id: 1,
    limit: -1 
  }

  await cakeSizeStores.fetchCakeSizes(data)

  let dataArray = [];
      
  cakeSizesStores.getCakeSizes.forEach(element => {
    let data = {
      ID: element.id,
      NOMBRE: element.name
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "cakeSizes", "csv");

  isRequestOngoing.value = false

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

              <div class="search d-flex">
                <VAutocomplete
                  id="selectType"
                  v-model="selectedType"
                  label="Tipo de torta"
                  :items="types"
                  :item-title="item => item.name"
                  :item-value="item => item.id"
                  autocomplete="off"
                  clearable
                  class="me-5" />

                <VTextField
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"
                  clearable
                />
              </div>

              <!-- 👉 Add user button -->
              <v-btn
                v-if="$can('crear','parámetros')"
                prepend-icon="tabler-plus"
                @click="isAddNewCakeSizeDrawerVisible = true">
                  Agregar tamaño de torta
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ID </th>
                <th scope="col"> TIPO </th>
                <th scope="col"> NOMBRE </th>
                <th scope="col" v-if="$can('editar', 'parámetros') || $can('eliminar', 'parámetros')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="cakeSize in cakeSizes"
                :key="cakeSize.id"
                style="height: 3.75rem;">

                <td> {{ cakeSize.id }} </td>
                <td class="text-wrap"> {{ cakeSize.cake_type.name }} </td>
                <td class="text-wrap"> {{ cakeSize.name }} </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'parámetros') || $can('eliminar', 'parámetros')">      
                  <VBtn
                    v-if="$can('editar', 'parámetros')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editCakeSize(cakeSize)">
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
                    v-if="$can('eliminar','parámetros')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(cakeSize)">
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
            <tfoot v-show="!cakeSizes.length">
              <tr>
                <td
                  colspan="4"
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

    <!-- 👉 Add New CakeSize -->
    <AddNewCakeSizeDrawer
      v-if="types.length > 0"
      v-model:isDrawerOpen="isAddNewCakeSizeDrawerVisible"
      :cakeSize="selectedCakeSize"
      :types="types"
      @cakeSize-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar tamaño de torta">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el tamaño de torta <strong>{{ selectedCakeSize.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeCakeSize">
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
    subject: parámetros
</route>