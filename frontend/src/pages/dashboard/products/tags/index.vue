<script setup>

import { useTagsStores } from '@/stores/useTags'
import { excelParser } from '@/plugins/csv/excelParser'
import AddNewTagDrawer from './AddNewTagDrawer.vue' 

const tagsStores = useTagsStores()

const tags = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalTags = ref(0)
const isRequestOngoing = ref(true)
const isAddNewTagDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedTag = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = tags.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = tags.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalTags.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewTagDrawerVisible.value)
        selectedTag.value = {}
})

watchEffect(fetchData)

async function fetchData() {

  let data = {
    search: searchQuery.value,
    orderByField: 'id',
    orderBy: 'desc',
    tag_type_id: 1,
    limit: rowPerPage.value,
    page: currentPage.value
  }

  isRequestOngoing.value = true

  await tagsStores.fetchTags(data)

  tags.value = tagsStores.getTags
  totalPages.value = tagsStores.last_page
  totalTags.value = tagsStores.tagsTotalCount

  isRequestOngoing.value = false
}

const editTag = tagData => {
    isAddNewTagDrawerVisible.value = true
    selectedTag.value = { ...tagData }
}


const showDeleteDialog = tagData => {
  isConfirmDeleteDialogVisible.value = true
  selectedTag.value = { ...tagData }
}

const removeTag = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await tagsStores.deleteTag(selectedTag.value.id)
  selectedTag.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Tag eliminado!' : res.data.message,
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

const submitForm = async (tag, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    tag.data.append('_method', 'PUT')
    submitUpdate(tag)
    return
  }

  submitCreate(tag.data)
}


const submitCreate = tagData => {

    tagsStores.addTag(tagData)
        .then((res) => {
            if (res.data.success) {
                advisor.value = {
                    type: 'success',
                    message: 'Tag creado!',
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

const submitUpdate = tagData => {

    tagsStores.updateTag(tagData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'Tag actualizado!',
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
    tag_type_id: 1,
    limit: -1 
  }

  await tagsStores.fetchTags(data)

  let dataArray = [];
      
  tagsStores.getTags.forEach(element => {
    let data = {
      ID: element.id,
      NOMBRE: element.name
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "product-tags", "csv");

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
                v-if="$can('crear','tags')"
                prepend-icon="tabler-plus"
                @click="isAddNewTagDrawerVisible = true">
                  Agregar Tag
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
                <th scope="col" v-if="$can('editar', 'tags') || $can('eliminar', 'tags')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="tag in tags"
                :key="tag.id"
                style="height: 3.75rem;">

                <td> {{ tag.id }} </td>
                <td class="text-wrap"> {{ tag.name }} </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'tags') || $can('eliminar', 'tags')">      
                  <VBtn
                    v-if="$can('editar', 'tags')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editTag(tag)">
                              
                    <VIcon
                        size="22"
                        icon="tabler-edit" />
                  </VBtn>

                  <VBtn
                    v-if="$can('eliminar','tags')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(tag)">
                              
                    <VIcon
                      size="22"
                      icon="tabler-trash" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!tags.length">
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

    <!-- 👉 Add New Tag -->
    <AddNewTagDrawer
      v-model:isDrawerOpen="isAddNewTagDrawerVisible"
      :tag="selectedTag"
      @tag-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Tag">
        <VCardText>
          Está seguro de eliminar el Tag de <strong>{{ selectedTag.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeTag">
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
    subject: tags
</route>