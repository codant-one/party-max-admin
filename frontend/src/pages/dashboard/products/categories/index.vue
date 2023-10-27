<script setup>

import { useCategoriesStores } from '@/stores/useCategories'
import { ref } from "vue"
import { themeConfig } from '@themeConfig'
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'

const categoriesStores = useCategoriesStores()

const categories = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalCategories = ref(0)
const isRequestOngoing = ref(true)
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

const editCategory = categoryData => {
  router.push({ name : 'dashboard-products-categories-edit-id', params: { id: categoryData.id } })
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
    message: res.data.success ? 'Categoría eliminada!' : res.data.message,
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
                v-if="$can('crear','categorías')"
                prepend-icon="tabler-plus"
                :to="{ name: 'dashboard-products-categories-add' }">
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
                <th scope="col"> SUBCATEGORÍA </th>
                <th scope="col"> SLUG </th>
                <th scope="col"> BANNER </th>
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
                <td> {{ category.category?.name }} </td>
                <td> {{ category.slug }} </td>
                <td>
                    <VImg
                      class="me-6"
                        v-if="category.banner !== null"
                        :src="themeConfig.settings.urlStorage + category.banner"
                        :height="50"
                        aspect-ratio="1/1"
                    />
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'categorías') || $can('eliminar', 'categorías')">      
                  <VBtn
                    v-if="$can('editar', 'categorías')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editCategory(category)">
                              
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
    subject: categorías
</route>