<script setup>

import { useBrandsStores } from '@/stores/useBrands'
import { ref } from "vue"
import { excelParser } from '@/plugins/csv/excelParser'
import AddNewBrandDrawer from './AddNewBrandDrawer.vue' 

const brandsStores = useBrandsStores()

const brands = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalBrands = ref(0)
const isRequestOngoing = ref(true)
const isAddNewBrandDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedBrand = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = brands.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = brands.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalBrands.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewBrandDrawerVisible.value)
        selectedBrand.value = {}
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

  await brandsStores.fetchBrands(data)

  brands.value = brandsStores.getBrands
  totalPages.value = brandsStores.last_page
  totalBrands.value = brandsStores.brandsTotalCount

  isRequestOngoing.value = false
}

const editBrand = brandData => {
    isAddNewBrandDrawerVisible.value = true
    selectedBrand.value = { ...brandData }
}


const showDeleteDialog = brandData => {
  isConfirmDeleteDialogVisible.value = true
  selectedBrand.value = { ...brandData }
}

const removeBrand = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await brandsStores.deleteBrand(selectedBrand.value.id)
  selectedBrand.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Marca eliminada!' : res.data.message,
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

const submitForm = async (brand, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    brand.data.append('_method', 'PUT')
    submitUpdate(brand)
    return
  }

  submitCreate(brand.data)
}


const submitCreate = brandData => {

    brandsStores.addBrand(brandData)
        .then((res) => {
            if (res.data.success) {
                advisor.value = {
                    type: 'success',
                    message: 'Marca creada!',
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

const submitUpdate = brandData => {

    brandsStores.updateBrand(brandData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'Marca actualizada!',
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

  let data = { limit: -1 }

  await brandsStores.fetchBrands(data)

  let dataArray = [];
      
  brandsStores.getBrands.forEach(element => {
    let data = {
      ID: element.id,
      NOMBRE: element.name
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "brands", "csv");

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
                <v-text-field
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"/>
              </div>

              <!-- 👉 Add user button -->
              <v-btn
                v-if="$can('crear','marcas')"
                prepend-icon="tabler-plus"
                @click="isAddNewBrandDrawerVisible = true">
                  Agregar Marca
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
                <th scope="col" v-if="$can('editar', 'marcas') || $can('eliminar', 'marcas')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="brand in brands"
                :key="brand.id"
                style="height: 3.75rem;">

                <td> {{ brand.id }} </td>
                <td class="text-wrap"> {{ brand.name }} </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'marcas') || $can('eliminar', 'marcas')">      
                  <VBtn
                    v-if="$can('editar', 'marcas')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editBrand(brand)">
                              
                    <VIcon
                        size="22"
                        icon="tabler-edit" />
                  </VBtn>

                  <VBtn
                    v-if="$can('eliminar','marcas')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(brand)">
                              
                    <VIcon
                      size="22"
                      icon="tabler-trash" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!brands.length">
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

    <!-- 👉 Add New Brand -->
    <AddNewBrandDrawer
      v-model:isDrawerOpen="isAddNewBrandDrawerVisible"
      :brand="selectedBrand"
      @brand-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Marca">
        <VCardText>
          Está seguro de eliminar la marca de <strong>{{ selectedBrand.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeBrand">
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
    subject: marcas
</route>