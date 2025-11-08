<script setup>

import { useBannersStores } from '@/stores/useBanners'
import { themeConfig } from '@themeConfig'
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'

const bannersStores = useBannersStores()

const banners = ref([])
const searchQuery = ref('')
const searchFathers = ref(0)
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalBanners = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const isConfirmStateDialogVisible = ref(false)
const state_id = ref()
const selectedBanner = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = banners.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = banners.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalBanners.value } registros`
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

  await bannersStores.fetchBanners(data)

  banners.value = bannersStores.getBanners
  totalPages.value = bannersStores.last_page
  totalBanners.value = bannersStores.bannersTotalCount

  isRequestOngoing.value = false
}

const editBanner = bannerData => {
  router.push({ name : 'dashboard-admin-banners-edit-id', params: { id: bannerData.id } })
}

const openBanner = function (bannerData) {
  window.open(`${themeConfig.settings.urlDomain + 'banners/' + bannerData.slug}`)
}


const showDeleteDialog = bannerData => {
  isConfirmDeleteDialogVisible.value = true
  selectedBanner.value = { ...bannerData }
}


const removeBanner = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await bannersStores.deleteBanner({ ids: [selectedBanner.value.id] })
  selectedBanner.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Banner eliminado!' : res.data.message,
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
                <VTextField
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"
                  clearable
                />
              </div>

              <!-- 👉 Add user button -->
              <v-btn
                v-if="$can('crear','banners')"
                prepend-icon="tabler-plus"
                :to="{ name: 'dashboard-admin-banners-add' }">
                  Agregar Banner
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr class="text-no-wrap">
                <th> NOMBRE </th>
                <th v-if="$can('editar', 'banners') || $can('eliminar', 'banners')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="banner in banners"
                :key="banner.id"
                style="height: 3.75rem;">

                <td>
                  <div class="d-flex gap-x-3">
                    <div class="d-block">
                      <span class="d-block text-base">
                        {{ banner.name }}
                      </span>
                      <span class="d-block text-sm text-disabled">
                        {{ banner.slug }}
                      </span>
                    </div>
                  </div>
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'banners') || $can('eliminar', 'banners')">      
                  <VBtn
                    v-if='false'
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
                      @click="openBanner(banner)"
                      />
                  </VBtn>

                  <VBtn
                    v-if="$can('editar', 'banners')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editBanner(banner)">
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
                    v-if="$can('eliminar','banners')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(banner)">
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
            <tfoot v-show="!banners.length">
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
      <VCard title="Eliminar Banner">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el banner de <strong>{{ selectedBanner.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeBanner">
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
    subject: banners
</route>