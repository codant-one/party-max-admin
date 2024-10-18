<script setup>

import { themeConfig } from '@themeConfig'
import { requiredValidator } from '@/@core/utils/validators'
import { useReferralsStores } from '@/stores/useReferrals'
import { useCategoriesStores } from '@/stores/useCategories'
import Toaster from "@/components/common/Toaster.vue";

const referralsStores = useReferralsStores()
const categoriesStores = useCategoriesStores()

const myProductsList = ref([])
const products = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalProducts = ref(0)
const isRequestOngoing = ref(true)

const selectedCategory = ref()

const rol = ref(null)
const userData = ref(null)
const categories = ref([])

const refFormEdit = ref()
const isConfirmEditDialogVisible = ref(false)
const selectedReferral = ref({})
const quantity = ref(null)

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = products.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = products.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalProducts.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
})

watchEffect(fetchData)

async function fetchData() {

    userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
    rol.value = userData.value.roles[0].name

    let data = {
        search: searchQuery.value,
        category_id: selectedCategory.value,
        orderByField: 'id',
        orderBy: 'asc',
        limit: rowPerPage.value,
        page: currentPage.value,
        supplierId: userData.value.id
    }

    isRequestOngoing.value = true
    
    let info = { 
        limit: -1, 
        category_type_id: 1
    }

    await categoriesStores.fetchCategoriesOrder(info)

    categories.value = categoriesStores.getCategories

    await referralsStores.fetchProducts(data)

    products.value =  []
    myProductsList.value =  []
    myProductsList.value = referralsStores.getReferrals

    myProductsList.value.forEach(element =>
        products.value.push({
        id: element.id,
        user: element.user,
        state: element.state,
        in_stock: element.in_stock,
        stock: element.stock,           
        title: element.name,
        image: element.image,
        price: element.price_for_sale,
        originalLink: themeConfig.settings.urlDomain + 'products/' + element.slug,
        categories: element.colors[0]?.categories.map(item => item.category.name),// Utiliza map para extraer los nombres de las categorías
        rating: element.rating,//agregar mas adelante informacion
        comments: 0,//agregar mas adelante informacion
        sales: element.sales,//agregar mas adelante informacion
        selling_price: 0,//agregar mas adelante informacion,
        likes: element.likes
        })
    );

    totalPages.value = referralsStores.last_page
    totalProducts.value = referralsStores.referralsTotalCount

    isRequestOngoing.value = false
}

const showEditDialog = referralData => {
  isConfirmEditDialogVisible.value = true
  selectedReferral.value = { ...referralData }
  quantity.value = referralData.colors[0]?.referral.quantity
}

const onSubmitEdit = () =>{
  refFormEdit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {

      let data = {
        id: selectedReferral.value.colors[0].referral.referral_id,
        product_color_id: selectedReferral.value.colors[0].id,
        quantity: quantity.value
      }

        isRequestOngoing.value = true
        isConfirmEditDialogVisible.value = false
        referralsStores.updateProduct(data)
            .then(response => {

                window.scrollTo(0, 0)

                advisor.value.show = true
                advisor.value.type = 'success'
                advisor.value.message = 'Stock actualizado!'

                nextTick(() => {
                  refFormEdit.value?.reset()
                  refFormEdit.value?.resetValidation()
                })

                setTimeout(() => {
                    advisor.value.show = false
                    advisor.value.type = ''
                    advisor.value.message = ''
                }, 5000)

                fetchData()

            }).catch(error => {
                window.scrollTo(0, 0)
                
                advisor.value.show = true
                advisor.value.type = 'error'
                advisor.value.message = 'Se ha producido un error...! (Server Error)'
                setTimeout(() => {
                    advisor.value.show = false
                    advisor.value.type = ''
                    advisor.value.message = ''
                }, 5000)  
            })
    }
     
  })
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
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
    <VCard v-if="products" id="rol-list" title="Productos pendientes de actualización de stock">
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

        <VTextField
          v-model="searchQuery"
          label="Buscar"
          placeholder="Buscar"
          density="compact"
          clearable
        />
      </VCardText>

      <VDivider />
            
      <VCardText class="px-0">   
        <v-table class="text-no-wrap">
          <thead>
            <tr class="text-no-wrap">
              <th> #ID </th>
              <th> PRODUCTO </th>
              <th class="pe-4"> SKU </th>
              <th class="pe-4"> QTY ENTREGADO</th>
              <th scope="col" v-if="$can('editar', 'stock')">
                ACCIONES
              </th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="product in myProductsList" :key="product.id"
              style="height: 3.75rem;">
              <td> {{ product.id }} </td>
              <td> 
                <div class="d-flex align-center gap-x-2">
                  <VAvatar
                    v-if="product.image"
                    size="38"
                    variant="outlined"
                    rounded
                    :image="themeConfig.settings.urlStorage + product.image"
                    />
                  <div class="d-flex flex-column">
                    <span class="text-body-1 font-weight-medium">{{ product.name }}</span>
                    <span class="text-sm text-disabled">Tienda: {{ product.user.user_detail.store_name ?? (product.user.supplier?.company_name ?? (product.user.name + ' ' + (product.user.last_name ?? ''))) }}</span>
                  </div>
                </div>
              </td>
              <td> {{ product.colors[0]?.sku ?? '--' }} </td>
              <td> {{ product.colors[0]?.referral.quantity ?? 0 }} </td>
              <!-- 👉 Acciones -->
              <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'stock')">      
                <VBtn
                  v-if="$can('editar', 'stock')"
                  icon
                  size="x-small"
                  color="default"
                  variant="text"
                  @click="showEditDialog(product)"
                  >
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
              </td>
            </tr>
          </tbody>
          <!-- 👉 table footer  -->
          <tfoot v-show="!products.length">
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
      </VCardText>
    </VCard>

      <!-- DIALOGO DE EDITAR -->
      <VDialog
        v-model="isConfirmEditDialogVisible"
        max-width="600"
        persistent
        >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="isConfirmEditDialogVisible = !isConfirmEditDialogVisible" />

        <!-- Dialog Content -->
        <VCard title="Actualizar stock">
            <VDivider class="mt-4"/>
            <VForm
                ref="refFormEdit"
                @submit.prevent="onSubmitEdit"
            >
                <VCardText class="pb-0">
                    <VRow>
                        <VCol cols="12">
                            <VTextField
                                v-model="quantity"
                                type="number"
                                label="Stock"
                                :rules="[requiredValidator]"
                            />
                        </VCol>
                    </VRow>
                    <VCardText class="d-flex justify-end gap-3 flex-wrap px-0">
                      <VBtn
                        color="secondary"
                        variant="tonal"
                        @click="isConfirmEditDialogVisible = false"
                      >
                      Cancelar
                      </VBtn>
                      <VBtn type="submit">
                        Editar
                      </VBtn>
                    </VCardText>
                </VCardText>
            </VForm>
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
    subject: stock
</route>