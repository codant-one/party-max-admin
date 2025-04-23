<script setup>

import { themeConfig } from '@themeConfig'
import { useReferralsStores } from '@/stores/useReferrals'
import { useCategoriesStores } from '@/stores/useCategories'
import Toaster from "@/components/common/Toaster.vue";
import router from '@/router'

const referralsStores = useReferralsStores()
const categoriesStores = useCategoriesStores()

const referrals = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalReferrals = ref(0)
const isRequestOngoing = ref(true)

const selectedCategory = ref()

const rol = ref(null)
const userData = ref(null)
const categories = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = referrals.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = referrals.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalReferrals.value } registros`
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
        orderBy: 'desc',
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

    await referralsStores.fetchReferrals(data)

    referrals.value = referralsStores.getReferrals

    totalPages.value = referralsStores.last_page
    totalReferrals.value = referralsStores.referralsTotalCount

    isRequestOngoing.value = false
}

const seeSupplier = supplierData => {
  router.push({ name : 'dashboard-admin-suppliers-id', params: { id: supplierData.id } })
}

const seeReferreral = id => {
    router.push({ name : 'dashboard-referrals-id', params: { id: id } })
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
    <VCard v-if="referrals" id="rol-list" title="Remisiones">
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
              <th> REMISIÓN </th>
              <th> EMPRESA </th>
              <th> CONTACTO </th>
              <th class="pe-4"> FECHA </th>
              <th class="pe-4"> PRODUCTOS </th>
              <th class="pe-4"> STATUS </th>
              <th class="pe-4"> ACCIONES </th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="referral in referrals" :key="referral.id"
              style="height: 3.75rem;">
              <td> {{ referral.id }} </td>
              <td class="text-wrap w-25">
                <div class="d-flex align-center gap-x-3" v-if="referral.user.supplier">
                  <VAvatar
                    :variant="referral.user.avatar ? 'outlined' : 'tonal'"
                    size="38"
                    >
                    <VImg
                      v-if="referral.user.avatar"
                      style="border-radius: 50%;"
                      :src="themeConfig.settings.urlStorage + referral.user.avatar"
                    />
                      <span v-else>{{ avatarText(referral.user.name) }}</span>
                  </VAvatar>
                  <div class="d-flex flex-column">
                    <span class="font-weight-medium cursor-pointer text-primary" @click="seeSupplier(referral.user.supplier)">
                      {{ referral.user.supplier.company_name }}
                    </span>
                    <span class="text-sm text-disabled" v-if="referral.user.supplier.document">
                      {{ referral.user.supplier.document?.type.code }}: {{ referral.user.supplier.document?.main_document }}
                    </span>
                  </div>
                </div>
              </td>
              <td class="text-wrap w-25">
                <div class="d-flex flex-column">
                  <span class="font-weight-medium">
                    {{ referral.user.name }} {{ referral.user.last_name ?? '' }} 
                  </span>
                  <span class="text-sm text-disabled">{{ referral.user.email }}</span>
                </div>
              </td>
              <td> {{ referral.date }} </td>
              <td> {{ referral.details_count }} </td>
              <td> 
                <VChip
                    :text="referral.delivered === 0 ? 'Pendiente' : 'Recibido'"
                    :color="referral.delivered === 0 ? 'error' : 'primary'"
                    density="default"
                    label
                  />
              </td>
              <!-- 👉 Acciones -->
              <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'remisiones')">      
                  <VBtn
                    v-if="$can('ver', 'remisiones')"
                    icon
                    variant="text"
                    color="default"
                    size="x-small"
                    @click="seeReferreral(referral.id)">
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
                </td>
            </tr>
          </tbody>
          <!-- 👉 table footer  -->
          <tfoot v-show="!referrals.length">
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
    subject: remisiones
</route>