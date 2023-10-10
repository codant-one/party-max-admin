<script setup>

import { useFaqsStores } from '@/stores/useFaqs'
import { ref } from "vue"
import { themeConfig } from '@themeConfig'
import AddNewFaqDrawer from './AddNewFaqDrawer.vue' 

const faqsStores = useFaqsStores()

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

  let data = {
    search: searchQuery.value,
    orderByField: 'id',
    orderBy: 'desc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  isRequestOngoing.value = true

  await faqsStores.fetchFaqs(data)

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
    message: res.data.success ? 'FAQ eliminada con éxito!' : res.data.message,
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
                    message: 'FAQ creada con éxito',
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
                    message: 'FAQ actualizada con éxito',
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
              <div class="search">
                <v-text-field
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"/>
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
                <th scope="col"> #ID </th>
                <th scope="col"> TITULO </th>
                <th scope="col"> DESCRIPCIÓN </th>
                <th scope="col" v-if="$can('editar', 'faqs') || $can('eliminar', 'faqs')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="faq in faqs"
                :key="faq.id"
                style="height: 3.75rem;">

                <td> {{ faq.id }} </td>
                <td> {{ faq.title }} </td>
                <td> {{ faq.description }} </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'faqs') || $can('eliminar', 'faqs')">      
                  <VBtn
                    v-if="$can('editar', 'faqs')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editFaq(faq)">
                              
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
                              
                    <VIcon
                      size="22"
                      icon="tabler-trash" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!faqs.length">
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

    @media(min-width: 991px){
        .search {
            width: 30rem;
        }
    }
</style>
<route lang="yaml">
  meta:
    action: ver
    subject: FAQs
</route>