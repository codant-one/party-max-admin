<script setup>

import { themeConfig } from '@themeConfig'
import { useClipboard } from '@vueuse/core'
import { useServicesStores } from '@/stores/useServices'
import { useCategoriesStores } from '@/stores/useCategories'
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'
import detailsService from "@/components/services/detailsService.vue";
import show from "@/components/services/show.vue";

const servicesStores = useServicesStores()
const categoriesStores = useCategoriesStores()
const cp = useClipboard()

const myServicesList = ref([])
const services = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalServices = ref(0)
const isRequestOngoing = ref(true)
const selectedService = ref({})

const isServiceDetailDialog = ref(false)
const isConfirmDeleteDialogVisible = ref(false)

const favourite = ref(null)
const archived = ref(null)
const discarded = ref(null)

const selectedStatus = ref(3)
const selectedCategory = ref()

const isConfirmApproveDialogVisible = ref(false)
const state_id = ref(3)
const currentTab = ref(0)

const rol = ref(null)
const userData = ref(null)

const resolveStatus = statusMsg => {
  if (statusMsg === 3)
    return {
      text: 'Publicado',
      color: 'success',
    }
  if (statusMsg === 5)
    return {
      text: 'Eliminado',
      color: 'warning',
    }
  if (statusMsg === 4)
    return {
      text: 'Pendiente',
      color: 'error',
    }

  if (statusMsg === 6)
    return {
      text: 'Rechazado',
      color: 'warning',
    }
}
const status = ref([
  {
    title: 'Publicado',
    value: 3
  },
  {
    title: 'Pendiente',
    value: 4
  },
  {
    title: 'Eliminado',
    value: 5
  },
  {
    title: 'Rechazado',
    value: 6
  },
])

const categories = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = services.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = services.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalServices.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
})

watchEffect(fetchData)

async function fetchData() {

  if(favourite.value === 0 && archived.value === 0 && discarded.value === 0) {
    favourite.value = null
    archived.value = null
    discarded.value = null
  }

  let data = {
    search: searchQuery.value,
    favourite: favourite.value,
    archived: archived.value,
    discarded: discarded.value,
    state_id: selectedStatus.value,
    category_id: selectedCategory.value,
    orderByField: 'id',
    orderBy: 'desc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  isRequestOngoing.value = true
  
  let info = { 
    limit: -1, 
    category_type_id: 2
  }

  await categoriesStores.fetchCategoriesOrder(info)

  categories.value = categoriesStores.getCategories

  await servicesStores.fetchServices(data)

  services.value =  []
  myServicesList.value =  []
  myServicesList.value = servicesStores.getServices

  myServicesList.value.forEach(element =>
    services.value.push({
      id: element.id,
      favourite: element.favourite,
      discarded: element.discarded,
      user: element.user,
      state: element.state,
      archived: element.archived,            
      title: element.name,
      image: element.image,
      price: element.cupcakes.length > 0 ? element.cupcakes[0].price : element.price,
      originalLink: themeConfig.settings.urlDomain + 'services/' + element.slug,
      categories: element.categories.map(item => item.category.name),// Utiliza map para extraer los nombres de las categorías
      rating: element.rating,//agregar mas adelante informacion
      comments: 0,//agregar mas adelante informacion
      sales: element.sales,//agregar mas adelante informacion
      selling_price: 0,//agregar mas adelante informacion,
      likes: element.likes
    })
  );

  totalPages.value = servicesStores.last_page
  totalServices.value = servicesStores.servicesTotalCount

  userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
  rol.value = userData.value.roles[0].name

  isRequestOngoing.value = false
}

const showStateDialog = (serviceData, id) => {
    isConfirmApproveDialogVisible.value = true
    state_id.value = id
    selectedService.value = { ...serviceData }
}

const showDeleteDialog = serviceData => {
  isConfirmDeleteDialogVisible.value = true
  selectedService.value = { ...serviceData }
}

const stateService = async state_id => {
    isConfirmApproveDialogVisible.value = false

    let data = {
        state_id: state_id
    }

    let res = await servicesStores.updateState(data, selectedService.value.id)
    selectedService.value = {}

    advisor.value = {
        type: res.data.success ? 'success' : 'error',
        message: res.data.success ? 'Servicio actualizado!' : res.data.message,
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

const editService = id => {
    router.push({ name : 'dashboard-services-services-edit-id', params: { id: id } })
}

const deleteService = id => {
  isConfirmDeleteDialogVisible.value = true
  selectedService.value = myServicesList.value.filter((element) => element.id === id )[0]
}

const updateLink = (data) => {

  let request = {}

  if(data.text === 'favourite')
      request = { favourite: data.value }
  else if(data.text === 'archived')
      request = { archived: data.value }
  else if(data.text === 'discarded')
      request = { discarded: data.value }

  servicesStores.updateLink(request, data.id)
      .then(response => {

          window.scrollTo(0, 0)
                  
          advisor.value.show = true
          advisor.value.type = 'success'
          advisor.value.message = 'Servicio actualizado!'

          closeAdvisor()
          fetchData()

      }).catch(error => {
          window.scrollTo(0, 0)
                  
          advisor.value.show = true
          advisor.value.type = 'error'

          if (error.feedback === 'params_validation_failed') {
              advisor.value.message = error.message
          } else {
              advisor.value.message = 'Se ha producido un error...! (Server Error)'
          }

          closeAdvisor()  
      })
}

const findArchived = () => {
  archived.value = (archived.value === 1) ? 0 : 1
  favourite.value = 0
  discarded.value = 0
}

const findFavourite = () => {
  favourite.value = (favourite.value === 1) ? 0 : 1
  archived.value = 0
  discarded.value = 0
}

const findDiscarded = () => {
    discarded.value = (discarded.value === 1) ? 0 : 1
    archived.value = 0
    favourite.value = 0
}

const closeAdvisor = () => {
    setTimeout(() => {
    advisor.value = {
      type: '',
      message: '',
      show: false
    }
  }, 3000)
}

const copy = (data) => {
  cp.copy(data)
  advisor.value.type = 'success'
  advisor.value.show = true
  advisor.value.message = 'Enlace copiado!'
  closeAdvisor()
}

const open = (url) => {
    window.open(url, '_blank', 'noreferrer');

    advisor.value.type = 'success'
    advisor.value.show = true
    advisor.value.message = 'Enlace Abierto Exitosamente!'
    closeAdvisor()
}

const download = async (img) => {

    try {
        const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + img);
        const blob = await response.blob();

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        link.setAttribute('download', 'image.jpg');

        document.body.appendChild(link);
        link.click();

        window.URL.revokeObjectURL(url);

        advisor.value.type = 'success'
        advisor.value.show = true
        advisor.value.message = 'Descarga Exitosa!'

      } catch (error) {

        advisor.value.type = 'error'
        advisor.value.show = true
        advisor.value.message = 'Error al descargar la imagen:' + error
      }

    closeAdvisor()
}

const showService = async (id) => {
  isServiceDetailDialog.value = true
  selectedService.value = myServicesList.value.filter((element) => element.id === id )[0]
}

const showAlert = function(alert) {
  advisor.value.show = alert.value.show
  advisor.value.type = alert.value.type
  advisor.value.message = alert.value.message
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
}

const removeService = async () => {
  isConfirmDeleteDialogVisible.value = false

  let res = await servicesStores.deleteService({ ids: [selectedService.value.id] })
  selectedService.value = {}
  searchQuery.value = ''
  
  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Servicio eliminado!' : res.data.message,
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
    <VAlert
      v-if="advisor.show"
      :type="advisor.type"
      class="mb-6">  
      {{ advisor.message }}
    </VAlert>
    <Toaster />
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
            class="mb-0"
          />
        </VCardText>
      </VCard>
    </VDialog>
    <VCard v-if="services"
      id="rol-list"
      title="Filtros">

      <VCardText>
        <VRow>
          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="3"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Estados"
              :items="status"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- 👉 Select Category -->
          <VCol
            cols="12"
            sm="3"
          >
            <VAutocomplete
              id="selectCategory"
              v-model="selectedCategory"
              label="Categoría"
              :items="categories"
              :item-title="item => item.name"
              :item-value="item => item.id"
              autocomplete="off"
              clearable
              :menu-props="{ maxHeight: '300px' }">
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
          </VCol>

          <VCol cols="12" :sm="rol === 'Proveedor' ? '6' : '4'">
            <VTextField
              v-model="searchQuery"
              label="Buscar"
              placeholder="Buscar"
              density="compact"
              clearable
            />
          </VCol>
          
          <VCol cols="12" sm="2" v-if="rol !== 'Proveedor'">
            <div class="d-flex justify-content-end flex-wrap gap-4">
              <VBtn
                @click="findArchived()"
                icon
                variant="text"
                color="default"
                size="x-small">
                <VTooltip
                  open-on-focus
                  location="top"
                  activator="parent">
                  Archivados
                </VTooltip>
                <VIcon
                  size="20"
                  icon="tabler-building-store"
                  class="me-1"
                  :color="archived === 1 ? 'warning' : 'default'"
                />
              </VBtn>
              <VBtn
                @click="findFavourite()"
                icon
                variant="text"
                color="default"
                size="x-small">
                <VTooltip
                  open-on-focus
                  location="top"
                  activator="parent">
                  Favoritos
                </VTooltip>
                <VIcon
                  size="20"
                  icon="tabler-star-filled"
                  class="me-1"
                  :color="favourite === 1 ? 'info' : 'default'"
                />
              </VBtn>
              <VBtn
                @click="findDiscarded()"
                icon
                variant="text"
                color="default"
                size="x-small">
                <VTooltip
                  open-on-focus
                  location="top"
                  activator="parent">
                  Descartados
                </VTooltip>
                <VIcon
                  size="20"
                  icon="mdi-cart-remove"
                  class="me-1"
                  :color="discarded === 1 ? 'error' : 'default'"
                />
              </VBtn>
            </div> 
          </VCol>          
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex align-center flex-wrap gap-4">
        <div
          class="d-flex align-center"
          style="width: 135px;">
          <span class="text-no-wrap me-3">Ver:</span>
          <VSelect
            v-model="rowPerPage"
            density="compact"
            variant="outlined"
            :items="[10, 20, 30, 50]"
          />
        </div>

        <VSpacer />

        <VBtn
          v-if="$can('crear','servicios')"
          prepend-icon="tabler-plus"
          :to="{ name: 'dashboard-services-services-add' }">
          Agregar Servicio
        </VBtn>
      </VCardText>

      <VDivider />

      <VCardText class="pb-0">
        <VTabs
          v-model="currentTab"
          grow
          stacked
          >
          <VTab class="hover-icon">
            <VIcon
              icon="mdi-grid"
              class="mb-2"
            />
              <span>ICONO</span>
          </VTab>
          <VTab>
            <VIcon
              icon="mdi-format-list-bulleted"
              class="mb-2"
            />
            <span>LISTA</span>
          </VTab>
        </VTabs>
      </VCardText>
            
      <VCardText class="px-0">   
        <VWindow v-model="currentTab">
          <!-- CATALOGO -->
          <VWindowItem>              
            <VCardText>
              <VRow class="gap-y-4">
                <VCol
                  v-for="service in services"
                  cols="12"
                  md="6"
                  sm="12"
                  class="ps-6">
                  <detailsService
                    :isShowComponent="true"
                    :service="service"
                    :rol="rol"
                    @alert="showAlert"
                    @copy="copy"
                    @open="open"
                    @download="download"
                    @updateLink="updateLink"
                    @show="showService"
                    @editService="editService"
                    @deleteService="deleteService"
                  />
                </VCol>
                <VCol
                  cols="12" 
                  v-show="!services.length"
                  class="text-center">
                    Datos no disponibles
                </VCol>
              </VRow>
            </VCardText>

            <VDivider />

            <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
              <span class="text-sm text-disabled">
                {{ paginationData }}
              </span>

              <VPagination
                v-model="currentPage"
                size="small"
                :total-visible="5"
                :length="totalPages"
              />
            </VCardText>

          </VWindowItem>

          <!-- LISTADO -->
          <VWindowItem>
            <v-table class="text-no-wrap">
              <thead>
                <tr class="text-no-wrap">
                  <th> #ID </th>
                  <th> SERVICIO </th>
                  <th class="pe-4"> SKU </th>
                  <th class="pe-4"> PRECIO </th>
                  <th class="pe-4"> STATUS </th>
                  <th scope="pe-4" v-if="
                    $can('aprobar', 'servicios') || 
                    $can('rechazar', 'servicios') || 
                    $can('editar', 'servicios') || 
                    $can('eliminar', 'servicios')">
                    ACCIONES
                  </th>
                </tr>
            </thead>
            <tbody>
              <tr
                v-for="service in myServicesList"
                :key="service.id"
                style="height: 3.75rem;">
                <td> {{ service.id }} </td>
                <td> 
                  <div class="d-flex align-center gap-x-2">
                    <VAvatar
                      v-if="service.image"
                      size="38"
                      variant="outlined"
                      rounded
                      :image="themeConfig.settings.urlStorage + service.image"
                    />
                    <div class="d-flex flex-column">
                      <span class="text-body-1 font-weight-medium">{{ service.name }}</span>
                      <span class="text-sm text-disabled">Tienda: {{ service.user.user_detail.store_name ?? (service.user.name + ' ' + (service.user.last_name ?? '')) }}</span>
                    </div>
                  </div>
                </td>
                <td> {{ service.sku }} </td>
                <td> {{ (parseFloat(service.cupcakes.length > 0 ? service.cupcakes[0].price : service.price)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}</td>
                <td> 
                  <VChip
                    v-bind="resolveStatus(service.state_id)"
                    density="default"
                    label
                  />
                </td>
                <td class="text-center" style="width: 5rem;" 
                    v-if="$can('aprobar', 'servicios') ||
                    $can('rechazar', 'servicios') || 
                    $can('editar', 'servicios') || 
                    $can('eliminar', 'servicios')">          
                  <VBtn
                    v-if="$can('ver', 'servicios')"
                    @click="showService(service.id)"
                    icon
                    variant="text"
                    color="default"
                    size="x-small">
                    <VTooltip
                      open-on-focus
                      location="top"
                      activator="parent">
                      Ver
                    </VTooltip>
                    <VIcon
                      size="28"
                      icon="tabler-eye"
                      class="me-1"
                    />
                  </VBtn>          
                  <VBtn
                    v-if="$can('aprobar', 'servicios') && service.state_id === 4"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showStateDialog(service, 3)">
                    <VTooltip
                      open-on-focus
                      location="top"
                      activator="parent">
                      Aprobar
                    </VTooltip>      
                    <VIcon
                      size="22"
                      icon="mdi-cart-check" />
                  </VBtn>
                  <VBtn
                    v-if="$can('rechazar', 'servicios') && service.state_id === 4"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showStateDialog(service, 6)">
                    <VTooltip
                      open-on-focus
                      location="top"
                      activator="parent">
                      Rechazar
                    </VTooltip>      
                    <VIcon
                      size="22"
                      icon="mdi-cart-off" />
                  </VBtn>
                  <VBtn
                    v-if="$can('editar', 'servicios') && service.state_id !== 4"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    :disabled="service.state_id === 5"
                    @click="editService(service.id)">
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
                    v-if="$can('eliminar','servicios')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    :disabled="service.state_id === 5"
                    @click="showDeleteDialog(service)">
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
            <tfoot v-show="!services.length">
              <tr>
                <td
                  colspan="8"
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

          </VWindowItem>
        </VWindow>
      </VCardText>
    </VCard>

    <show 
      v-model:isDrawerOpen="isServiceDetailDialog"
      :service="selectedService"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Servicio">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el servicio de <strong>{{ selectedService.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeService">
              Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isConfirmApproveDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
            
      <DialogCloseBtn @click="isConfirmApproveDialogVisible = !isConfirmApproveDialogVisible" />

      <!-- Dialog Content -->
      <VCard :title=" (state_id === 3 ? 'Aprobar ': 'Rechazar ') + 'Servicio'">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de {{ state_id === 3 ? 'aprobar': 'rechazar' }}  el servicio <strong>{{ selectedService.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmApproveDialogVisible = false">
            Cancelar
          </VBtn>
          <VBtn @click="stateService(state_id)">
            Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<style scope>
    .align-right {
      text-align: right !important;
    }

    .justify-content-end {
        justify-content: end !important;
    }

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
    subject: servicios
</route>