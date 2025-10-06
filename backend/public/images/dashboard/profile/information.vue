<script setup>

import { useAuthStores } from '@/stores/auth'
import { useProfileStores } from '@/stores/profile'
import { useCountriesStores } from '@/stores/countries'
import { useProvincesStores } from '@/stores/provinces'
import { useGendersStores } from '@/stores/genders'
import { useDocumentTypesStores } from '@/stores/document-types'
import { requiredValidator } from '@validators'
import Loader from '@/components/common/Loader.vue'
import router from '@/router'

import check_circle from '@assets/icons/check-circle.svg';
import error_circle from '@assets/icons/error-circle.svg';

const profileStores = useProfileStores()
const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()
const gendersStores = useGendersStores()
const authStores = useAuthStores()
const documentTypesStores = useDocumentTypesStores()

const load = ref(false)
const refVForm = ref()
const isLoading = ref(true)
const isMobile = /Mobi/i.test(navigator.userAgent)

const name = ref(null)
const usermail = ref(null)
const phone = ref(null)
const document = ref(null)
const type_document = ref('')
const country = ref(null)
const province = ref(null)
const birthday = ref(null)
const address = ref(null)
const gender = ref(null)
const dialog = ref(false)
const isDialogVisible = ref(false)
const message = ref()
const isError = ref(false)

const newfname = ref(null) 
const newlname = ref(null)
const newdocument = ref(null)
const newdocument_type_id = ref('')
const newdocument_typeOld_id = ref('')
const newaddress = ref(null)
const newbirthday = ref(null)
const newgender = ref(null)
const newprovince = ref(null)

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])
const client_country_id = ref(null)
const countryOld_id = ref(null)
const province_id = ref('')
const provinceOld_id = ref('')
const genders = ref('')
const documentTypes = ref([])

const errors = ref({
  newfname: undefined,
  newlname: undefined,
  newdocument: undefined,
  newdocument_type_id: undefined,
  newaddress: undefined,
  newbirthday: undefined,
  newgender: undefined,
  newprovince: undefined
})


const inputChange = () => {
  errors.value = {
    newfname: undefined,
    newlname: undefined,
    newdocument: undefined,
    newdocument_type_id: undefined,
    newaddress: undefined,
    newbirthday: undefined,
    newgender: undefined,
    newprovince: undefined
  }
}

const getProvinces = computed(() => {
  return listProvincesByCountry.value.map((province) => {
    return {
      title: province.name,
      value: province.id,
    }
  })
})

const getGenders = computed(() => {
  return genders.value.map((gender) => {
    return {
      title: gender.name,
      value: gender.id,
    }
  })
})


onMounted(async () => {

    isLoading.value = true

    await countriesStores.getAll();
    await provincesStores.getAll();
    await gendersStores.getAll();

    loadCountries()
    loadProvinces()
    loadGenders()

    selectCountry(countryOld_id.value)
    fetchData()
    isLoading.value = false
})


watchEffect(fetchData)

async function fetchData() { 
    if(localStorage.getItem('user_data')){
        const userData = localStorage.getItem('user_data')
        const userDataJ = JSON.parse(userData)
        name.value = userDataJ.name + ' ' +(userDataJ.last_name ?? '')
        usermail.value = userDataJ.email
        phone.value = userDataJ.user_details.phone
        document.value = userDataJ.user_details.document ?? '----'
        country.value = userDataJ.user_details.province.country.name
        address.value = userDataJ.user_details.address ?? '----'
        birthday.value = userDataJ.client.birthday ?? '----'
        gender.value = userDataJ.client.gender.name 
        province.value =  userDataJ.user_details.province.name

        type_document.value = userDataJ.user_details?.document_type?.name ?? '----'

        client_country_id.value = userDataJ.user_details.province.country.name
        countryOld_id.value = userDataJ.user_details.province.country.name

        province_id.value = userDataJ.user_details.province.name
        provinceOld_id.value = userDataJ.user_details.province_id

        newfname.value = userDataJ.name
        newlname.value = userDataJ.last_name
        newdocument.value = userDataJ.user_details.document
        newdocument_type_id.value = userDataJ.user_details?.document_type_id
        newdocument_typeOld_id.value = userDataJ.user_details?.document_type?.name
        newaddress.value = userDataJ.user_details.address
        newbirthday.value = userDataJ.client.birthday
        newgender.value = userDataJ.client.gender_id 
        newprovince.value = userDataJ.user_details.province_id

        await documentTypesStores.fetchDocumentTypes()
        documentTypes.value = documentTypesStores.getData
    }
}

const getDocumentTypes = computed(() => {
    return documentTypes.value.map((documentType) => {
        return {
        title: '(' + documentType.code + ') - ' + documentType.name,
        value: documentType.id,
        }
    })
})

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
}

const loadGenders= () => {
    genders.value = gendersStores.getGenders
}

const selectCountry = country => {
  if (country) {
    let _country = listCountries.value.find(item => item.name === country)
    client_country_id.value = _country.name
 
    province_id.value = null
    
    listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
  }
}

const slugify = (text) => {
  return text
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-')
    .replace(/^-+/, '')
    .replace(/-+$/, '')
}

const onSubmit = () => {
    refVForm.value?.validate().then(({ valid: isValid }) => {
        if (isValid) {

            load.value = true

            let data = {
                name: newfname.value,
                last_name: newlname.value,
                document_type_id: Number.isInteger(newdocument_type_id.value) ? newdocument_type_id.value : newdocument_typeOld_id.value ,
                document: newdocument.value,
                address: newaddress.value,
                gender_id: newgender.value,
                province_id: (Number.isInteger(province_id.value)) ? province_id.value : provinceOld_id.value,
                username: slugify(newfname.value + ' ' + newlname.value),
                birthday: newbirthday.value
            }

            profileStores.update_profile(data)
                .then(response => {

                    isDialogVisible.value = true
                    message.value = 'Datos actualizados exitosamente'
                    dialog.value = false

                    refresh()
                    setTimeout(() => {
                        isDialogVisible.value = false
                        message.value = ''
                        isError.value = false
                        router.push({ name: 'profile' })
                    }, 3000)

                    load.value = false                    
                    
                }).catch(err => {

                    load.value = false
                    dialog.value = false

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
    
    })
}

const refresh = async () => {
    if(localStorage.getItem('user_data')){
        const userData = localStorage.getItem('user_data')
        const userDataJ = JSON.parse(userData)

        const { user_data, userAbilities } = await authStores.me(userDataJ.hash)

        localStorage.setItem('userAbilities', JSON.stringify(userAbilities))
        localStorage.setItem('user_data', JSON.stringify(user_data))
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
    <VContainer class="mt-1 mt-md-10 container-dashboard" v-if="listCountries.length > 0">

        <h2 class="data-title mt-5 pt-md-7">Mis datos</h2>
        <h6 class="data-subtitle tw-text-tertiary">Datos de cuenta</h6>

        <VCard class="card-profile">
            <VRow no-gutters class="align-center">
                <VCol cols="12" md="4"><span class="labels tw-text-tertiary">E-mail</span> </VCol>
                <VCol cols="12" md="8"> <span class="labels tw-text-gray">{{ usermail }}</span></VCol>
            </VRow>
        </VCard>
            
        <VCard class="card-profile px-0 mb-5">
            <VCardItem class="px-8 border_text">
                <VCardTitle>Datos personales</VCardTitle>
                <template v-slot:append>
                    <VBtn variant="plain" class="edit-button" @click="dialog=true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit icon-edit" width="25" height="25" viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                            <path d="M16 5l3 3" />
                        </svg>
                    </VBtn>
                </template>
            </VCardItem>
            <VCardItem class="px-8">
                <VRow no-gutters class="align-center my-5">
                    <VCol cols="12" md="4" class="mb-0 mb-md-4">
                        <span class="labels tw-text-tertiary">Nombre y apellido</span>
                    </VCol>
                    <VCol cols="12" md="8" class="mb-2 mb-md-4">
                        <span class="labels tw-text-gray">{{ name }}</span>
                    </VCol>
                    <VCol cols="12" md="4" class="mb-0 mb-md-4">
                        <span class="labels tw-text-tertiary">Tipo de documento</span>
                    </VCol>
                    <VCol cols="12" md="8" class="mb-2 mb-md-4">
                        <span class="labels tw-text-gray">{{ type_document }}</span>
                    </VCol>
                    <VCol cols="12" md="4" class="mb-0 mb-md-4">
                        <span class="labels tw-text-tertiary">Documento</span>
                    </VCol>
                    <VCol cols="12" md="8" class="mb-2 mb-md-4">
                        <span class="labels tw-text-gray">{{ document }}</span>
                    </VCol>
                    <VCol cols="12" md="4" class="mb-0 mb-md-4">
                        <span class="labels tw-text-tertiary">País</span>
                    </VCol>
                    <VCol cols="12" md="8" class="mb-2 mb-md-4">
                        <VAvatar
                            start
                            size="25"
                            :image="getFlagCountry(country)"
                            />
                        <span class="labels tw-text-gray ms-2">
                            {{ country }}
                        </span>
                    </VCol>
                    <VCol cols="12" md="4" class="mb-0 mb-md-4">
                        <span class="labels tw-text-tertiary">Provincia</span>
                    </VCol>
                    <VCol cols="12" md="8" class="mb-2 mb-md-4">
                        <span class="labels tw-text-gray">{{ province }}</span>
                    </VCol>
                    <VCol cols="12" md="4" class="mb-0 mb-md-4">
                        <span class="labels tw-text-tertiary">Dirección</span>
                    </VCol>
                    <VCol cols="12" md="8" class="mb-2 mb-md-4">
                        <span class="labels tw-text-gray">{{ address }}</span>
                    </VCol>
                    <VCol cols="12" md="4" class="mb-0 mb-md-4">
                        <span class="labels tw-text-tertiary">Fecha de cumpleaños</span>
                    </VCol>
                    <VCol cols="12" md="8" class="mb-2 mb-md-4">
                        <span class="labels tw-text-gray">{{ birthday }}</span>
                    </VCol>
                    <VCol cols="12" md="4">
                        <span class="labels tw-text-tertiary">Género</span>
                    </VCol>
                    <VCol cols="12" md="8">
                        <span class="labels tw-text-gray">{{ gender }}</span>
                    </VCol>
                </VRow>
            </VCardItem>  
        </VCard>

        <!--MODAL ACTUALIZAR DATOS-->
        <VDialog v-model="dialog" transition="dialog-top-transition">
            <VForm
                ref="refVForm"
                @submit.prevent="onSubmit"> 
                <VCard 
                    class="pb-2 pb-md-4 no-shadown card-register d-block text-center mx-auto card-form">
                    <VCardText class="subtitle-register p-0 mt-0 mt-md-7">
                        ACTUALIZAR DATOS
                    </VCardText>
                    <VCardItem class="pb-0 px-5 px-md-10">
                        <VRow no-gutters class="text-left align-center">
                            <VCol cols="12" md="6" class="textinput mb-0 mb-md-2">
                                <VTextField
                                    label="Nombre"
                                    v-model="newfname"
                                    variant="outlined"
                                    :rules="[requiredValidator]"
                                    :error-messages="errors.newfname"
                                    @input="inputChange()"
                                    class="mt-2 me-0 me-md-2"
                                    />
                            </VCol>
                            <VCol cols="12" md="6" class="textinput mb-2">
                                <VTextField
                                    label="Apellido"
                                    v-model="newlname"
                                    variant="outlined"
                                    :rules="[requiredValidator]"
                                    :error-messages="errors.newlname"
                                    @input="inputChange()"
                                    class="mt-2"
                                />
                            </VCol>
                            <VCol cols="12" md="6" class="textinput mb-2">
                                <VTextField
                                    label="Fecha de nacimiento"
                                    v-model="newbirthday"
                                    type="date"
                                    variant="outlined"
                                    class="me-0 me-md-2"
                                    :rules="[requiredValidator]"
                                    :error-messages="errors.newbirthday"
                                     @input="inputChange()"
                                />
                            </VCol>
                            <VCol cols="12" md="6" class="textinput mb-2">
                                <VAutocomplete
                                    variant="outlined"
                                    v-model="newgender"
                                    label="Género"
                                    :rules="[requiredValidator]"
                                    :items="getGenders"
                                    :menu-props="{ maxHeight: '200px' }"
                                    />
                            </VCol>
                            
                            <VCol cols="12" md="6" class="textinput mb-2">
                                <VAutocomplete
                                    variant="outlined"
                                    v-model="newdocument_type_id"
                                    label="Tipo de Documento"
                                    :rules="[requiredValidator]"
                                    :items="getDocumentTypes"
                                    class="me-0 me-md-2"
                                    :menu-props="{ maxHeight: '200px' }"
                                    /> 
                            </VCol>
                            <VCol cols="12" md="6" class="textinput mb-2">
                                <VTextField
                                    label="Nro Documento"
                                    v-model="newdocument"
                                    variant="outlined"
                                    :rules="[requiredValidator]"
                                    :error-messages="errors.newdocument"
                                    @input="inputChange()"
                                />
                            </VCol>
                            <VCol cols="12" md="6" class="textinput mb-2">
                                <VAutocomplete
                                    variant="outlined"
                                    v-model="client_country_id"
                                    label="País"
                                    :rules="[requiredValidator]"
                                    :items="listCountries"
                                    item-title="name"
                                    item-value="name"
                                    :menu-props="{ maxHeight: '200px' }"
                                    @update:model-value="selectCountry"
                                    class="me-0 me-md-2"
                                    readonly>
                                    <template 
                                        v-if="client_country_id"
                                        #prepend
                                        >
                                        <VAvatar
                                            start
                                            style="margin-top: -8px;"
                                            :size="isMobile ? '30' : '36'"
                                            :image="getFlagCountry(client_country_id)"
                                            />
                                    </template>
                                </VAutocomplete>
                            </VCol>
                            <VCol cols="12" md="6" class="textinput mb-2">
                                <VAutocomplete
                                    variant="outlined"
                                    v-model="province_id"
                                    label="Provincias"
                                    :rules="[requiredValidator]"
                                    :items="getProvinces"
                                    :menu-props="{ maxHeight: '200px' }"
                                />
                            </VCol>
                             <VCol cols="12">
                                <VTextarea
                                    rows="3"
                                    label="Dirección"
                                    v-model="newaddress"
                                    variant="outlined"
                                    :rules="[requiredValidator]"
                                    :error-messages="errors.newaddress"
                                    @input="inputChange()"
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
                            @click="dialog = false"
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
    </VContainer>
</template>

<style scoped>

    .subtitle-register {
        color: #FF0090;
        text-align: center;
        font-size: 23px;
        font-style: normal;
        font-weight: 600;
        line-height: 30px;  
    }

    .container-dashboard {
        padding: 0 15%;
    }

    .border_text {
        border-bottom: 1px solid #E1E1E1;
    }   

    .data-title {
        color: #0A1B33;
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: normal;
        text-align: left;
    }

    .data-subtitle {
        font-size: 16px;
        font-style: normal;
        font-weight: 600;
        line-height: normal;
        margin-top: 32px;
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

    .icon-edit {
        stroke:#FF0090;   
    }

    .link-menu {
        text-decoration: none;
    }

    .edit-button {
        box-shadow: none;
    }

    .edit-button .icon-edit:hover {
        stroke:#FF27B3; 
        transform: scale(1.2);
    }

    .text-close {
        font-size:18px;
        font-weight: 700;
    }

    .row-form {
        padding: 0px 32px;
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
        color: #0A1B33 !important;
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

    .text-message {
        color:  #FF0090;
        text-align: center;
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: 30px; 
        padding: 0 80px !important;
    }

    @media only screen and (max-width: 767px) {
        .container-dashboard {
            padding: 0 5%;
        }

        .labels {
            font-size: 13px;
        }

        .col-info {
            padding-left: 0 !important;
        }

        .card-form {
            width: 350px;
        }

        .card-register {
            width: auto;
            padding: 40px 20px !important;
        }

        .text-message {
            padding: 0 30px !important;
            font-size: 18px;
        }
    }
</style>