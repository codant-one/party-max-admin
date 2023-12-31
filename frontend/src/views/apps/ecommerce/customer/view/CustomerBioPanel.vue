<script setup>

import { themeConfig } from '@themeConfig'
import { avatarText } from '@/@core/utils/formatters'
import { useCountriesStores } from '@/stores/useCountries'
import rocketImg from '@images/eCommerce/rocket.png'

const countriesStores = useCountriesStores()

const props = defineProps({
  customerData: {
    type: null,
    required: true,
  },
})

const isUserInfoEditDialogVisible = ref(false)
const isUpgradePlanDialogVisible = ref(false)

const listCountries = ref([])

watchEffect(fetchData)

async function fetchData() {
  await countriesStores.fetchCountries();

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
                <VIcon icon="tabler-shopping-cart" />
              </VAvatar>
              <div class="d-flex flex-column align-start">
                <span class="text-body-1 font-weight-medium">181</span>
                <span class="text-body-2">Ordenes</span>
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
                <span class="text-body-1 font-weight-medium">COP 2.000.000</span>
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
              <div class="text-body-1">
                <span class="font-weight-medium me-2">Nombre:</span>
                <span>
                  {{ props.customerData.user.name }} {{ props.customerData.user.last_name ?? '' }}
                </span>
              </div>
            </VListItem>

            <VListItem>
              <div class="text-body-1">
                <span class="font-weight-medium me-2">E-mail:</span>
                <span>
                  {{ props.customerData.user.email }}
                </span>
              </div>
            </VListItem>


            <VListItem>
              <div class="text-body-1">
                <span class="font-weight-medium me-2">Teléfono:</span>
                <span>
                  {{ props.customerData.user.user_detail.phone }}
                </span>
              </div>
            </VListItem>

            <VListItem>
              <div class="text-body-1">
                <span class="font-weight-medium me-2">País:</span>
                  <VAvatar
                      start
                      size="25"
                      :image="getFlagCountry(props.customerData.user.user_detail.province.country.name)"
                    />

                  <span class="ms-2">
                    {{ props.customerData.user.user_detail.province.country.name }}
                  </span>
              </div>
            </VListItem>
          </VList>
        </VCardText>

        <!-- <VCardText class="text-center">
          <VBtn @click="isUserInfoEditDialogVisible = !isUserInfoEditDialogVisible">
            Edit Details
          </VBtn>
        </VCardText> -->
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
  <UserInfoEditDialog v-model:isDialogVisible="isUserInfoEditDialogVisible" />
  <UserUpgradePlanDialog v-model:isDialogVisible="isUpgradePlanDialogVisible" />
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
