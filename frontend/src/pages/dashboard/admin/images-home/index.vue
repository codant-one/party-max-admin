<script setup>
import create from './create.vue'
import { useHomeImagesStores } from '@/stores/useHomeImages'
import { excelParser } from '@/plugins/csv/excelParser'

const homeimagesStores = useHomeImagesStores()

const homeimages = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalHomeImages = ref(0)
const selectedRows = ref([])

const selectedHomeImage = ref({})
const readonly = ref(false)

const isRequestOngoing = ref(true)

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = homeimages.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = homeimages.value.length + (currentPage.value - 1) * rowPerPage.value
  
  return `Mostrando ${ firstIndex } al ${ lastIndex } de ${ totalHomeImages.value } imágenes`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
})

watchEffect(fetchData)


// 👉 Fetch Images Home
async function fetchData() {
  isRequestOngoing.value = true

  let data = {
    search: searchQuery.value,
    orderByField: 'id',
    orderBy: 'asc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  await homeimagesStores.fetchHomeImages(data)

  homeimages.value = homeimagesStores.getHomeImages
  totalPages.value = homeimagesStores.last_page
  totalHomeImages.value = homeimagesStores.homeimagesTotalCount

  isRequestOngoing.value = false
}


// show dialogs

const showAlert = function(alert) {
  advisor.value.show = alert.value.show
  advisor.value.type = alert.value.type
  advisor.value.message = alert.value.message
}

const downloadCSV = async () => {

  isRequestOngoing.value = true

  let data = { limit: -1}

  await homeimagesStores.fetchHomeImages(data)

  let dataArray = []

  homeimagesStores.getHomeImages.forEach(element => {
    let data = {
      ID: element.id,
      ROL: element.name
    }
        
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "images-home", "csv")

  isRequestOngoing.value = false
}

</script>

<template>
  <section>
    <v-row>
  
        <v-col cols="12">

            <VCard>
                <VCardText class="d-flex align-center flex-wrap gap-4">
                    <!-- 👉 Rows per page -->
                    <div
                    class="d-flex align-center"
                    style="width: 135px;"
                    >
                    <span class="text-no-wrap me-3">Ver:</span>
                    <VSelect
                        v-model="rowPerPage"
                        density="compact"
                        :items="[10, 20, 30, 50]"
                    />
                    </div>

                    <div class="me-3">
                    <create
                        @data="fetchData"
                        @alert="showAlert"/>
                    </div>

                    <div class="me-3">
                    <tree />
                    </div>

                    <VSpacer />

                    <div class="d-flex align-center">
                    <VBtn
                        variant="tonal"
                        color="secondary"
                        prepend-icon="tabler-file-export"
                        @click="downloadCSV"
                    >
                        Exportar
                    </VBtn>
                    </div>

                    <div class="d-flex align-center flex-wrap gap-4">
                    <!-- 👉 Search  -->
                    </div>
                </VCardText>
            </VCard>
        </v-col>

    </v-row>
  </section>
</template>

<style lang="scss">
  #rol-list {
    .rol-list-actions {
      inline-size: 8rem;
    }

    .rol-list-filter {
      inline-size: 12rem;
    }
  }

  .v-label {
    text-overflow: clip;
  }

  #permisos-lista {
    .n1 strong {
      font-size: 1.7rem;
    }

    .n2 strong {
      font-size: 1.3rem;
    }

    .n3 strong {
      font-size: 1rem;
    }

    .tab {
      margin-block: 4px;
      margin-inline-start: 2rem;
    }
  }

  .search {
    width: 14rem;
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
    subject: images-home
</route>
