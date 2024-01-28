<script setup>

import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'
import { requiredValidator, phoneValidator } from '@/@core/utils/validators'

const props = defineProps({
  billingAddress: {
    type: Object,
    required: false
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
  'submit',
])

const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()

const id = ref(0)
const isEdit = ref(false)
const isFormValid = ref(false)
const refForm = ref()
const billingAddress = ref(null)
const provinceOld_id = ref('')

watchEffect(async() => {

    if (!(Object.entries(props.billingAddress).length === 0) && props.billingAddress.constructor === Object) {
        isEdit.value = true
        id.value = props.billingAddress.id
        billingAddress.value = props.billingAddress
        provinceOld_id.value = props.billingAddress.provinceOld_id
    } else {
        billingAddress.value = {
            addresses_type_id: '1',
            country_id: 'Colombia',
            province_id: '',
            title: '',
            street: '',
            city: '',
            address: '',
            phone: '',
            postal_code: null,
            default: false
        }
    }
})

const resetForm = () => {
    emit('update:isDialogVisible', false)
    billingAddress.value = {
        addresses_type_id: '1',
        country_id: 'Colombia',
        province_id: '',
        title: '',
        street: '',
        city: '',
        address: '',
        phone: '',
        postal_code: null,
        default: false
    }
    isEdit.value = false 
    id.value = 0
}

const onFormSubmit = () => {
    refForm.value?.validate().then(({ valid }) => {
        if (valid) {
            billingAddress.value.province_id = (Number.isInteger(billingAddress.value.province_id)) ? billingAddress.value.province_id : provinceOld_id.value
            emit('update:isDialogVisible', false)
            emit('submit',  { data: billingAddress.value, id: id.value }, isEdit.value ? 'update' : 'create')
            resetForm()
        }
    })
}

const addressTypes = [
  {
    icon: {
      icon: 'mdi-home-city',
      size: '40',
    },
    title: 'Hogar',
    desc: 'Hora de entrega (7 a.m. - 9 p.m.)',
    value: '1',
  },
  {
    icon: {
      icon: 'mdi-office-building',
      size: '40',
    },
    title: 'Oficina',
    desc: 'Hora de entrega (10 a.m. - 6 p.m.)',
    value: '2',
  },
]

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])

const getProvinces = computed(() => {
  return listProvincesByCountry.value.map((province) => {
    return {
      title: province.name,
      value: province.id,
    }
  })
})

onMounted(async () => {
    await countriesStores.fetchCountries();
    await provincesStores.fetchProvinces();

    loadCountries()
    loadProvinces()
    selectCountry( billingAddress.value.country_id) 
})


const selectCountry = country => {
  if (country) {
    let _country = listCountries.value.find(item => item.name === country)
    billingAddress.value.country_id = _country.name
 
    billingAddress.value.province_id = ''
    listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
  }
}

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
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
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 610 "
    :model-value="props.isDialogVisible"
    @update:model-value="val => $emit('update:isDialogVisible', val)"
  >
    <!-- 👉 Dialog close btn -->
    <DialogCloseBtn @click="$emit('update:isDialogVisible', false)" />

    <VCard
      v-if="props.billingAddress"
      class="pa-sm-8 pa-5"
    >
      <!-- 👉 Title -->
      <VCardItem>
        <VCardTitle class="text-h3 text-center">
          {{ props.billingAddress.address ? 'Editar' : 'Agregar Nueva' }} Dirección
        </VCardTitle>
      </VCardItem>

      <VCardText>
        <!-- 👉 Subtitle -->
        <VCardSubtitle class="text-center mb-6">
          <span class="text-base">

            Agregar nueva dirección para entrega
          </span>
        </VCardSubtitle>

        <div class="d-flex">
          <CustomRadiosWithIcon
            v-model:selected-radio="billingAddress.addresses_type_id"
            :radio-content="addressTypes"
            :grid-column="{ sm: '6', cols: '12' }"
          />
        </div>

        <!-- 👉 Form -->
        <VForm
            class="mt-4"
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onFormSubmit"
        >
          <VRow>
            <!-- 👉 Title -->
            <VCol
              cols="12"
              md="12"
            >
              <AppTextField
                v-model="billingAddress.title"
                label="Descripción"
                placeholder="Descripción"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Country -->
            <VCol
              cols="12"
              md="6"
            >
            <VAutocomplete
                v-model="billingAddress.country_id"
                label="País"
                :rules="[requiredValidator]"
                :items="listCountries"
                item-title="name"
                item-value="name"
                :menu-props="{ maxHeight: '200px' }"
                @update:model-value="selectCountry"
                >
                <template
                    v-if="billingAddress.country_id"
                    #prepend
                    >
                    <VAvatar
                        start
                        style="margin-top: -8px;"
                        size="36"
                        :image="getFlagCountry(billingAddress.country_id)"
                    />
                  </template>
                </VAutocomplete>
            </VCol>

            <!-- 👉 Province -->
            <VCol
              cols="12"
              md="6"
            >
                <VAutocomplete
                    v-model="billingAddress.province_id"
                    label="Estado"
                    :rules="[requiredValidator]"
                    :items="getProvinces"
                    :menu-props="{ maxHeight: '200px' }"
                  />
            </VCol>

            <!-- 👉 City -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="billingAddress.city"
                label="Ciudad"
                placeholder="Ciudad"
                :rules="[requiredValidator]"
              />
            </VCol>

             <!-- 👉 Street -->
             <VCol
              cols="12"
              md="6"
            >
                <AppTextField
                v-model="billingAddress.street"
                label="Calle"
                placeholder="Calle"
              />
            </VCol>

            <!-- 👉 Billing Address -->
            <VCol cols="12">
              <VTextarea
                v-model="billingAddress.address"
                rows="2"
                label="Dirección"
                placeholder="1, Pixinvent Street, USA"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Phone -->
            <VCol
              cols="12"
              md="6"
            >
                <AppTextField
                v-model="billingAddress.phone"
                label="Teléfono"
                placeholder="+57 23 456 7890"
                :rules="[requiredValidator, phoneValidator]"
              />
            </VCol>

            <!-- 👉 Postal Code -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="billingAddress.postal_code"
                label="Código Postal"
                placeholder="123123"
                :rules="[requiredValidator, phoneValidator]"
              />
            </VCol>
            <VCol cols="12" md="8"></VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VCheckbox
                v-model="billingAddress.default"
                label="Dir. por Defecto"
                true-icon="tabler-check"
                false-icon="tabler-x"
                />
            </VCol>

            <!-- 👉 Submit and Cancel button -->
            <VCol
              cols="12"
              class="text-center"
            >
              <VBtn
                type="submit"
                class="me-3"
              >
                Enviar
              </VBtn>

              <VBtn
                variant="tonal"
                color="secondary"
                @click="resetForm"
              >
                Cancelar
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
