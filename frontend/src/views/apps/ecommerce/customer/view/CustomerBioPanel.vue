<script setup>

import { themeConfig } from '@themeConfig'
import { avatarText, formatNumber } from '@/@core/utils/formatters'
import { useCountriesStores } from '@/stores/useCountries'
// import rocketImg from '@images/eCommerce/rocket.png'

const props = defineProps({
  customerData: {
    type: Object,
    required: true,
  }
})

const route = useRoute()
const countriesStores = useCountriesStores()

const isUpgradePlanDialogVisible = ref(false)

const listCountries = ref([])
const valueCount = ref(null)
const valueText = ref(null)
const icon = ref('tabler-shopping-cart')

watchEffect(fetchData)

async function fetchData() {
  await countriesStores.fetchCountries();

  if (route.name.includes('clients')) {
    valueCount.value = props.customerData.orders_count ?? 0
    valueText.value = 'Pedidos'
    icon.value = 'tabler-shopping-cart'
  } else {
    valueCount.value = props.customerData.product_count ?? 0
    valueText.value = 'Publicados'
    icon.value = 'tabler-building-store'
  }

  loadCountries()
}

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
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
  <VRow>
    <!-- SECTION Customer Details -->
    <VCol cols="12">
      <VCard v-if="props.customerData">
        <VCardText class="text-center pt-15">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded
            :size="100"
            :color="!props.customerData.customer ? 'primary' : undefined"
            :variant="!props.customerData.user.avatar ? 'tonal' : undefined"
          >
            <VImg
              v-if="props.customerData.user.avatar"
              :src="themeConfig.settings.urlStorage + props.customerData.user.avatar"
            />
            <span
              v-else
              class="text-5xl font-weight-medium"
            >
              {{ avatarText(props.customerData.user.name) }}
            </span>
          </VAvatar>

          <!-- 👉 Customer fullName -->
          <h4 class="text-h4 mt-4">
            {{ props.customerData.user.name }}  {{ props.customerData.user.last_name ?? '' }}
          </h4>
          <span class="text-sm">Cliente ID #{{ props.customerData.id }}</span>

          <div class="d-flex justify-center gap-x-5 mt-6">
            <div class="d-flex align-center">
              <VAvatar
                variant="tonal"
                color="primary"
                rounded
                class="me-3"
              >
                <VIcon :icon="icon" />
              </VAvatar>
              <div class="d-flex flex-column align-start">
                <span class="text-body-1 font-weight-medium"> {{ valueCount }} </span>
                <span class="text-body-2">{{ valueText }}</span>
              </div>
            </div>
            <div class="d-flex align-center">
              <VAvatar
                variant="tonal"
                color="primary"
                rounded
                class="me-3"
              >
                <VIcon icon="tabler-currency-dollar" />
              </VAvatar>
              <div class="d-flex flex-column align-start">
                <span class="text-body-1 font-weight-medium">COP {{ formatNumber(props.customerData.sales) ?? '0.00' }}</span>
                <span class="text-body-2">Ventas</span>
              </div>
            </div>
          </div>
        </VCardText>

        <!-- 👉 Customer Details -->
        <VCardText>
          <VDivider class="my-4" />
          <div class="text-disabled text-uppercase text-sm">
            Detalles
          </div>

          <VList class="card-list mt-2">
            <VListItem>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  Nombre:
                  <span class="text-body-2">
                    {{ props.customerData.user.name }}
                  </span>
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  Apellido:
                  <span class="text-body-2">
                    {{ props.customerData.user.last_name ?? '' }}
                  </span>
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  Email:
                  <span class="text-body-2">
                    {{ props.customerData.user.email }}
                  </span>
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  Username:
                  <span class="text-body-2">
                    {{ props.customerData.user.username }}
                  </span>
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                    Teléfono:
                  <span class="text-body-2">
                    {{ props.customerData.user.user_detail.phone }}
                  </span>
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  Dirección:
                  <span class="text-body-2">
                    {{ props.customerData.user.user_detail.address }}
                  </span>
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  Estado:
                  <span class="text-body-2">
                    {{ props.customerData.user.user_detail.province.name }}
                  </span>
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  País:
                  <span class="text-body-2 me-2">
                    {{ props.customerData.user.user_detail.province.country.name }}
                  </span>
                  <VAvatar
                    start
                    size="25"
                    :image="getFlagCountry(props.customerData.user.user_detail.province.country.name)"
                  />
                </h6>
              </VListItemTitle>
              <VListItemTitle>
                <h6 class="text-base font-weight-semibold">
                  Documento:
                  <span class="text-body-2">
                    {{ props.customerData.user.user_detail.document }}
                  </span>
                </h6>
              </VListItemTitle>
            </VListItem>
            
          </VList>
        </VCardText>

      </VCard>
    </VCol>
    <!-- !SECTION -->

    <!-- SECTION  Upgrade to Premium -->
    <!-- <VCol cols="12">
      <VCard
        flat
        class="current-plan"
      >
        <VCardText>
          <div class="d-flex align-center">
            <div>
              <div class="text-xl font-weight-medium mb-4">
                Upgrade to premium
              </div>
              <p class="mb-6 text-wrap">
                Upgrade customer to premium membership to access pro features.
              </p>
            </div>
            <div>
              <VImg
                :src="rocketImg"
                height="108"
                width="108"
              />
            </div>
          </div>
          <VBtn
            color="#fff"
            class="text-primary"
            block
            @click="isUpgradePlanDialogVisible = !isUpgradePlanDialogVisible"
          >
            Upgrade to Premium
          </VBtn>
        </VCardText>
      </VCard>
    </VCol> -->
    <!-- !SECTION -->
  </VRow>
  <!-- <UserUpgradePlanDialog v-model:isDialogVisible="isUpgradePlanDialogVisible" /> -->
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.75rem;
}

.current-plan{
  background: linear-gradient(45deg, rgb(var(--v-theme-primary)) 0%, #9E95F5 100%);
  color: #fff;
}
</style>
