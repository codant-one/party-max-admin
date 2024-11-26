<script setup>

import { useHomeImagesStores } from '@/stores/useHomeImages'
import { themeConfig } from '@themeConfig'
import AddNewHomeImageDrawer from './AddNewHomeImageDrawer.vue' 
import draggable from 'vuedraggable'

const homeImagesStores = useHomeImagesStores()

const homeImages = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalHomeImages = ref(0)
const isRequestOngoing = ref(true)
const isAddNewHomeImageDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedHomeImage = ref({})

const selectedType = ref(null)
const types = ref([
  {
    value: 1,
    text: 'Slider'
  },
  {
    value: 2,
    text: 'Banner'
  }
])

const enabled = ref(true)

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = homeImages.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = homeImages.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalHomeImages.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewHomeImageDrawerVisible.value)
        selectedHomeImage.value = {}
})

watchEffect(fetchData)

async function fetchData() {

  if(selectedType.value !== '' && selectedType.value !== null) {
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
    is_slider: selectedType.value
  }

  isRequestOngoing.value = true

  await homeImagesStores.fetchHomeImages(data)

  homeImages.value = homeImagesStores.getHomeImages
  totalPages.value = homeImagesStores.last_page
  totalHomeImages.value = homeImagesStores.homeImagesTotalCount

  isRequestOngoing.value = false
}

const editHomeImage = homeImageData => {
    isAddNewHomeImageDrawerVisible.value = true
    selectedHomeImage.value = { ...homeImageData }
}


const showDeleteDialog = homeImageData => {
  isConfirmDeleteDialogVisible.value = true
  selectedHomeImage.value = { ...homeImageData }
}

const removeHomeImage = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await homeImagesStores.deleteHomeImage({ ids: [selectedHomeImage.value.id] })
  selectedHomeImage.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Imagen eliminada!' : res.data.message,
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

const onStart = async (e) => {
  // console.log('oldIndex',e.oldIndex)
}

const onEnd = async (e) => {
  homeImagesStores.updateOrder(homeImages.value)
  fetchData()
}

const submitForm = async (homeImage, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    homeImage.data.append('_method', 'PUT')
    submitUpdate(homeImage)
    return
  }

  submitCreate(homeImage.data)
}

const submitCreate = homeImageData => {

  homeImagesStores.addHomeImage(homeImageData)
    .then((res) => {
      if (res.data.success) {
        advisor.value = {
          type: 'success',
          message: 'Imagen creada!',
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

const submitUpdate = homeImageData => {

    homeImagesStores.updateHomeImage(homeImageData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'Imagen actualizada!',
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
              
              <VAutocomplete
                id="selectType"
                v-model="selectedType"
                label="Tipos"
                :items="types"
                :item-title="item => item.text"
                :item-value="item => item.value"
                autocomplete="off"
                clearable
                style="width: 200px"
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
                v-if="$can('crear','home-imagenes')"
                prepend-icon="tabler-plus"
                @click="isAddNewHomeImageDrawerVisible = true">
                  Agregar Imagen
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ORDEN ID </th>
                <th scope="col"> SLIDER </th>
                <th scope="col"> URL </th>
                <th scope="col" class="w-100px"> IMAGEN </th>
                <th scope="col" v-if="$can('editar', 'home-imagenes') || $can('eliminar', 'home-imagenes')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <draggable 
              v-model="homeImages" 
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
                <td> 
                  <VChip
                    label
                    :color="element.is_slider === 1 ? 'success' : 'error'"
                    size="small">
                    {{  element.is_slider === 1 ? 'SI' : 'NO' }}
                  </VChip>  
                </td>
                <td> {{ element.url }} </td>
                <td>
                  <VImg
                    v-if="element.image !== null"
                    :src="themeConfig.settings.urlStorage + element.image"
                    :height="50"
                  />
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'home-imagenes') || $can('eliminar', 'home-imagenes')">      
                  <VBtn
                    v-if="$can('editar', 'home-images')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editHomeImage(element)">
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
                    v-if="$can('eliminar','home-images')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(element)">
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
            <tfoot v-show="!homeImages.length">
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

    <!-- 👉 Add New Home Images -->
    <AddNewHomeImageDrawer
      v-model:isDrawerOpen="isAddNewHomeImageDrawerVisible"
      :image="selectedHomeImage"
      @image-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Imagen">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar la imagen {{ selectedHomeImage.order_id }} del
          <strong>{{ selectedHomeImage.is_slider ? 'slider' : 'banner' }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeHomeImage">
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
    .w-100px {
      width: 100px;
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
    subject: home-imagenes
</route>