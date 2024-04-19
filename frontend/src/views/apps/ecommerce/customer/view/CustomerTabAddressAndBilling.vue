<script setup>

import { formatNumber } from '@/@core/utils/formatters'
import {requiredValidator} from '@/@core/utils/validators'
import { useSuppliersStores } from '@/stores/useSuppliers'
import AddEditAddressDialog from "@/components/dialogs/AddEditAddressDialog.vue";
import router from '@/router'

const refForm = ref()
const isFormValid = ref(false)
const cant_commission = ref(0)
const who_commission = ref(0)
const total_balance = ref(0)
const settings = ref(0)
const who_settings = ref(0)
const route = useRoute()
const suppliersStores = useSuppliersStores()

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const props = defineProps({
  addresses: {
    type: Object,
    required: false
  },
  customerData: {
    type: Object,
    required: false,
  },
  isSupplier: {
    type: Boolean,
    required: true,
  }
})

const emit = defineEmits([
  'submit',
  'delete',
  'copy',
  'download',
  'alert'
])

const show = ref([
  true,
  false,
  false,
])

const isEditAddressDialogVisible = ref(false)
const selectedAddress = ref({})
const addresses_ = ref(props.addresses)

const accountTypes = [
  {
    icon: {
      icon: 'mdi-cash-multiple',
      size: '40',
    },
    title: 'Cuenta Corriente',
    value: '1',
  },
  {
    icon: {
      icon: 'tabler-pig-money',
      size: '40',
    },
    title: 'Cuenta de Ahorros',
    value: '2',
  }
]

const type_account = ref('1')
const document = ref(null)
const icon_type = ref(null)

watch(() =>  
  props.addresses, (addreses_) => {
    addresses_.value = addreses_
  });

watchEffect(() => {
    if (!isEditAddressDialogVisible.value)
      selectedAddress.value = {}
})

const calculateBalance = () => {

total_balance.value = props.customerData.account.balance ?? 0;

if(props.customerData.account.retail_sales_amount !== null || props.customerData.account.wholesale_sales_amount !== null) {
  const retail_sales = parseFloat(props.customerData.account.retail_sales_amount ?? 0)
  const wholesale_sales = parseFloat(props.customerData.account.wholesale_sales_amount ?? 0)
  const total_sales = retail_sales + wholesale_sales
  const commission_retail = retail_sales * (parseFloat(props.customerData.commission ?? 0)/100)
  const commission_wholesale = wholesale_sales * (parseFloat(props.customerData.wholesale_commission??0)/100) 
  total_balance.value = total_sales - commission_retail - commission_wholesale
}

let data = {
  balance: total_balance.value,
  type_commission: 2
}

suppliersStores.updateBalance(route.params.id, data)
}

watchEffect(fetchData)

async function fetchData() {

  if(props.isSupplier && props.customerData.account !== null) {
    type_account.value = props.customerData.account.type_account.toString()

    if(props.customerData.account.file_account) {
      document.value = props.customerData.account.file_account.split('documents/')[1]
      switch (document.value.split('.')[1]) {
        case 'pdf':
          icon_type.value = 'tabler-file-type-pdf'
          break;
        case 'docx':
          icon_type.value = 'mdi-file-word'
          break;
        case 'doc':
          icon_type.value = 'mdi-file-word'
          break;
        case 'jpg':
          icon_type.value = 'tabler-file-type-jpg'
          break;
        default:
          icon_type.value = 'tabler-file-type-png'
          break;
      }
    }
  }

  cant_commission.value = props.customerData.commission ?? 0
  who_commission.value = props.customerData.wholesale_commission ?? 0

  calculateBalance()

}

const download = (file) => {
  let data = {
    icon: document.value.split('.')[1],
    document: file
  }
  emit('download', data)
}

const copy = (account) => {
  emit('copy', account)
}

const editAddress = addressData => {

  addressData.addresses_type_id = addressData.addresses_type_id.toString()
  addressData.default = (addressData.default) === 1 ? true : false
  addressData.country_id = addressData.province.country.name
  addressData.provinceOld_id = addressData.province.id
  addressData.province_id = addressData.province.name

  isEditAddressDialogVisible.value = true
  selectedAddress.value = { ...addressData }
}

const showDeleteDialog = addressData => {
  emit('delete', addressData)
}

const onSubmit = (address, method) => {
  emit('submit', address, method)
}

const addCommission = () => {
  refForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {

      let data = {
        commission: cant_commission.value,
        type_commission: 0
      }

      suppliersStores.updateCommission(route.params.id, data)
        .then(async response => {
          window.scrollTo(0, 0)

          advisor.value.show = true
          advisor.value.type = 'success'
          advisor.value.message = 'Comisión actualizada!'
                    
          emit('alert', advisor)

          setTimeout(() => {
            advisor.value.show = false
            advisor.value.type = ''
            advisor.value.message = ''
            emit('alert', advisor)
          }, 5000)

          let res = await suppliersStores.updateBalance(route.params.id, data)

          total_balance.value = res.data.data.supplierAccount.balance
      })
      
    }
  })

  settings.value = 0;
}

const addWhocommission = () => {
  refForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      let data = {
        wholesale_commission: who_commission.value,
        type_commission: 1
      }

      suppliersStores.updateCommission(route.params.id, data)
        .then(async response => {
          window.scrollTo(0, 0)
          advisor.value.show = true
          advisor.value.type = 'success'
          advisor.value.message = 'Comisión actualizada!'
                    
          emit('alert', advisor)

          setTimeout(() => {
            advisor.value.show = false
            advisor.value.type = ''
            advisor.value.message = ''
            emit('alert', advisor)
          }, 5000)

          let res = await suppliersStores.updateBalance(route.params.id, data)
          total_balance.value = res.data.data.supplierAccount.balance
      })
    }
  })

  who_settings.value = 0;
}

const changeSettings = () => {
  settings.value = settings.value === 1 ? 0 : 1;
}

const changeWhosettings = () => {
  who_settings.value = who_settings.value === 1 ? 0 : 1;
}

</script>

<template>
  <!-- eslint-disable vue/no-v-html -->

  <!-- 👉 Address Book -->
  <VCard class="mb-6" v-if="!props.isSupplier">
    <VCardText>
      <div class="d-flex justify-space-between mb-6 flex-wrap align-center gap-y-4 gap-x-6">
        <h5 class="text-h5">
          Direcciones
        </h5>
        <VBtn
          variant="tonal"
          @click="isEditAddressDialogVisible = !isEditAddressDialogVisible"
        >
          Agregar
        </VBtn>
      </div>
      <template
        v-for="(address, index) in addresses_"
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

          <div class="ms-5 iconsAddress">
            <VBtn
              icon
              variant="text"
              color="default"
              @click="editAddress(address)">
              <VIcon
                icon="tabler-pencil"
                class="flip-in-rtl"
              />
            </VBtn>
            <VBtn
              icon
              variant="text"
              color="default"
              @click="showDeleteDialog(address)">
              <VIcon
                icon="tabler-trash"
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
          v-if="index !== addresses_.length - 1"
          class="my-4"
        />
      </template>
    </VCardText>
  </VCard>

  <!-- 👉 Payment Methods -->
  <VRow>
    <VCol cols="12" v-if="props.isSupplier">
      <VCard title="Método de pago">
        <VCardText class="d-flex flex-column gap-y-4">
          <VCard border flat>
            <VCardText class="d-flex flex-sm-row flex-column pa-4">
              <VRow>
                <VCol cols="12" md="12" class="d-flex">
                  <CustomRadiosWithIcon
                    v-model:selected-radio="type_account"
                    :radio-content="accountTypes"
                    :grid-column="{ sm: '6', cols: '12' }"
                    readonly
                  />
                </VCol>
                <VCol cols="12" md="12">
                  <VList class="card-list mt-2">
                    <VListItem>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Nombre del Banco:
                          <span class="text-body-2">
                            {{ props.customerData.account?.name_bank }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Nro. de Cuenta:
                          <span class="text-body-2">
                            {{ props.customerData.account?.bank_account }}
                            <VBtn
                              v-if="props.customerData.account?.bank_account"
                              class="ml-2"
                              icon
                              size="x-small"
                              color="secondary"
                              variant="text"
                            >
                                          
                              <VIcon size="22" icon="tabler-copy" @click="copy(props.customerData.account?.bank_account)"/>
                              <VTooltip activator="parent" location="top">
                                Copiar
                              </VTooltip>
                            </VBtn>
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Certificación Bancaria:
                          <span class="text-body-2" v-if="document">
                            {{ document }}

                            <VTooltip
                              open-on-focus
                              location="top"
                              activator="parent"
                              text="Descargar">
                              <template v-slot:activator="{ props }">
                                <VIcon color="primary" :icon="icon_type" size="x-large" @click="download(document)" />
                              </template>
                            </VTooltip>
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold mt-10">
                          Saldo:
                          <span class="text-body-2">
                            COP {{ formatNumber(total_balance) ?? '0.00' }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Detal:
                          <span class="text-body-2">
                            COP {{ formatNumber(props.customerData.account?.retail_sales_amount) ?? '0.00' }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Por Mayor:
                          <span class="text-body-2">
                            COP {{ formatNumber(props.customerData.account?.wholesale_sales_amount) ?? '0.00' }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <VForm
                          ref="refForm"
                          v-model="isFormValid"
                          @submit.prevent="addCommission"
                        >
                          <VRow no-gutters>
                            <VCol cols="6" md="3">
                              <div class="d-flex align-center">
                                <label class="text-primary font-weight-bold">Comisión Detal PartyMax: {{ formatNumber(cant_commission) }}%</label>
                                <VBtn 
                                  icon="mdi-pencil"
                                  variant="text"
                                  size="small"
                                  @click="changeSettings" 
                                />
                              </div>
                              <div  v-if="settings === 1">
                                  <VTextField
                                    v-model="cant_commission"
                                    :rules="[requiredValidator]"
                                    type="number"
                                    min= 0
                                    max= 100 
                                    label="Comisión"
                                    style="margin-top: 10px"
                                  />

                                  <VBtn type="submit" style="margin-top:20px">
                                    Actualizar       
                                  </VBtn>
                              </div>
                              
                            </VCol>
                          </VRow>
                        </VForm>
                      </VListItemTitle>
                      <VListItemTitle>
                        <VForm
                          ref="refForm"
                          v-model="isFormValid"
                          @submit.prevent="addWhocommission"
                        >
                          <VRow no-gutters>
                            <VCol cols="6" md="3">
                              <div class="d-flex align-center">
                                <label class="text-primary font-weight-bold">Comisión Mayorista PartyMax: {{ formatNumber(who_commission) }}%</label>
                                <VBtn 
                                  icon="mdi-pencil"
                                  variant="text"
                                  size="small"
                                  @click="changeWhosettings" 
                                />
                              </div>
                              <div  v-if="who_settings === 1">
                                  <VTextField
                                    v-model="who_commission"
                                    :rules="[requiredValidator]"
                                    type="number"
                                    min= 0
                                    max= 100
                                    label="Comisión Mayorista"
                                    style="margin-top: 10px"
                                  />

                                  <VBtn type="submit" style="margin-top:20px">
                                    Actualizar       
                                  </VBtn>
                              </div>
                              
                            </VCol>
                          </VRow>
                        </VForm>
                      </VListItemTitle>  
                    </VListItem>
                  </VList>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  
  <AddEditAddressDialog 
    v-model:isDialogVisible="isEditAddressDialogVisible"
    :billing-address="selectedAddress"
    @submit="onSubmit"/>
</template>

<style>
  .iconsAddress .v-btn--icon.v-btn--density-default {
    width: calc(var(--v-btn-height) + 0px) !important;
    height: calc(var(--v-btn-height) + 0px) !important;
  }
</style>
