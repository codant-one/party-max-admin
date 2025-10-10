<script setup>

import { integerValidator, requiredValidator } from '@/@core/utils/validators'
import { useInvoicesStores } from '@/stores/useInvoices'
import InvoiceEditable from '@/views/apps/invoice/InvoiceEditable.vue'
import router from '@/router'

const invoicesStores = useInvoicesStores()
//const billingsStores = useBillingsStores()
const route = useRoute()

const emitter = inject("emitter")

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Default Blank Data
const validateRemove = ref()
const validateSetting = ref()
const validate = ref()
const invoiceData = ref([])
const users = ref([])
const user = ref(null)
const invoice = ref(null)
const total = ref(0)
const last_record = ref(0)
const isRequestOngoing = ref(true)

const seeDialogRemove = ref(false)
const seeDialogSetting = ref(false)
const selectedInvoice = ref({})

watchEffect(fetchData)

async function fetchData() {

    if(Number(route.params.id)) { 

      isRequestOngoing.value = true
      const response = await invoicesStores.invoicesByUser(Number(route.params.id))
      
      console.log('response', response)
      
      if(response.success) {
        last_record.value = response.data.last_record + 1
        
        // Procesar los productos/servicios para la factura
        invoiceData.value = response.data.payments.map(payment => ({
          ...payment,
          disabled: true
        }));
        
        // Configurar el usuario
        user.value = {
          ...response.data.user,
          full_name: `${response.data.user.name} ${response.data.user.last_name}`
        }

        // Calcular el total inicial
        total.value = 0
        invoiceData.value.forEach(element => {
          if(element.state_id !== 3)
            total.value += Number(element.total)
        });
      }

      isRequestOngoing.value = false
    }
}

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

const addProduct = value => {
  invoiceData.value?.push(value)
}

const data = (data) => {
  invoice.value = data
}

const editProduct = () => {
  total.value = 0
  invoiceData.value.forEach(element => {
    if(element.state_id !== 3)
      total.value += Number(element.total)
  });
}

const deleteProduct = id => {
  invoiceData.value?.splice(id, 1)

  total.value = 0
  invoiceData.value.forEach(element => {
    if(element.state_id !== 3)
      total.value += Number(element.total)
  });
}

const removeProduct = id => {
  seeDialogRemove.value = true
  selectedInvoice.value = { ...invoiceData.value[id] }
}

const onSubmitRemove = () => {
  validateRemove.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      selectedInvoice.value.state_id = 3
      seeDialogRemove.value = false

      const index = invoiceData.value.findIndex(element => element.id === selectedInvoice.value.id)
      
      invoiceData.value[index] = selectedInvoice.value
      selectedInvoice.value = {}

      total.value = 0
      invoiceData.value.forEach(element => {
        if(element.state_id !== 3)
          total.value += Number(element.total)
      });
    }
  })
}

const settingProduct = id => {
  seeDialogSetting.value = true
  selectedInvoice.value = { ...invoiceData.value[id] }
}

const onSubmitSetting = () =>{
  validateSetting.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      selectedInvoice.value.state_id = 8
      seeDialogSetting.value = false

      const index = invoiceData.value.findIndex(element => element.id === selectedInvoice.value.id)
      
      invoiceData.value[index] = selectedInvoice.value
      selectedInvoice.value = {}

      total.value = 0
      invoiceData.value.forEach(element => {
        if(element.state_id !== 3)
          total.value += Number(element.total)
      });
    }
  })
}

const onSubmit = () => {

  validate.value?.validate().then(async ({ valid: isValid }) => {
  
    if (isValid) {

      let formData = new FormData()

      formData.append('start', invoice.value.start)
      formData.append('end', invoice.value.end)
      formData.append('payment_type', invoice.value.payment_type)
      formData.append('reference', invoice.value.reference)
      formData.append('image', invoice.value.image[0])
      formData.append('note', invoice.value.note)
      formData.append('total', total.value)
      formData.append('user_id', Number(route.params.id))

      invoiceData.value
        .filter(element => element.state_id !== 3)
        .forEach((element) => {
          formData.append(`payments[]`, JSON.stringify(element));
        });

      invoicesStores.addInvoice(formData)
        .then((res) => {
          let data = {
            message: 'Factura creada con éxito',
            error: false
          }

          router.push({ name : 'dashboard-invoices'})
          emitter.emit('toast', data)
      })
      .catch((err) => {
        advisor.value.show = true
        advisor.value.type = 'error'
        advisor.value.message = Object.values(err.message).flat().join('<br>')

        setTimeout(() => { 
          advisor.value.show = false
          advisor.value.type = ''
          advisor.value.message = ''
        }, 3000)
      
      })
    }
  })
}

</script>

<template>
  <VForm
    ref="validate"
    @submit.prevent="onSubmit"
    >
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
    <VRow v-if="advisor.show">
      <VCol cols="12">
        <VAlert
          v-if="advisor.show"
          :type="advisor.type"
          class="mb-6">       
          {{ advisor.message }}
        </VAlert>
      </VCol>
    </VRow>
    <VRow v-if="user">
      <!-- 👉 InvoiceEditable -->
      <VCol
        cols="12"
        md="9"
      >
        <InvoiceEditable
          :data="invoiceData"
          :users="users"
          :user="user"
          :total="total"
          :id="last_record"
          @push="addProduct"
          @remove="removeProduct"
          @delete="deleteProduct"
          @setting="settingProduct"
          @edit="editProduct"
          @data="data"
        />
      </VCol>

      <!-- 👉 Right Column: Invoice Action -->
      <VCol
        cols="12"
        md="3"
      >
        <VCard class="mb-8">
          <VCardText>
            <!-- 👉 Send Invoice -->
            <VBtn
              block
              prepend-icon="tabler-send"
              class="mb-2"
              type="submit"
            >
              Generar
            </VBtn>

            <!-- 👉 Preview -->
            <VBtn
              block
              color="default"
              variant="tonal"
              class="mb-2"
              :to="{ name: 'dashboard-invoices' }"
            >
              Regresar
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>

    </VRow>

    <VDialog
      v-model="seeDialogSetting"
      max-width="600"
      persistent
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="seeDialogSetting = false" />

      <!-- Dialog Content -->
      <VCard title="Ajustar Pago">
        <VForm 
          ref="validateSetting"
          @submit.prevent="onSubmitSetting"
        >
          <VCardText>
            <VTextarea
              v-model="selectedInvoice.description"
              label="DESCRIPCIÓN"
              rows="2"
              :rules="[requiredValidator]"
            />      
          </VCardText>
          <VCardText>
            <VTextField 
              v-model="selectedInvoice.total"
              type="number"
              label="PRECIO"
              :rules="[requiredValidator, integerValidator]"
            />      
          </VCardText>
          
          <VCardText class="d-flex justify-end gap-3 flex-wrap">
            <VBtn
              color="secondary"
              variant="tonal"
              @click="seeDialogSetting = false"
            >
              Cancelar
            </VBtn>
            <VBtn
                
              type="submit"
            >
              Guardar
            </VBtn>
          </VCardText>
        </VForm>
      </VCard>
    </VDialog>

    <VDialog
      v-model="seeDialogRemove"
      persistent
      class="v-dialog-sm"
    >
      <!-- Dialog close btn -->
      <DialogCloseBtn @click="seeDialogRemove = false" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Pago">
        <VForm
          ref="validateRemove"
          @submit.prevent="onSubmitRemove">
          <VCardText>
            Esta seguro que desea eliminar el pago <strong>{{ selectedInvoice.description }}</strong>?
          </VCardText>

        

          <VCardText class="d-flex justify-end gap-3 flex-wrap">
            <VBtn 
              color="secondary"
              variant="tonal"
              @click="seeDialogRemove = false"
            >
              Cancelar
            </VBtn>
            <VBtn            
              type="submit"
            >
              Eliminar
            </VBtn>
          </VCardText>
        </VForm>
      </VCard>
    </VDialog>
  </VForm>

</template>


<route lang="yaml">
  meta:
    action: crear
    subject: facturas
</route>