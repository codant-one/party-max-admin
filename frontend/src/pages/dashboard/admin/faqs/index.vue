<script setup>

import { useCategoriesStores } from '@/stores/useFaqCategories'
import { useFaqsStores } from '@/stores/useFaqs'
import { excelParser } from '@/plugins/csv/excelParser'
import AddNewFaqDrawer from './AddNewFaqDrawer.vue' 
import draggable from 'vuedraggable'

const categoriesStores = useCategoriesStores()
const faqsStores = useFaqsStores()

const selectedCategory = ref('')
const categories = ref([])

const faqs = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalFaqs = ref(0)
const isRequestOngoing = ref(true)
const isAddNewFaqDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedFaq = ref({})

const enabled = ref(true)

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = faqs.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = faqs.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalFaqs.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewFaqDrawerVisible.value)
        selectedFaq.value = {}
})

watchEffect(fetchData)

async function fetchData() {

  if(selectedCategory.value !== '' && selectedCategory.value !== null) {
    enabled.value = false
  } else {
    enabled.value = true
  }

  let data = {
    search: searchQuery.value,
    orderByField: 'order_id',
    orderBy: 'asc',
    limit: rowPerPage.value,
    page: currentPage.value,
    category_id: selectedCategory.value
  }

  isRequestOngoing.value = true

  await faqsStores.fetchFaqs(data)
  await categoriesStores.fetchCategories({limit: -1})

  categories.value = categoriesStores.getCategories
  faqs.value = faqsStores.getFaqs
  totalPages.value = faqsStores.last_page
  totalFaqs.value = faqsStores.faqsTotalCount

  isRequestOngoing.value = false
}

const editFaq = faqData => {
    isAddNewFaqDrawerVisible.value = true
    selectedFaq.value = { ...faqData }
}


const showDeleteDialog = faqData => {
  isConfirmDeleteDialogVisible.value = true
  selectedFaq.value = { ...faqData }
}

const removeFaq = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await faqsStores.deleteFaq(selectedFaq.value.id)
  selectedFaq.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'FAQ eliminada!' : res.data.message,
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

const submitForm = async (faq, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    faq.data.append('_method', 'PUT')
    submitUpdate(faq)
    return
  }

  submitCreate(faq.data)
}


const submitCreate = faqData => {

    faqsStores.addFaq(faqData)
        .then((res) => {
            if (res.data.success) {
                advisor.value = {
                    type: 'success',
                    message: 'FAQ creada!',
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

const submitUpdate = faqData => {

    faqsStores.updateFaq(faqData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'FAQ actualizada!',
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

  await faqsStores.fetchFaqs(data)

  let dataArray = [];
      
  faqsStores.getFaqs.forEach(element => {
    let data = {
      ID: element.id,
      TÍTULO: element.title,
      DESCRIPCIÓN: element.text,
      CATEGORÍA:  element.category.name
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "faqs", "csv");

  isRequestOngoing.value = false

}

const onStart = async (e) => {
  // console.log('oldIndex',e.oldIndex)
}

const onEnd = async (e) => {
  faqsStores.updateOrder(faqs.value)
  fetchData()
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
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
              <VAutocomplete
                id="selectCategory"
                v-model="selectedCategory"
                label="Categoría"
                autocomplete="off"
                clearable
                :items="categories"
                :item-title="item => item.name"
                :item-value="item => item.id"
                :menu-props="{ maxHeight: '300px' }"
                style="width: 15rem;">
                <template v-slot:item="{ props, item }">
                  <v-list-item
                    v-bind="props"
                    :title="item?.raw?.name"
                    :style="{ 
                    paddingLeft: `${(item?.raw?.level) * 20}px !important`,
                    paddingTop: `0 !important`,
                    height: `10px !important`
                    }"
                  >
                    <template v-slot:prepend="{ isActive }">
                      <v-list-item-action start>
                        <v-checkbox-btn :model-value="isActive"></v-checkbox-btn>
                      </v-list-item-action>
                    </template>
                  </v-list-item>
                </template>
                <template v-slot:append-item>
                  <v-divider class="mt-2"></v-divider>
                  <v-list-item title="Cerrar Opciones" class="text-right">
                    <template v-slot:append>
                      <VBtn
                        size="small"
                        variant="plain"
                        icon="tabler-x"
                        color="black"
                        :ripple="false"
                        @click="closeDropdown"/>
                    </template>
                  </v-list-item>
                </template>
              </VAutocomplete>
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
                v-if="$can('crear','faqs')"
                prepend-icon="tabler-plus"
                @click="isAddNewFaqDrawerVisible = true">
                  Agregar FAQ
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ORDEN ID </th>
                <th scope="col"> TITULO </th>
                <th scope="col"> DESCRIPCIÓN </th>
                <th scope="col"> CATEGORÍA </th>
                <th scope="col" v-if="$can('editar', 'faqs') || $can('eliminar', 'faqs')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <draggable 
              v-model="faqs" 
              tag="tbody"
              item-key="id"
              :disabled="enabled"
              @start="onStart"
              @end="onEnd">
              <template #item="{ element }">
                <tr 
                  style="height: 3.75rem;"
                  :class="!enabled ? 'draggable-item' : ''">
                  <td> {{ element.order_id }} </td>
                  <td class="text-wrap"> {{ element.title }} </td>
                  <td class="text-wrap"> {{ element.description }} </td>
                  <td class="text-wrap"> {{ element.category.name }} </td>
                  <!-- 👉 Acciones -->
                  <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'faqs') || $can('eliminar', 'faqs')">      
                    <VBtn
                      v-if="$can('editar', 'faqs')"
                      icon
                      size="x-small"
                      color="default"
                      variant="text"
                      @click="editFaq(element)">
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
                      v-if="$can('eliminar','faqs')"
                      icon
                      size="x-small"
                      color="default"
                      variant="text"
                      @click="showDeleteDialog(faq)">
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
              </template>
            </draggable>
            <!-- 👉 table footer  -->
            <tfoot v-show="!faqs.length">
              <tr>
                <td
                  colspan="5"
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

    <!-- 👉 Add New Faq -->
    <AddNewFaqDrawer
      v-model:isDrawerOpen="isAddNewFaqDrawerVisible"
      :faq="selectedFaq"
      @faq-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar FAQ">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar la FAQ de <strong>{{ selectedFaq.title }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeFaq">
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

    .draggable-item:hover {
      background-color: #e9ecef; /* Color de fondo al hacer hover */
      cursor: move; /* Cambia el cursor para indicar que el elemento es interactivo */
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
    subject: faqs
</route>