<script setup>

import InvoiceProductEdit from "@/components/invoice/InvoiceProductEdit.vue"
import { useInvoicesStores } from '@/stores/useInvoices'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { formatNumber } from '@/@core/utils/formatters'
import { requiredValidator } from '@validators'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  users: {
    type: Object,
    required: true,
  },
  user: {
    type: Object,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
  totalProducts: {
    type: Number,
    required: true,
  },
  commissionProducts: {
    type: Number,
    required: true,
  },
  amountCommissionProducts: {
    type: Number,
    required: true,
  },
  totalServices: {
    type: Number,
    required: true,
  },
  commissionServices: {
    type: Number,
    required: true,
  },
  amountCommissionServices: {
    type: Number,
    required: true,
  },
  totalLessCommission: {
    type: Number,
    required: true,
  },
  id: {
    type: Number,
    required: true,
  },
  start: {
    type: String,
    required: false,
    default: ""
  },
  end: {
    type: String,
    required: false,
    default: ""
  },
  note: {
    type: String,
    required: false,
    default: ""
  },
  typeInvoice: {
    type: String,
    required: true
  },
  disabled: {
    type: Boolean,
    required: false,
    default: true
  }
})

const invoicesStores = useInvoicesStores()

const emit = defineEmits([
  'push',
  'remove',
  'delete',
  'setting',
  'data',
  'edit'
])

const users = ref(props.users)
const user = ref(props.user)
const total = ref(props.total)
const totalProducts = ref(props.totalProducts)
const commissionProducts = ref(props.commissionProducts)
const amountCommissionProducts = ref(props.amountCommissionProducts)
const totalServices = ref(props.totalServices)
const commissionServices = ref(props.commissionServices)
const amountCommissionServices = ref(props.amountCommissionServices)
const totalLessCommission = ref(props.totalLessCommission)
const disabled = ref(props.disabled)
const typeInvoice = ref(props.typeInvoice)
const payment = ref(null)

const invoice = ref({
  id: props.id,
  start: props.start,
  end: props.end,
  note: props.note,
  payment_type: null,
  reference: null,
  image: [],
  payments: props.data
})

watch(props.data, val => {
  invoice.value.payments = val
})

watch(() => props.total, (val) => {
  total.value = val
})

watch(() => props.totalProducts, (val) => {
  totalProducts.value = val
})

watch(() => props.commissionProducts, (val) => {
  commissionProducts.value = val
})
watch(() => props.amountCommissionProducts, (val) => {
  amountCommissionProducts.value = val
})
watch(() => props.totalServices, (val) => {
  totalServices.value = val
})
watch(() => props.commissionServices, (val) => {
  commissionServices.value = val
})
watch(() => props.amountCommissionServices, (val) => {
  amountCommissionServices.value = val
})
watch(() => props.totalLessCommission, (val) => {
  totalLessCommission.value = val
})

const startDateTimePickerConfig = computed(() => {

  const now = new Date();
  const tomorrow = new Date(now);
  tomorrow.setDate(now.getDate() + 1);

  const formatToISO = (date) => date.toISOString().split('T')[0];


  const config = {
    dateFormat: 'Y-m-d',
    position: 'auto right',
    disable: [
      {
        from: '2099-12-30', // Una fecha futura lejana para bloquear indefinidamente //formatToISO(tomorrow),
        to: '2099-12-31' // Una fecha futura lejana para bloquear indefinidamente
      }
    ]
  }

  return config
})


const resolvePrice = (data) => {
  const priceMapping = {
    video_id: 'videos',
    title_optimization_id: 'optimizations',
    ia_image_id: 'images',
    redaction_id: 'redactions',
    task_id: 'tasks'
  };

  const key = Object.keys(priceMapping).find(k => data[k] !== null);

  if (key) {
    const priceKey = priceMapping[key];
    const price = payment.value[priceKey];
    total.value += price;
    return price
  }

  return 0;
};

const selectUser = async() => {

  invoice.value.payments = []
  total.value = 0

  const response = await invoicesStores.invoicesByUser(user.value.id)
  
  if(response.success) {
    invoice.value.payments = response.data.payments.map(payment => ({
      ...payment,
      disabled: true
    }));
    
    // Actualizar datos del usuario
    user.value = {
      ...response.data.user,
      full_name: `${response.data.user.name} ${response.data.user.last_name}`
    }

    // Calcular total
    total.value = 0
    invoice.value.payments.forEach(element => {
      if(element.state_id !== 3)
        total.value += Number(element.total)
    });

    invoice.value.user_id = user.value.id
    emit('data', invoice.value)
  }
}

// 👉 Add item function
const addItem = () => {
  emit('push', {
    id: 0,
    disabled: false,
    state_id: 6,
    video_id: null,
    title_optimization_id: null,
    ia_image_id: null,
    redaction_id: null,
    task_id: null,
    description: null,
    total: 0
  })
}

const removeProduct = id => {
  emit('remove', id)
}

const deleteProduct = id => {
  emit('delete', id)
}

const settingProduct = id => {
  emit('setting', id)
}

const inputData = () => {
  emit('data', invoice.value)
}
</script>

<template>
  <VCard class="pa-10">
    <VCardText class="d-flex flex-wrap justify-space-between flex-column flex-sm-row print-row bg-var-theme-background rounded">
      <div class="ma-sm-4">
        <div class="d-flex align-center mb-6">
          <!-- 👉 Logo -->
          <VNodeRenderer
            :nodes="themeConfig.app.logoFull"
            class="me-3"
          />

          <!-- 👉 Title -->
          <h6 class="font-weight-bold text-capitalize text-h4">
            {{ themeConfig.app.title }}
          </h6>
        </div>

        <!-- 👉 Address -->
        <p class="mb-0">
          Office 149, 450 South Brand Brooklyn
        </p>
        <p class="mb-0">
          San Diego County, CA 91905, USA
        </p>
        <p class="mb-0">
          +1 (123) 456 7891, +44 (876) 543 2198
        </p>
      </div>

      <!-- 👉 Right Content -->
      <div class="mt-4 ma-sm-4 text-right">
        <!-- 👉 Invoice Id -->
        <h6 class="d-flex align-center font-weight-medium justify-sm-end text-xl mb-1">
          <span class="me-2 text-h6">
            Factura:
          </span>

          <span>
            <VTextField
              v-model="invoice.id"
              disabled
              prefix="#"
              density="compact"
              style="inline-size: 10.5rem;"
            />
          </span>
        </h6>

        <!-- 👉 Issue Date -->
        <div class="d-flex align-center justify-sm-end mb-1 text-right">
          <span class="me-2">
            Fecha de emisión:
          </span>

          <span style="inline-size: 10.5rem;">
            <AppDateTimePicker
              :key="JSON.stringify(startDateTimePickerConfig)"
              v-model="invoice.start"
              density="compact"
              placeholder="YYYY-MM-DD"
              :rules="[requiredValidator]"
              :config="startDateTimePickerConfig"
              @input="inputData"
              clearable
              :disabled="typeInvoice !== '0'"
            />
          </span>
        </div>

        <!-- 👉 Due Date -->
        <div class="d-flex align-center justify-sm-end mb-0">
          <span class="me-2">
            Fecha de vencimiento:
          </span>

          <span style="min-inline-size: 10.5rem;">
            <AppDateTimePicker
              :key="JSON.stringify(startDateTimePickerConfig)"
              v-model="invoice.end"
              density="compact"
              placeholder="YYYY-MM-DD"
              :rules="[requiredValidator]"
              :config="startDateTimePickerConfig"
              @input="inputData"
              clearable
              :disabled="typeInvoice !== '0'"
            />
          </span>
        </div>
      </div>
    </VCardText>
    <!-- !SECTION -->

    <VCardText class="d-flex flex-wrap justify-space-between flex-column flex-sm-row gap-y-5 gap-4 px-0">
      <div
        class="my-sm-4"
      >
        <h6 class="text-h6 font-weight-medium">
          Facturar a:
        </h6> 

        <VAutocomplete
          v-model="user.id"
          :items="users"
          item-title="full_name"
          item-value="id"
          placeholder="Usuarios"
          class="d-none mb-3"
          :disabled="disabled"
          style="width: 300px"
          :rules="[requiredValidator]"
          @update:modelValue="selectUser"
          clearable
        />
        <p class="mb-0">
          <span class="font-weight-bold">Nombre: </span> {{ user.name }} {{ user.last_name }}
        </p>
        <p class="mb-0">
          <span class="font-weight-bold">Organización: </span> {{ user.supplier.company_name }}
        </p>
        <p
          v-if="user.address"
          class="mb-0"
        >
          <span class="font-weight-bold">Dirección: </span>{{ user.address }}, {{ user.country }}
        </p>
        <p class="mb-0">
          <span class="font-weight-bold">Teléfono: </span> {{ user.supplier.phone_contact }}
        </p>
        <p class="mb-0">
          <span class="font-weight-bold">E-mail: </span> {{ user.email }}
        </p>
      </div>

      <div 
        v-if="typeInvoice == '1'"
        class="mt-4 my-sm-4" style="width: 400px">
        <h6 class="text-h6 font-weight-medium mb-6">
          Datos del pago:
        </h6>
        <table class="w-100">
          <tbody>
            <tr>
              <td class="font-weight-bold pb-2">
                Total:
              </td>
              <td class="font-weight-bold pb-2 text-end">
                $ {{ formatNumber(total) }}
              </td>
            </tr>
            <tr>
              <td class="pb-2 font-weight-bold">
                Tipo de pago:
              </td>
              <td class="pb-2 w-70 text-end">
                <VTextField
                  v-model="invoice.payment_type"
                  placeholder="Tipo de pago"
                  label="Tipo de pago"
                  :rules="[requiredValidator]"
                  @input="inputData"
                  clearable
                />
              </td>
            </tr>
            
            <tr>
              <td class="pb-2 font-weight-bold">
                Referencia:
              </td>
              <td class="pb-2 w-70">
                <VTextField
                  v-model="invoice.reference"
                  placeholder="Referencia"
                  label="Referencia"
                  :rules="[requiredValidator]"
                  @input="inputData"
                  clearable
                />
              </td>
            </tr>
            <tr>
              <td class="pb-0 font-weight-bold">
                Imagen:
              </td>
              <td class="pb-0 w-70">
                <VFileInput
                  v-model="invoice.image"
                  accept="image/*,application/pdf"
                  value=""
                  prepend-icon="tabler-paperclip"
                  @change="inputData"
                  >
                  <template #selection="{ fileNames }">
                    <template
                      v-for="fileName in fileNames"
                      :key="fileName">
                      <VChip
                        label
                        size="small"
                        variant="outlined"
                        color="primary"
                        class="me-2">
                        {{ fileName }}
                      </VChip>
                    </template>
                  </template>
                </VFileInput>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </VCardText>

    <!-- 👉 Add purchased products -->
    <VCardText class="add-products-form px-0">
      <!-- eslint-disable vue/no-mutating-props -->
      <!-- Header fijo con títulos -->
      <div class="add-products-header mb-4 d-none d-md-flex ps-5 pe-16">
        <VRow class="font-weight-medium">
          <VCol
            cols="12"
            md="7"
          >
            <span class="text-base">
              DESCRIPCIÓN
            </span>
          </VCol>     

          <VCol
            cols="12"
            md="1"
            class="text-center"
          >
            <span class="text-base">
              QTY
            </span>
          </VCol>

          <VCol
            cols="12"
            md="2"
            class="text-end"
          >
            <span class="text-base">
              PRECIO
            </span>
          </VCol>

          <VCol
            cols="12"
            md="2"
            class="text-end"
          >
            <span class="text-base">
              TOTAL
            </span>
          </VCol>
        </VRow>
        <div class="d-flex flex-column justify-space-between pa-0" style="width: 1%;"></div>
      </div>

      <!-- Contenedor con scroll solo para los productos -->
      <div style="max-height: 400px; overflow-y: auto;">
        <div
          v-for="(product, index) in invoice.payments"
          :key="product.id"
          class="my-4"
        >
        
          <InvoiceProductEdit
            v-if="product.state_id !== 3"
            :id="index"
            :data="product"
            :typeInvoice="typeInvoice"
            :supplier="user.supplier"
            @remove-product="removeProduct"
            @delete-product="deleteProduct"
            @setting-product="settingProduct"
            @edit-product="$emit('edit')"
          />
        </div>
      </div>

      <!-- <div class="mt-4">
        <VBtn @click="addItem">
          Agregar pago
        </VBtn>
      </div> -->
    </VCardText>

    <VDivider />

    <!-- 👉 Total Amount -->
    <VCardText class="d-flex justify-space-between flex-wrap flex-column flex-sm-row px-0">
      <VSpacer />
      <div class="my-4">
        <!--
        <table class="w-100">
          <tbody>
            <tr>
              <td class="pe-16">
                Subtotal:
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm">
                  $ {{ formatNumber(total) }}
                </h6>
              </td>
            </tr>
            <tr>
              <td class="pe-16">
                Descuento:
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm">
                  $0.00
                </h6>
              </td>
            </tr>
            <tr>
              <td class="pe-16">
                Impuesto:
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm">
                  $0.00
                </h6>
              </td>
            </tr>
          </tbody>
        </table>

        <VDivider class="mt-4 mb-3" />
        -->

        <table class="w-100">
          <tbody>
            <tr>
              <td class="pe-16">
                Subtotal Productos:
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm">
                  {{ formatNumber(totalProducts) }} COP
                </h6>
              </td>
            </tr>
            <tr>
              <td class="pe-16">
                Comisión Productos ({{ formatNumber(commissionProducts) }} %):
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm text-warning">
                  <span v-if="amountCommissionProducts > 0">-</span> {{ formatNumber( amountCommissionProducts) }} COP
                </h6>
              </td>
            </tr>

            <tr>
              <td class="pe-16">
                Subtotal Servicios:
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm">
                  {{ formatNumber(totalServices) }} COP
                </h6>
              </td>
            </tr>
            <tr>
              <td class="pe-16">
                Comisión Servicios ({{ formatNumber(commissionServices) }} %):
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm text-warning">
                  <span v-if="amountCommissionServices > 0">-</span> {{ formatNumber( amountCommissionServices) }} COP
                </h6>
              </td>
            </tr>

             <tr>
              <td colspan="2">
                <VDivider class="mt-4 mb-3" />
              </td>
            </tr>

            <tr>
              <td class="pe-16">
                Total:
              </td>
              <td :class="$vuetify.locale.isRtl ? 'text-start' : 'text-end'">
                <h6 class="text-sm">
                  {{ formatNumber(totalLessCommission) }} COP
                </h6>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </VCardText>

    <VCardText 
      v-if="typeInvoice === '1'"
      class="mb-sm-4 px-0">
      <p class="font-weight-medium text-sm text-high-emphasis mb-2">
        Nota:
      </p>
      <VTextarea
        v-model="invoice.note"
        placeholder="Escribe una nota aquí (opcional)..."
        @input="inputData"
        :rows="2"
        :disabled="typeInvoice !== '1'"
      />
    </VCardText>
  </VCard>
</template>

<style scoped>
  .w-70 {
    width: 70% !important;
  }
</style>
