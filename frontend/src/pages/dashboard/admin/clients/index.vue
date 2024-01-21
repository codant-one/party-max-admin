<script setup>

import { useClientsStores } from '@/stores/useClients'
import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'
import { useGendersStores } from '@/stores/useGenders'
import { excelParser } from '@/plugins/csv/excelParser'
import { themeConfig } from '@themeConfig'
import { avatarText } from '@/@core/utils/formatters'
import AddNewClientDrawer from './AddNewClientDrawer.vue' 
import router from '@/router'

const clientsStores = useClientsStores()
const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()
const gendersStores = useGendersStores()

const clients = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalClients = ref(0)
const isRequestOngoing = ref(true)
const isAddNewClientDrawerVisible = ref(false)
const isConfirmDeleteDialogVisible = ref(false)
const selectedClient = ref({})
const listCountries = ref([])
const listProvinces = ref([])
const listGenders = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = clients.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = clients.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalClients.value } registros`
})

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
}

const loadGenders = () => {
  listGenders.value = gendersStores.getGenders
}

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

    if (!isAddNewClientDrawerVisible.value)
        selectedClient.value = {}
})

onMounted(async () => {

  await countriesStores.fetchCountries()

  loadCountries()
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

  await clientsStores.fetchClients(data)

  clients.value = clientsStores.getClients
  totalPages.value = clientsStores.last_page
  totalClients.value = clientsStores.clientsTotalCount

  await provincesStores.fetchProvinces()
  loadProvinces()
  
  await gendersStores.fetchGenders()
  loadGenders()

  isRequestOngoing.value = false
}

const editClient = clientData => {
    isAddNewClientDrawerVisible.value = true
    selectedClient.value = { ...clientData }
}


const showDeleteDialog = clientData => {
  isConfirmDeleteDialogVisible.value = true
  selectedClient.value = { ...clientData }
}

const seeClient = clientData => {
  router.push({ name : 'dashboard-admin-clients-id', params: { id: clientData.id } })
}

const removeClient = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await clientsStores.deleteClient(selectedClient.value.id)
  selectedClient.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Cliente eliminado!' : res.data.message,
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

const submitForm = async (client, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    client.data.append('_method', 'PUT')
    submitUpdate(client)
    return
  }

  submitCreate(client.data)
}


const submitCreate = clientData => {

    clientsStores.addClient(clientData)
        .then((res) => {
            if (res.data.success) {
                advisor.value = {
                    type: 'success',
                    message: 'Cliente creado! ' + res.data.email_response,
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

const submitUpdate = clientData => {

    clientsStores.updateClient(clientData)
        .then((res) => {
            if (res.data.success) {
                    advisor.value = {
                    type: 'success',
                    message: 'Cliente actualizado!',
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

  await clientsStores.fetchClients(data)

  let dataArray = [];
      
  clientsStores.getClients.forEach(element => {

    let data = {
      ID: element.id,
      NOMBRE: element.user.name,
      APELLIDO: element.user.last_name ?? '',
      USUARIO: element.user.username,
      PAÍS:  element.user.user_detail.province.country.name
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "clients", "csv");

  isRequestOngoing.value = false

}

const getFlagCountry = country => {
  let val = listCountries.value.find(item => {
    return item.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") === country.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  })

  if(val)
    return 'https://hatscripts.github.io/circle-flags/flags/'+val.iso.toLowerCase()+'.svg'
  else
    return ''
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
                v-if="$can('crear','clientes')"
                prepend-icon="tabler-plus"
                @click="isAddNewClientDrawerVisible = true">
                  Agregar Cliente
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
                <th scope="col"> USUARIO </th>
                <th scope="col"> PAÍS </th>
                <th scope="col"> PEDIDOS </th>
                <th scope="col"> TOTAL VENTAS </th>
                <th scope="col" v-if="$can('editar', 'clientes') || $can('eliminar', 'clientes')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="client in clients"
                :key="client.id"
                style="height: 3.75rem;">

                <td> {{ client.id }} </td>
                <td class="text-wrap">
                  <div class="d-flex align-center gap-x-3">
                    <VAvatar
                      variant="tonal"
                      size="38"
                      >
                      <VImg
                        v-if="client.user.avatar"
                        style="border-radius: 50%;"
                        :src="themeConfig.settings.urlStorage + client.user.avatar"
                      />
                        <span v-else>{{ avatarText(client.user.name) }}</span>
                    </VAvatar>
                    <div class="d-flex flex-column">
                      <span class="font-weight-medium cursor-pointer text-primary" @click="seeClient(client)">
                        {{ client.user.name }} {{ client.user.last_name }} 
                      </span>
                      <span class="text-sm text-disabled">{{ client.user.email }}</span>
                    </div>
                  </div>
                </td>
                <td class="text-wrap"> {{ client.user.username }} </td>
                <td class="text-wrap"> 
                  <VAvatar
                    start
                    size="25"
                    :image="getFlagCountry(client.user.user_detail.province.country.name)"
                  />
                  <span class="text-body-2 ms-2">
                    {{ client.user.user_detail.province.country.name }} 
                  </span>
                </td>
                <td>
                  324
                </td>
                <td>
                  <span class="text-body-1 font-weight-medium text-high-emphasis">$100</span>
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'clientes') || $can('eliminar', 'clientes')">      
                  <VBtn
                    v-if="$can('ver', 'clientes')"
                    icon
                    variant="text"
                    color="default"
                    size="x-small"
                    @click="seeClient(client)">
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
                    v-if="$can('editar', 'clientes')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editClient(client)">
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
                    v-if="$can('eliminar','clientes')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(client)">
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
            <tfoot v-show="!clients.length">
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
    <!-- 👉 Add New Client -->
    <AddNewClientDrawer
      v-if="listProvinces.length > 0"
      v-model:isDrawerOpen="isAddNewClientDrawerVisible"
      :client="selectedClient"
      :countries="listCountries"
      :provinces="listProvinces"
      :genders="listGenders"
      @client-data="submitForm"/>

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Cliente">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el Cliente <strong>{{ selectedClient.user.name }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeClient">
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
    subject: clientes
</route>