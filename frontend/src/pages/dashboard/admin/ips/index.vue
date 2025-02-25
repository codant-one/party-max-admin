<script setup>

import { useIpsStores } from '@/stores/useIps'
import { excelParser } from '@/plugins/csv/excelParser'
import router from '@/router'

const ipsStores = useIpsStores()

const ips = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalIps = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const selectedIp = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = ips.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = ips.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalIps.value } registros`
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
    orderBy: 'asc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  isRequestOngoing.value = true

  await ipsStores.fetchIps(data)

  ips.value = ipsStores.getIps
  totalPages.value = ipsStores.last_page
  totalIps.value = ipsStores.ipsTotalCount

  isRequestOngoing.value = false
}

const showDeleteDialog = ipData => {
  isConfirmDeleteDialogVisible.value = true
  selectedIp.value = { ...ipData }
}

const removeIp = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await ipsStores.deleteIp(selectedIp.value.id)
  selectedIp.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'IP eliminada!' : res.data.message,
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

const showIp = id => {
    router.push({ name : 'dashboard-admin-ips-id', params: { id: id } })
}

const resolveStatus = statusMsg => {
  if (statusMsg === 0)
    return {
      text: 'Activo',
      color: 'success',
    }
  if (statusMsg === 1)
    return {
      text: 'Bloqueado',
      color: 'warning',
    }
}

const openLink = function (data) {
  window.open(`https://www.google.com/maps?q=${data.coordinates}`)
}

const downloadCSV = async () => {

  isRequestOngoing.value = true

  let data = { limit: -1 }

  await ipsStores.fetchIps(data)

  let dataArray = [];
      
  ipsStores.getIps.forEach(element => {
    let data = {
        IP: element.ip,
        NÚMERO_REGISTROS: element.registration_number,
        DISPOSITIVO: element.device,
        PLATAFORMA: element.plataform,
        NAVEGADOR: element.browser,
        LOCALIZACIÓN: element.location,
        CÓDIGO_POSTAL: element.postal_code,
        TIMEZONE: element.timezone,
        COORDENADAS: element.coordinates,
        GOOGLE_MAPS: `https://www.google.com/maps?q=${element.coordinates}`,
        ES_ROBOT: element.is_bot === 0 ? 'NO' : 'SI',
        ESTADO: element.is_blocked === 0 ? 'ACTIVO' : 'BLOQUEADO'
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "ips", "csv");

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
                <VTextField
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"
                  clearable
                />
              </div>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ID </th>
                <th scope="col"> IP </th>
                <th scope="col"> DISPOSITIVO </th>
                <th scope="col"> PLATAFORMA </th>
                <th scope="col"> NAVEGADOR </th>
                <th scope="col"> LOCALIZACIÓN </th>
                <th scope="col"> ES ROBOT? </th>
                <th scope="col"> ESTADO </th>
                <th scope="col" v-if="$can('ver', 'ips') || $can('bloquear', 'ips') || $can('eliminar', 'ips')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
                <tr 
                    v-for="ip in ips"
                    :key="ip.id"
                    style="height: 3.75rem;">
                    <td> {{ ip.id }} </td>
                    <td class="text-wrap"> {{ ip.ip }} </td>
                    <td class="text-wrap"> {{ ip.device }} </td>
                    <td class="text-wrap"> {{ ip.plataform }} </td>
                    <td class="text-wrap"> {{ ip.browser }} </td>
                    <td class="text-wrap">
                        <div class="d-flex align-center gap-x-3">
                            <div class="d-flex flex-column">
                                <span class="font-weight-medium cursor-pointer text-primary" @click="openLink(ip)">
                                    {{ ip.location }}
                                </span>
                                <span class="text-sm text-disabled">
                                    Código Postal: {{ ip.postal_code }}
                                </span>
                                <span class="text-sm text-disabled">
                                    Timezone: {{ ip.timezone }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="text-wrap">
                        <div class="text-body-1 text-high-emphasis font-weight-medium text-success">
                            {{ ip.is_bot === 0 ? 'NO' : 'SI'}}
                        </div>
                    </td>
                    <td class="text-wrap">
                        <VChip
                            v-bind="resolveStatus(ip.is_blocked)"
                            density="default"
                            label
                        /> 
                    </td>
                    <!-- 👉 Acciones -->
                    <td class="text-center" style="width: 5rem;" v-if="$can('ver', 'ips') || $can('bloquear', 'ips') || $can('eliminar', 'ips')">      
                        <VBtn
                            v-if="$can('ver', 'ips')"
                            @click="showIp(ip.id)"
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
                            v-if="$can('ver','ips')"
                            icon
                            variant="text"
                            color="default"
                            size="x-small">  
                            <VTooltip
                                open-on-focus
                                location="top"
                                activator="parent"
                                >
                                Abrir ubicación
                            </VTooltip>
                            <VIcon
                                icon="mdi-open-in-new"
                                :size="22"
                                @click="openLink(ip)"
                            />
                        </VBtn>
                        <VBtn
                            v-if="$can('eliminar','ips')"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            @click="showDeleteDialog(ip)">
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
            <tfoot v-show="!ips.length">
              <tr>
                <td
                  colspan="9"
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
      <VCard title="Eliminar IP">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar la ip <strong>{{ selectedIp.ip }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeIp">
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
    subject: ips
</route>