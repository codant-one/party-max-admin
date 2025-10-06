<script setup>

import { requiredValidator, phoneValidator } from '@validators'
import { useAddressesStores } from '@/stores/addresses'
import { useCountriesStores } from '@/stores/countries'
import { useProvincesStores } from '@/stores/provinces'
import CustomRadiosWithIcon from '@/components/app/CustomRadiosWithIcon.vue'
import icon_more from '@assets/icons/more-vertical.svg?inline';
import icon_address from '@assets/icons/icon-domicilio.svg?inline';
import icon_office from '@assets/icons/Portfolio.svg?inline';
import icon_right from '@assets/icons/right-pink.svg?inline';
import Loader from '@/components/common/Loader.vue'

import check_circle from '@assets/icons/check-circle.svg';
import error_circle from '@assets/icons/error-circle.svg';

const addressesStores = useAddressesStores()
const provincesStores = useProvincesStores()
const countriesStores = useCountriesStores()

const load = ref(false)
const refVForm = ref()
const isLoading = ref(true)
const isMobile = /Mobi/i.test(navigator.userAgent)
const isEdit = ref(false)

const selectedAddress = ref({
    id: 0,
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
})

const addresses = ref(null)
const dialog = ref(false)
const userDataJ = ref(null)
const isDialogVisible = ref(false)
const message = ref()
const isError = ref(false)

const addressTypes = [
  {
    icon: {
      icon: 'mdi-home-city',
      size: '50',
    },
    title: 'Hogar',
    value: '1',
  },
  {
    icon: {
      icon: 'mdi-office-building',
      size: '50',
    },
    title: 'Oficina',
    value: '2',
  },
]

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])
const client_country_id = ref(null)
const provinceOld_id = ref('')

const getProvinces = computed(() => {
  return listProvincesByCountry.value.map((province) => {
    return {
      title: province.name,
      value: province.id,
    }
  })
})

onMounted(async () => {

    isLoading.value = true

    await countriesStores.getAll();
    await provincesStores.getAll();

    loadCountries()
    loadProvinces()

    selectCountry(selectedAddress.value.country_id)

    isLoading.value = false
})

watchEffect(fetchData)

async function fetchData() { 
    
    isLoading.value = true

    if(localStorage.getItem('user_data')){
      const userData = localStorage.getItem('user_data')
      userDataJ.value = JSON.parse(userData)

      selectedAddress.value.client_id = userDataJ.value.client.id
    }

    let data = {
        limit: -1,
        client_id: userDataJ.value.client.id
    }

    await addressesStores.fetchAddresses(data)
    addresses.value = addressesStores.getAddresses

    isLoading.value = false
}

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
}

const selectCountry = country => {
  if (country) {
    let _country = listCountries.value.find(item => item.name === country)
    client_country_id.value = _country.name
 
    selectedAddress.value.province_id = null
    
    listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
  }
}

const edit = (addressData) => {

    selectedAddress.value = {
        id: addressData.id,
        addresses_type_id: addressData.addresses_type_id.toString(),
        country_id: addressData.province.country.name,
        province_id: addressData.province.name,
        title: addressData.title,
        street: addressData.street,
        city: addressData.city,
        address: addressData.address,
        phone: addressData.phone,
        postal_code: addressData.postal_code,
        default: (addressData.default === 0) ? false : true,
        client_id: addressData.client_id
    }

    provinceOld_id.value = addressData.province_id

    dialog.value = true
    isEdit.value = true
}

const onSubmit = () => {
    refVForm.value?.validate().then(({ valid: isValid }) => {
        if (isValid) {
            if(isEdit.value)
                updateAddress()
            else
                addAddress()
        }
    })

}

const addAddress = () => {
    load.value = true

    selectedAddress.value.default = (selectedAddress.value.default === false) ? 0 : 1
    selectedAddress.value.province_id = (Number.isInteger(selectedAddress.value.province_id)) ? selectedAddress.value.province_id : provinceOld_id.value,

    addressesStores.addAddress(selectedAddress.value)
        .then(response => {

            isDialogVisible.value = true
            message.value = 'Dirección creada exitosamente'
            closeDialog()

            fetchData()

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
            }, 3000)

            load.value = false                    
            
        }).catch(err => {

            load.value = false

            if(err.message === 'error'){
                isDialogVisible.value = true
                message.value = err.errors
                isError.value = true
            } else {
                isDialogVisible.value = true
                isError.value = true
                message.value = 'Se ha producido un error...! (Server Error)'
            }                    

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
            }, 3000)

            // console.error(err.message)
        })

}

const updateAddress = () => {

    load.value = true

    selectedAddress.value.default = (selectedAddress.value.default === false) ? 0 : 1
    selectedAddress.value.province_id = (Number.isInteger(selectedAddress.value.province_id)) ? selectedAddress.value.province_id : provinceOld_id.value,

    addressesStores.updateAddress(selectedAddress.value, selectedAddress.value.id)
        .then(response => {

            isDialogVisible.value = true
            message.value = 'Dirección actualizada exitosamente'
            closeDialog()

            fetchData()

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
                
            }, 3000)

            load.value = false                    
            
        }).catch(err => {

            load.value = false

            if(err.message === 'error'){
                isDialogVisible.value = true
                message.value = err.errors
                isError.value = true
            } else {
                isDialogVisible.value = true
                isError.value = true
                message.value = 'Se ha producido un error...! (Server Error)'
            }                    

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
            }, 3000)

            // console.error(err.message)
        })
}

const removeAddress = (addressData) => {

    isLoading.value = true 

    addressesStores.deleteAddress(addressData.id)
        .then(response => {
            closeDialog()
            isDialogVisible.value = true
            message.value = 'Dirección eliminada exitosamente'
            isLoading.value = false 

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
                fetchData()
            }, 3000)

            load.value = false                    
            
        }).catch(err => {

            load.value = false
            isLoading.value = false
            
            if(err.message === 'error'){
                isDialogVisible.value = true
                message.value = err.errors
                isError.value = true
            } else {
                isDialogVisible.value = true
                isError.value = true
                message.value = 'Se ha producido un error...! (Server Error)'
            }                    

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
            }, 3000)

            // console.error(err.message)
        })
}

const closeDialog = () => {

    isEdit.value = 0
    dialog.value = false
    selectedAddress.value = {
        id: 0,
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
    <Loader :isLoading="isLoading"/>
    <VContainer class="mt-1 mt-md-10 container-dashboard" v-if="addresses">
   
        <h2 class="data-title mt-5 pt-md-7">Domicilios</h2>

        <VCard class="card-profile px-0 py-0">
            <VCardText class="d-flex align-center py-0 px-7 px-md-12 mb-2" 
                v-for="address in addresses">
                <icon_office v-if="address.addresses_type_id === 2" class="mt-5 mb-3 mb-md-2 mt-md-6"/>
                <icon_address v-else class="mt-5 mb-3 mb-md-2 mt-md-6"/>
                <v-icon v-if="address.default === 1" color="primary" icon="mdi-check-circle" class="circle" />
                <div class="d-block ms-2 ms-md-5 mt-6 mb-2 mb-md-2 mt-md-6">
                    <span class="d-block labels tw-text-tertiary">{{ address.address }}</span>
                    <span class="d-block labels tw-text-gray">{{ address.province.name }}</span>
                    <span class="d-block labels tw-text-gray">{{ address.title }}</span>
                </div>
                <VSpacer />
                <VMenu >
                    <template v-slot:activator="{ props }">
                    <VBtn variant="plain" icon class="pb-2 user more" v-bind="props">
                        <icon_more class="mt-3 mt-md-2"/>
                    </VBtn>
                    </template>
                    <VList>
                        <VListItem class="px-0">
                            <VListItemTitle class="px-3" @click="edit(address)">
                                <div class="d-flex hover:tw-text-primary">
                                    <v-icon icon="mdi-pencil" size="small" class="me-2 mt-1"/>
                                    <span>Editar</span>
                                </div>
                            </VListItemTitle>
                            <VListItemTitle class="px-3 mt-2" @click="removeAddress(address)">
                                <div class="d-flex hover:tw-text-primary">
                                    <v-icon icon="mdi-trash-can-outline" size="small" class="me-2 mt-1"/>
                                    <span>Eliminar</span>
                                </div>
                            </VListItemTitle>
                        </VListItem>
                    </VList>
              </VMenu>
             </VCardText>       
             <VCardText 
                :class="(addresses.length > 0) ? 'border_line' : ''"
                class="d-flex align-center py-0 px-7 px-md-12 mb-2" 
                @click="dialog=true">
                <span class="labels tw-text-primary mt-3 mb-1 mt-md-3 mb-md-2">Agregar domicilio</span>
                <VSpacer />
                <icon_right class="icon-right me-5 mt-2 mt-md-1"/>  
            </VCardText>
        </VCard>
    </VContainer>

    <!--MODAL ADD ADDRESS-->
    <VDialog v-model="dialog" transition="dialog-top-transition">
        <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
        > 
            <VCard class="pb-2 pb-md-4 no-shadown card-register d-block text-center mx-auto">
                <VCardText class="subtitle-register p-0 mt-0 mt-md-7">
                    AGREGAR NUEVA DIRECCIÓN
                </VCardText>           
                <VCardItem class="pb-0 px-3 px-md-10">
                    <VRow no-gutters class="text-left align-center">
                        <VCol cols="12" md="12" class="textinput mb-0 mb-md-2 mt-3">
                            <CustomRadiosWithIcon
                                v-model:selected-radio="selectedAddress.addresses_type_id"
                                :radio-content="addressTypes"
                                :grid-column="{ sm: '6', cols: '6' }"
                            />
                        </VCol>  
                        <VCol cols="12" md="12" class="textinput mb-2 mb-md-2">
                            <VTextField
                                label="Descripción"
                                v-model="selectedAddress.title"
                                variant="outlined"
                                :rules="[requiredValidator]"
                                />
                        </VCol>
                        <VCol cols="12" md="6" class="textinput mb-0 mb-md-2">
                            <VAutocomplete
                                variant="outlined"
                                v-model="selectedAddress.country_id"
                                label="País"
                                :rules="[requiredValidator]"
                                :items="listCountries"
                                item-title="name"
                                item-value="name"
                                :menu-props="{ maxHeight: '200px' }"
                                @update:model-value="selectCountry"
                                class="me-0 me-md-2"
                                >
                                <template
                                    v-if="selectedAddress.country_id"
                                    #prepend
                                    >
                                    <VAvatar
                                        start
                                        style="margin-top: -8px;"
                                        :size="isMobile ? '30' : '36'"
                                        :image="getFlagCountry(selectedAddress.country_id)"
                                    />
                                </template>
                            </VAutocomplete>
                        </VCol>  
                        <VCol cols="12" md="6" class="textinput mb-0 mb-md-2">
                            <VAutocomplete
                                variant="outlined"
                                v-model="selectedAddress.province_id"
                                label="Estado"
                                :rules="[requiredValidator]"
                                :items="getProvinces"
                                :menu-props="{ maxHeight: '200px' }"
                            />    
                        </VCol> 
                        <VCol cols="12" md="6" class="textinput mb-0 mb-md-2">
                            <VTextField
                                label="Ciudad"
                                v-model="selectedAddress.city"
                                variant="outlined"
                                :rules="[requiredValidator]"
                                class="me-0 me-md-2"
                                />
                        </VCol>  
                        <VCol cols="12" md="6" class="textinput mb-0 mb-md-2">
                            <VTextField
                                label="Localidad / Barrio"
                                v-model="selectedAddress.street"
                                variant="outlined"
                                /> 
                        </VCol> 
                        <VCol cols="12">
                            <VTextarea
                                v-model="selectedAddress.address"
                                rows="2"
                                label="Dirección"
                                variant="outlined"
                                :rules="[requiredValidator]"
                            />
                        </VCol>
                        <VCol cols="12" md="6" class="textinput mb-0 mb-md-2">
                            <VTextField
                                v-model="selectedAddress.phone"
                                label="Teléfono"
                                placeholder="+57 23 456 7890"
                                variant="outlined"
                                class="me-0 me-md-2"
                                :rules="[requiredValidator, phoneValidator]"
                            />
                        </VCol>  
                        <VCol cols="12" md="6" class="textinput mb-0 mb-md-2">
                            <VTextField
                                v-model="selectedAddress.postal_code"
                                label="Código Postal"
                                variant="outlined"
                            />    
                        </VCol> 
                        <VCol cols="12" md="7"></VCol>
                        <VCol cols="12" md="5" class="mb-3 mb-md-0">
                            <VCheckbox
                                v-model="selectedAddress.default"
                                color="primary"
                                label="Dirección por defecto"
                                true-icon="mdi-check-bold"
                                false-icon="mdi-window-close"
                                class="ms-md-3"
                            />
                        </VCol>
                    </VRow>
                </VCardItem>
                <VCardActions class="px-10 d-flex justify-content-center">
                    <VSpacer class="d-none d-md-block"/>
                    <VBtn
                        variant="flat"   
                        type="submit"
                        class="btn-register tw-text-white tw-bg-primary button-hover"
                        >
                        Enviar
                        <VProgressCircular
                            v-if="load"
                            indeterminate
                            color="#fff"
                        />
                    </VBtn>
                    <VBtn
                        color="primary"
                        variant="outlined"
                        class="btn-register"
                        @click="closeDialog"
                    >
                    Cerrar
                    </VBtn>
                </VCardActions>
            </VCard>
        </VForm>
    </VDialog>
    
    <!--PopUp Message-->

    <VDialog v-model="isDialogVisible" >
        <VCard
            class="px-10 py-14 pb-2 pb-md-4 no-shadown card-register d-block text-center mx-auto">
            <VImg :width="isMobile ? '120' : '180'" :src="isError ? error_circle : check_circle" class="mx-auto"/>
            <VCardText class="text-message mt-10 mb-5">
                {{ message }}
            </VCardText>
        </VCard>
    </VDialog>
</template>

<style scoped>

    .more:hover::v-deep(path){
        stroke: #FF0090;
    }

    .text-message {
        color:  #FF0090;
        text-align: center;
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: 30px; 
        padding: 0 80px !important;
    }

    .subtitle-register {
        color: #FF0090;
        text-align: center;
        font-size: 23px;
        font-style: normal;
        font-weight: 600;
        line-height: 30px;  
    }

    .circle {
        margin-top: 6%;
        margin-left: -2%;
    }

    .border_line {
        border-top: 1px solid #E1E1E1;
    }

    .container-dashboard {
        padding: 0 15%;
    }

    .data-title {
        color: #0A1B33;
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: normal;
        text-align: left;
    }

    .card-profile {
        padding: 16px 32px;
        margin-top: 24px;
        border-radius: 16px;
        box-shadow: none;
    }

    .labels {
        font-size: 15px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px; /* 133.333% */
    }

    .icon-more {
        width: 24px;
        height: 24px;
        margin: auto;
    }

    .icons {
        width: 56px;
        height: 56px;
        border-radius: 27px;
        border: 1px solid var(--Grey-2, #E1E1E1);
        background: var(--White, #FFF);
    }

    .address-add {
        padding: 24px;
        border-top: 1px solid var(--Grey-2, #E1E1E1);
    }

    .row-form {
        padding: 0px 32px;
    }

    .btn-register {
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 14px;
        border-radius: 32px;
    }

    .button-hover:hover {
        background-color: #FF27B3 !important;
        box-shadow: 0px 0px 24px 0px #FF27B3;
    }

    .card-register {
        width: 500px;
        border-radius: 32px!important;
    }

    .card-form {
        width: 800px;
    }

    .textinput .v-text-field::v-deep(.v-field) { 
        border-radius: 24px;
        height: 35px;
        font-size: 14px;
    }

    .v-text-field::v-deep(.v-field__outline__start) {
        flex: 0 0 17px !important;
    }

    .v-text-field::v-deep(::placeholder) { 
        color: #999999 !important;
        opacity: inherit;
    }

    .v-text-field::v-deep(input) { 
        padding-top: 0 !important;
        padding-left: 20px !important;
    }

    .v-text-field::v-deep(.v-input__details) {
        min-height: 15px !important;
    }

    .textinput .v-text-field::v-deep(.v-field-label) {
        top: 33% !important;
        font-size: 14px !important;
    }

    .v-textarea::v-deep(.v-field-label) {
        top: 10% !important;
        font-size: 14px !important;
    }

    .v-text-field::v-deep(.v-field__append-inner) { 
        padding-top: 8px !important;
        align-items: start !important;
    }

    .v-autocomplete::v-deep(.v-field__overlay) {
        background: white !important;
    }

    .v-autocomplete::v-deep(.v-field__input) { 
        padding-top: 0 !important;
    }

    .v-textarea::v-deep(.v-field) { 
        border-radius: 24px !important;
    }

    .v-checkbox::v-deep(.v-input__details) { 
        min-height: 0 !important;
        padding: 0 !important;
        height: 0 !important;
    }

    .v-checkbox::v-deep(.v-label) {
        color:#0A1B33;
        font-size: 13px;
        font-style: normal;
        font-weight: 400;
        line-height: 18px; /* 138.462% */
        margin-left: 10px;
    }

    @media only screen and (max-width: 767px) {

        .v-checkbox::v-deep(.v-selection-control) {
            text-align: center;
            align-items: center; 
            justify-content: center; 
            min-height: 35px;
        }
        
        .labels {
            font-size: 11px;
        }

        .container-dashboard {
            padding: 0 5%;
        }

        .card-form {
            width: 350px;
        }

        .circle {
            margin-top: 15%;
            margin-left: -6%;
        }

        .card-register, .card-password {
            width: auto !important;
            padding: 40px 20px !important;
        }

        .text-message {
            padding: 0 30px !important;
            font-size: 18px;
        }
    }
</style>