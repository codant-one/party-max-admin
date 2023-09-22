<script setup>

import { useCategoriesStores } from '@/stores/useCategories'
import AddNewCategoryDrawer from './AddNewCategoryDrawer.vue' 
import { ref } from "vue"

const categoriesStores = useCategoriesStores()

const categories = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalCategories = ref(0)
const isRequestOngoing = ref(true)
const isAddNewCategoryDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedCategory = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = categories.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = categories.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalCategories.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewCategoryDrawerVisible.value)
        selectedCategory.value = {}
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

  await categoriesStores.fetchCategories(data)

  categories.value = categoriesStores.getCategories
  totalPages.value = categoriesStores.last_page
  totalCategories.value = categoriesStores.categoriesTotalCount

  isRequestOngoing.value = false
}


const showDeleteDialog = categoryData => {
  isConfirmDeleteDialogVisible.value = true
  selectedCategory.value = { ...categoryData }
}

const removeCategory = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await categoriesStores.deleteCategory({ ids: [selectedCategory.value.id] })
  selectedCategory.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Categoría eliminada con éxito!' : res.data.message,
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

const submitForm = async (category, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    category.data.append('_method', 'PUT')
    submitUpdate(category)
    return
  }

  submitCreate(category.data)
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

            <v-spacer />

            <div class="d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 10rem;">
                <v-text-field
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"/>
              </div>

              <!-- 👉 Add user button -->
              <v-btn
                v-if="$can('crear','categorías')"
                prepend-icon="tabler-plus"
                @click="isAddNewCategoryDrawerVisible = true">
                  Agregar Categoría
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
                <th scope="col"> DESCRIPCIÓN </th>
                <th scope="col"> SUBCATEGORÍA </th>
                <th scope="col"> SLUG </th>
                <th scope="col" v-if="$can('editar', 'categorías') || $can('eliminar', 'categorías')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="category in categories"
                :key="category.id"
                style="height: 3.75rem;">

                <td> {{ category.id }} </td>
                <td> {{ category.name }} </td>
                <td> {{ category.description }} </td>
                <td> {{ category.category?.name }} </td>
                <td> {{ category.slug }} </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'categorías') || $can('eliminar', 'categorías')">      
                  <VBtn
                    v-if="$can('editar', 'categorías')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text">
                              
                    <VIcon
                        size="22"
                        icon="tabler-edit" />
                  </VBtn>

                  <VBtn
                    v-if="$can('eliminar','categorías')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(category)">
                              
                    <VIcon
                      size="22"
                      icon="tabler-trash" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!categories.length">
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

    <!-- 👉 Add New Category -->
    <AddNewCategoryDrawer
      v-model:isDrawerOpen="isAddNewCategoryDrawerVisible"
      :category="selectedCategory"
      @category-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Categoría">
        <VCardText>
          Está seguro de eliminar la categoría de <strong>{{ selectedCategory.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeCategory">
              Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<route lang="yaml">
  meta:
    action: ver
    subject: categorías
</route>