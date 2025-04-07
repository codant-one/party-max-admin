<script setup>

import { useCategoriesStores } from '@/stores/useCategories'
import { themeConfig } from '@themeConfig'
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'
import banner from '@images/banner.jpg'

const categoriesStores = useCategoriesStores()

const categories = ref([])
const searchQuery = ref('')
const searchFathers = ref(0)
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalCategories = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const isConfirmStateDialogVisible = ref(false)
const selectedCategory = ref({})
const category_type_id = ref()
const categoryTypes = ref([
  {id: 1, name: 'Productos'},
  {id: 2, name: 'Servicios'},
])
const state_id = ref()

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
    fathers: searchFathers.value === true ? 1 : 0,
    category_type_id: category_type_id.value ?? null,
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
  router.push({ name : 'dashboard-categories-edit-id', params: { id: categoryData.id } })
}

const openCategory = function (categoryData) {
  window.open(`${themeConfig.settings.urlDomain + 'categories/' + categoryData.slug}`)
}


const showDeleteDialog = categoryData => {
  isConfirmDeleteDialogVisible.value = true
  selectedCategory.value = { ...categoryData }
}

const resolveStatus = statusMsg => {
  if (statusMsg === 1)
    return {
      text: 'Inactivo',
      color: 'warning',
    }
  if (statusMsg === 2)
    return {
      text: 'Activo',
      color: 'success',
    }
}

const showStateDialog = (categoryData, id) => {
  isConfirmStateDialogVisible.value = true
  state_id.value = id
  selectedCategory.value = { ...categoryData }
}

const stateCategory = async state_id => {
  isConfirmStateDialogVisible.value = false

  let data = {
      state_id: state_id
  }

  let res = await categoriesStores.updateState(data, selectedCategory.value.id)
  selectedCategory.value = {}

  advisor.value = {
      type: res.data.success ? 'success' : 'error',
      message: res.data.success ? 'Categoría actualizada!' : res.data.message,
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

            <div style="width: 200px;">
              <VSelect
                label="Tipo de categoría"
                v-model="category_type_id"
                :items="categoryTypes"
                item-value="id"
                item-title="name"
                clearable
                clear-icon="tabler-x"
                no-data-text="No disponible"
                density="compact"
                variant="outlined"/>
            </div>
            <v-spacer />

            <div class="d-flex align-center flex-wrap gap-4">
              <VCheckbox
                v-model="searchFathers"
                label="Categorías Padres"
              />
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
                v-if="$can('crear','categorías')"
                prepend-icon="tabler-plus"
                :to="{ name: 'dashboard-categories-add' }">
                  Agregar Categoría
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr class="text-no-wrap">
                <th> NOMBRE </th>
                <th> SUBCATEGORÍA </th>
                <th> TIPO </th>
                <th class="text-end pe-4" v-if="$can('editar', 'categorías') || $can('eliminar', 'categorías')"> PRODUCTOS PUBLICADOS</th>
                <th class="pe-4"> STATUS </th>
                <th v-if="$can('editar', 'categorías') || $can('eliminar', 'categorías')">
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

                <td>
                  <div class="d-flex gap-x-3">
                    <VAvatar
                      variant="outlined"
                      rounded
                      size="38"
                    >
                      <VImg
                        v-if="category.banner === null && category.icon_subcategory === null"
                        :src="banner"
                        :alt="category.name"
                        cover
                      />
                      <VImg
                        v-if="category.banner !== null && category.icon_subcategory === null"
                        :src="themeConfig.settings.urlStorage + category.banner"
                        :alt="category.name"
                        cover
                      />
                      <VImg
                        v-if="category.banner === null && category.icon_subcategory !== null"
                        :src="themeConfig.settings.urlStorage + category.icon_subcategory"
                        :alt="category.name"
                        cover
                      />                      
                    </VAvatar>
                    <div class="d-block">
                      <span class="d-block text-base">
                        {{ category.name }}
                      </span>
                      <span class="d-block text-sm text-disabled">
                        {{ category.slug }}
                      </span>
                    </div>
                  </div>
                </td>
                <td> {{ category.category?.name }} </td>
                <td> {{ category.category_type?.name }} </td>
                <td v-if="$can('editar', 'categorías') || $can('eliminar', 'categorías')"> 
                  <h4 class="text-end">
                    {{ category.category_type_id === 1 ? 
                      (category.product_count).toLocaleString() :
                      (category.service_count).toLocaleString()  
                    }}
                  </h4> 
                </td>
                <td> 
                  <VChip
                      v-bind="resolveStatus(category.state_id)"
                      density="default"
                      label
                  />
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'categorías') || $can('eliminar', 'categorías')">      
                  <VBtn
                    v-if="category.category_id === null"
                    icon
                    variant="text"
                    color="default"
                    size="x-small">  
                    <VTooltip
                      open-on-focus
                      location="top"
                      activator="parent"
                      >
                      Abrir
                    </VTooltip>
                    <VIcon
                      icon="mdi-open-in-new"
                      :size="22"
                      @click="openCategory(category)"
                      />
                  </VBtn>
                  <VBtn
                    v-if="$can('editar', 'categorías') && category.state_id === 1"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showStateDialog(category, 2)">
                    <VTooltip
                        open-on-focus
                        location="top"
                        activator="parent">
                        Activar
                    </VTooltip>      
                    <VIcon
                        size="22"
                        icon="mdi-checkbox-marked-circle-outline" />
                </VBtn>

                <VBtn
                    v-if="$can('editar', 'categorías') && category.state_id === 2"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showStateDialog(category, 1)">
                    <VTooltip
                        open-on-focus
                        location="top"
                        activator="parent">
                        Inactivar
                    </VTooltip>      
                    <VIcon
                        size="22"
                        icon="mdi-close-circle-multiple-outline" />
                </VBtn>

                  <VBtn
                    v-if="$can('editar', 'categorías')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editCategory(category)">
                    <VTooltip
                      open-on-focus
                      location="top"
                      activator="parent"
                      >
                      Editar
                    </VTooltip>      
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

     <!-- 👉 Confirm State -->
     <VDialog
        v-model="isConfirmStateDialogVisible"
        persistent
        class="v-dialog-sm" >
        <!-- Dialog close btn -->
        
        <DialogCloseBtn @click="isConfirmStateDialogVisible = !isConfirmStateDialogVisible" />

        <!-- Dialog Content -->
        <VCard :title=" (state_id === 2 ? 'Activar ': 'Inactivar') + ' Categoría'">
            <VDivider class="mt-4"/>
            <VCardText>
                Está seguro de {{ state_id === 2 ? 'activar': 'inactivar' }}  la categoría <strong>{{ selectedCategory.name }}</strong>?.
            </VCardText>

            <VCardText class="d-flex justify-end gap-3 flex-wrap">
            <VBtn
                color="secondary"
                variant="tonal"
                @click="isConfirmStateDialogVisible = false">
                Cancelar
            </VBtn>
            <VBtn @click="stateCategory(state_id)">
                Aceptar
            </VBtn>
            </VCardText>
        </VCard>
    </VDialog>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Categoría">
        <VDivider class="mt-4"/>
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