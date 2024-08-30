<script setup>

import { useCakeTypesStores } from '@/stores/useCakeTypes'
import { excelParser } from '@/plugins/csv/excelParser'
import AddNewCakeTypeDrawer from './AddNewCakeTypeDrawer.vue' 

const cakeTypesStores = useCakeTypesStores()

const cakeTypes = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalCakeTypes = ref(0)
const isRequestOngoing = ref(true)
const isAddNewCakeTypeDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedCakeType = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = cakeTypes.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = cakeTypes.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalCakeTypes.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewCakeTypeDrawerVisible.value)
        selectedCakeType.value = {}
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

  await cakeTypesStores.fetchCakeTypes(data)

  cakeTypes.value = cakeTypesStores.getCakeTypes
  totalPages.value = cakeTypesStores.last_page
  totalCakeTypes.value = cakeTypesStores.cakeTypesTotalCount

  isRequestOngoing.value = false
}

const editCakeType = cakeTypeData => {
    isAddNewCakeTypeDrawerVisible.value = true
    selectedCakeType.value = { ...cakeTypeData }
}


const showDeleteDialog = cakeTypeData => {
  isConfirmDeleteDialogVisible.value = true
  selectedCakeType.value = { ...cakeTypeData }
}

const removeCakeType = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await cakeTypesStores.deleteCakeType(selectedCakeType.value.id)
  selectedCakeType.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Relleno eliminado!' : res.data.message,
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

const submitForm = async (cakeType, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    cakeType.data.append('_method', 'PUT')
    submitUpdate(cakeType)
    return
  }

  submitCreate(cakeType.data)
}


const submitCreate = cakeTypeData => {

    cakeTypesStores.addCakeType(cakeTypeData)
        .then((res) => {
            if (res.data.success) {
                advisor.value = {
                    type: 'success',
                    message: 'Relleno creado!',
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

const submitUpdate = cakeTypeData => {

    cakeTypesStores.updateCakeType(cakeTypeData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'Relleno actualizado!',
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
    limit: -1 
  }

  await cakeTypesStores.fetchCakeTypes(data)

  let dataArray = [];
      
  cakeTypeStores.getCakeTypes.forEach(element => {
    let data = {
      ID: element.id,
      NOMBRE: element.name
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "cakeTypes", "csv");

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
                v-if="$can('crear','atributos')"
                prepend-icon="tabler-plus"
                @click="isAddNewCakeTypeDrawerVisible = true">
                  Agregar Relleno
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ID </th>
                <th scope="col"> NOMBRE </th>
                <th scope="col" v-if="$can('editar', 'atributos') || $can('eliminar', 'atributos')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="cakeType in cakeTypes"
                :key="cakeType.id"
                style="height: 3.75rem;">

                <td> {{ cakeType.id }} </td>
                <td class="text-wrap"> {{ cakeType.name }} </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'atributos') || $can('eliminar', 'atributos')">      
                  <VBtn
                    v-if="$can('editar', 'atributos')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editCakeType(cakeType)">
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
                    v-if="$can('eliminar','atributos')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(cakeType)">
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
            <tfoot v-show="!cakeTypes.length">
              <tr>
                <td
                  colspan="3"
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

    <!-- 👉 Add New CakeType -->
    <AddNewCakeTypeDrawer
      v-model:isDrawerOpen="isAddNewCakeTypeDrawerVisible"
      :cakeType="selectedCakeType"
      @cakeType-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Relleno">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar la relleno de <strong>{{ selectedCakeType.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeCakeType">
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
    subject: atributos
</route>