<script setup>

import { useBlogsStores } from '@/stores/useBlogs'
import { excelParser } from '@/plugins/csv/excelParser'
import { themeConfig } from '@themeConfig'
import { ref } from "vue"
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'

const blogsStores = useBlogsStores()

const blogs = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalBlogs = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const selectedBlog = ref({})

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = blogs.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = blogs.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalBlogs.value } registros`
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

  await blogsStores.fetchBlogs(data)

  blogs.value = blogsStores.getBlogs
  totalPages.value = blogsStores.last_page
  totalBlogs.value = blogsStores.blogsTotalCount

  isRequestOngoing.value = false

}

const editBlog = blogData => {
  router.push({ name : 'dashboard-admin-blogs-edit-id', params: { id: blogData.id } })
}

const showDeleteDialog = blogData => {
  isConfirmDeleteDialogVisible.value = true
  selectedBlog.value = { ...blogData }
}

const removeBlog = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await blogsStores.deleteBlog(selectedBlog.value.id)
  selectedBlog.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Blog eliminado!' : res.data.message,
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

const downloadCSV = async () => {

  isRequestOngoing.value = true

  let data = { limit: -1 }

  await blogsStores.fetchBlogs(data)

  let dataArray = [];
      
  blogsStores.getBlogs.forEach(element => {
    let data = {
      ID: element.id,
      TÍTULO: element.title,
      FECHA: element.date,
      CATEGORÍA: element.category.name,
      POPULAR: element.is_popular_blog === 1 ? 'SI' : 'NO',
      CONTENIDO: element.description
    }
        
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "blogs", "csv");

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
              <div style="width: 30rem;">
                <v-text-field
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"/>
              </div>

              <!-- 👉 Add user button -->
              <v-btn
                v-if="$can('crear','blogs')"
                prepend-icon="tabler-plus"
                :to="{ name: 'dashboard-admin-blogs-add' }">
                  Agregar Blog
              </v-btn>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ID </th>
                <th scope="col"> TÍTULO </th>
                <th scope="col"> FECHA </th>
                <th scope="col"> CATEGORÍA </th>
                <th scope="col"> POPULAR </th>
                <th scope="col"> CONTENIDO </th>
                <th scope="col"> IMAGEN </th>
                <th scope="col" v-if="$can('editar','blogs') || $can('eliminar','blogs')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="blog in blogs"
                :key="blog.id"
                style="height: 3.75rem;">

                <td> {{ blog.id }} </td>
                <td> {{ blog.title }} </td>
                <td> {{ blog.date }} </td>
                <td> {{ blog.category.name }} </td>
                <td> 
                  <VChip
                    label
                    :color="blog.is_popular_blog === 1 ? 'success' : 'error'"
                    size="small">
                    {{  blog.is_popular_blog === 1 ? 'SI' : 'NO' }}
                  </VChip>  
                </td>
                <td>
                  <span v-if="blog.description" v-html="blog.description.slice(0,50) + '...'"></span>
                </td>
                <td>
                    <VImg
                        v-if="blog.image !== null"
                        :src="themeConfig.settings.urlStorage + blog.image"
                        :height="50"
                        aspect-ratio="1/1"
                    />
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar','blogs') || $can('eliminar','blogs')">      
                  <VBtn
                    v-if="$can('editar','blogs')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="editBlog(blog)">
                              
                    <VIcon
                        size="22"
                        icon="tabler-edit" />
                  </VBtn>

                  <VBtn
                    v-if="$can('eliminar','blogs')"
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="showDeleteDialog(blog)">
                              
                    <VIcon
                      size="22"
                      icon="tabler-trash" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!blogs.length">
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
      <VCard title="Eliminar Blogs">
        <VCardText>
          Está seguro de eliminar el blog de <strong>{{ selectedBlog.title }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeBlog">
              Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<route lang="yaml">
  meta:
    action: ver
    subject: blogs
</route>

