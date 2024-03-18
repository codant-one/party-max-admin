<script setup>

import CustomerOrderTable from './CustomerOrderTable.vue'
import { formatNumber } from '@/@core/utils/formatters'

const props = defineProps({
  customerData: {
    type: Object,
    required: true,
  },
  isSupplier: {
    type: Boolean,
    required: true
  }
})

const balance = ref(null)

watchEffect(fetchData)

async function fetchData() {

  if(props.isSupplier)
    balance.value = (props.customerData.account === null) ? '0.00' : props.customerData.account.balance

}

</script>

<template>
  <Suspense>
    <VRow>
      <VCol
        v-if="props.isSupplier"
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText class="d-flex gap-y-2 flex-column">
            <VAvatar
              variant="tonal"
              color="primary"
              icon="tabler-currency-dollar"
              rounded
            />
            <h4 class="text-h4">
              Saldo de la Cuenta
            </h4>
            <div>
              <span class="text-primary text-h4 me-2">COP {{ formatNumber(balance) ?? '0.00' }}</span>
              <span class="text-body-1">Crédito restante</span>
              <p class="mb-0 text-base text-disabled">
                Saldo de cuenta para el proveedor
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        v-if="props.isSupplier"
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText class="d-flex gap-y-2 flex-column">
            <VAvatar
              variant="tonal"
              color="success"
              icon="tabler-gift-card"
              rounded
            />
            <h4 class="text-h4">
              Plan
            </h4>
            <div>
              <VChip
                color="success"
                class="mb-2"
                label
              >
                Platinum Miembro
              </VChip>
              <p class="mb-0 text-base text-disabled">
                3000 puntos para el siguiente nivel
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        v-if="!props.isSupplier"
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText class="d-flex gap-y-2 flex-column">
            <VAvatar
              variant="tonal"
              color="warning"
              icon="tabler-star"
              rounded
            />
            <h4 class="text-h4">
              Lista de deseos
            </h4>
            <div>
              <p class="mb-1">
                <span class="text-warning text-h5 me-2">{{ props.customerData.user.favorites.length }}</span>
                <span class="text-body-1">artículos en la lista de deseos</span>
              </p>
              <p class="mb-0 text-disabled">
                Artículos agregados por el cliente.
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        v-if="!props.isSupplier"
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText class="d-flex gap-y-1 flex-column">
            <VAvatar
              variant="tonal"
              color="info"
              icon="tabler-discount-2"
              rounded
            />
            <h4 class="text-h4 mb-2">
              Cupones
            </h4>
            <div>
              <p class="mb-1">
                <span class="text-info text-h5 me-2">0</span>
                <span class="text-body-1">Cupones que obtuvieron</span>
              </p>
              <p class="mb-0 text-disabled">
                Para usar cupón en sus próximas compras
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="12">
        <CustomerOrderTable v-if="!props.isSupplier"/>
      </VCol>
    </VRow>
  </Suspense>
</template>
