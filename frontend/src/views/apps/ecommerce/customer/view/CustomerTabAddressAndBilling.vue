<script setup>

import americanExpress from '@images/icons/payments/img/american-express.png'
import mastercard from '@images/icons/payments/img/mastercard.png'
import visa from '@images/icons/payments/img/visa-light.png'

const props = defineProps({
  addresses: {
    type: Object,
    required: true
  }
})

const show = ref([
  true,
  false,
  false,
])

const paymentShow = ref([
  true,
  false,
  false,
])

const isEditAddressDialogVisible = ref(false)
const isCardAddDialogVisible = ref(false)


const paymentData = [
  {
    title: 'Mastercard',
    subtitle: 'Expries Apr 2028',
    isDefaultMethod: false,
    image: mastercard,
  },
  {
    title: 'American Express',
    subtitle: 'Expries Apr 2028',
    isDefaultMethod: false,
    image: americanExpress,
  },
  {
    title: 'Visa',
    subtitle: '45 Roker Terrace',
    isDefaultMethod: true,
    image: visa,
  },
]
</script>

<template>
  <!-- eslint-disable vue/no-v-html -->

  <!-- 👉 Address Book -->
  <VCard class="mb-6">
    <VCardText>
      <div class="d-flex justify-space-between mb-6 flex-wrap align-center gap-y-4 gap-x-6">
        <h5 class="text-h5">
          Direcciones
        </h5>
        <VBtn
          v-if="false"
          variant="tonal"
          @click="isEditAddressDialogVisible = !isEditAddressDialogVisible"
        >
          Agregar
        </VBtn>
      </div>
      <template
        v-for="(address, index) in props.addresses"
        :key="index"
      >
        <div class="d-flex justify-space-between mb-4 gap-y-2 flex-wrap align-center">
          <div class="d-flex align-center gap-x-1">
            <VBtn
              icon
              variant="text"
              color="default"
              size="x-small"
              @click="show[index] = !show[index]"
            >
              <VIcon
                :icon="show[index] ? 'tabler-chevron-down' : 'tabler-chevron-right'"
                class="flip-in-rtl"
              />
            </VBtn>

            <div>
              <div class="d-flex">
                <h6 class="text-h6 me-2">
                  {{ address.type.name }}
                </h6>
                <VChip
                  v-if="address.default"
                  color="success"
                  label
                >
                  Dirección por defecto
                </VChip>
              </div>
              <span class="text-body-2 text-disabled">{{ address.title }}</span>
            </div>
          </div>

          <div class="ms-5 iconsAddress" v-if="false">
            <VBtn
              icon
              variant="text"
              color="default">
              <VIcon
                icon="tabler-pencil"
                class="flip-in-rtl"
              />
            </VBtn>
            <VBtn
              icon
              variant="text"
              color="default">
              <VIcon
                icon="tabler-trash"
                class="flip-in-rtl"
              />
            </VBtn>
            <VBtn
              icon
              variant="text"
              color="default">
              <VIcon
                icon="tabler-dots-vertical"
                class="flip-in-rtl"
              />
            </VBtn>
          </div>
        </div>
        <VExpandTransition>
          <div
            v-show="show[index]"
            class="px-10"
          >
            <h6 class="mb-1 text-h6">
              {{ address.address }}
            </h6>
            <div
              class="text-body-1"
              v-html="address.street"
            />
            <div
              class="text-body-1"
              v-html="address.city"
            />
            <div
              class="text-body-1"
              v-html="address.postal_code"
            />
            <div
              class="text-body-1"
              v-html="address.phone"
            />
          </div>
        </VExpandTransition>
        <VDivider
          v-if="index !== addresses.length - 1"
          class="my-4"
        />
      </template>
    </VCardText>
  </VCard>

  <!-- 👉 Payment Methods -->
  <VCard v-if="false">
    <VCardText>
      <div class="d-flex justify-space-between mb-6 flex-wrap align-center gap-y-4 gap-x-6">
        <h5 class="text-h5">
          Payment Methods
        </h5>
        <VBtn
          variant="tonal"
          @click="isCardAddDialogVisible = !isCardAddDialogVisible"
        >
          Add Payment Methods
        </VBtn>
      </div>
      <template
        v-for="(payment, index) in paymentData"
        :key="index"
      >
        <div class="d-flex justify-space-between mb-4 gap-y-2 flex-wrap align-center">
          <div class="d-flex align-center">
            <VBtn
              icon
              variant="text"
              color="default"
              size="x-small"
              @click="paymentShow[index] = !paymentShow[index]"
            >
              <VIcon
                :icon="paymentShow[index] ? 'tabler-chevron-down' : 'tabler-chevron-right'"
                class="flip-in-rtl"
              />
            </VBtn>

            <VImg
              :src="payment.image"
              height="30"
              width="50"
              class="me-3"
            />

            <div>
              <div class="d-flex gap-x-2">
                <h6 class="text-h6">
                  {{ payment.title }}
                </h6>
                <VChip
                  v-if="payment.isDefaultMethod"
                  color="success"
                  label
                >
                  Default Method
                </VChip>
              </div>
              <span class="text-body-2 text-disabled">{{ payment.subtitle }}</span>
            </div>
          </div>

          <div class="ms-5">
            <VBtn
              icon
              variant="text"
              color="default">
              <VIcon
                icon="tabler-pencil"
                class="flip-in-rtl"
              />
            </VBtn>
            <VBtn
              icon
              variant="text"
              color="default">
              <VIcon
                icon="tabler-trash"
                class="flip-in-rtl"
              />
            </VBtn>
            <VBtn
              icon
              variant="text"
              color="default">
              <VIcon
                icon="tabler-dots-vertical"
                class="flip-in-rtl"
              />
            </VBtn>
          </div>
        </div>
        <VExpandTransition>
          <div
            v-show="paymentShow[index]"
            class="px-8"
          >
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTable>
                  <tr>
                    <td>Name </td>
                    <td class="font-weight-medium">
                      Violet Mendoza
                    </td>
                  </tr>
                  <tr>
                    <td>Number </td>
                    <td class="font-weight-medium">
                      **** 4487
                    </td>
                  </tr>
                  <tr>
                    <td>Expires </td>
                    <td class="font-weight-medium">
                      08/2028
                    </td>
                  </tr>
                  <tr>
                    <td>Type </td>
                    <td class="font-weight-medium">
                      Master Card
                    </td>
                  </tr>
                  <tr>
                    <td>Issuer </td>
                    <td class="font-weight-medium">
                      VICBANK
                    </td>
                  </tr>
                  <tr>
                    <td>ID </td>
                    <td class="font-weight-medium">
                      DH73DJ8
                    </td>
                  </tr>
                </VTable>
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTable>
                  <tr>
                    <td>Billing </td>
                    <td class="font-weight-medium">
                      United Kingdom
                    </td>
                  </tr>
                  <tr>
                    <td>Number</td>
                    <td class="font-weight-medium">
                      +7634 983 637
                    </td>
                  </tr>
                  <tr>
                    <td>Email</td>
                    <td class="font-weight-medium">
                      vafgot@vultukir.org
                    </td>
                  </tr>
                  <tr>
                    <td>Origin</td>
                    <td class="font-weight-medium">
                      United States
                    </td>
                  </tr>
                  <tr>
                    <td>CVC Check</td>
                    <td class="font-weight-medium">
                      Passed
                      <VAvatar
                        class="ms-2"
                        color="success"
                        size="20"
                        variant="tonal"
                      >
                        <VIcon
                          icon="tabler-check"
                          size="14"
                        />
                      </VAvatar>
                    </td>
                  </tr>
                </VTable>
              </VCol>
            </VRow>
          </div>
        </VExpandTransition>
        <VDivider
          v-if="index !== paymentData.length - 1"
          class="my-4"
        />
      </template>
    </VCardText>
  </VCard>
  <!-- <AddEditAddressDialog v-model:isDialogVisible="isEditAddressDialogVisible" /> -->
  <!-- <CardAddEditDialog v-model:isDialogVisible="isCardAddDialogVisible" /> -->
</template>

<style>
  .iconsAddress .v-btn--icon.v-btn--density-default {
    width: calc(var(--v-btn-height) + 0px) !important;
    height: calc(var(--v-btn-height) + 0px) !important;
  }
</style>
