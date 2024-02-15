<script setup>

import router from '@/router'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { emailValidator, requiredValidator, phoneValidator } from '@/@core/utils/validators'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'
import { useDocumentTypesStores } from '@/stores/useDocumentTypes'

const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()
const documentTypesStores = useDocumentTypesStores()
const suppliersStores = useSuppliersStores()

const emitter = inject("emitter")

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])
const documentTypes = ref([])
const isRequestOngoing = ref(true)

const isFormValid = ref(false)
const refForm = ref()
const currentTab = ref('tab-1')

const supplier_country_id = ref('Colombia')
const countryOld_id = ref('Colombia')
const province_id = ref('')
const name = ref('')
const last_name = ref('')
const main_document = ref('')
const email = ref('')
const phone = ref('')
const phone_contact = ref('')
const address = ref('')
const company_name = ref('')
const document_type_id = ref('')
const file_nit = ref([])
const file_rut = ref([])
const file_account = ref([])
const ncc = ref([])
const type_account = ref('1')
const name_bank = ref('')
const bank_account = ref('')

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
    },
]

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
}

onMounted(async () => {

    await countriesStores.fetchCountries()

    loadCountries()
})

const selectCountry = country => {
    if (country) {
        let _country = listCountries.value.find(item => item.name === country)
        supplier_country_id.value = _country.name
    
        province_id.value = null
        
        listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
    }
}

watchEffect(async() => {

    isRequestOngoing.value = true

    await provincesStores.fetchProvinces()
    loadProvinces()

    await documentTypesStores.fetchDocumentTypes()
    documentTypes.value = documentTypesStores.getDocumentTypes

    selectCountry(countryOld_id.value)

    isRequestOngoing.value = false
})

const getDocumentTypes = computed(() => {
    return documentTypes.value.map((documentType) => {
        return {
        title: '(' + documentType.code + ') - ' + documentType.name,
        value: documentType.id,
        }
    })
})

const getProvinces = computed(() => {
    return listProvincesByCountry.value.map((province) => {
        return {
        title: province.name,
        value: province.id,
        }
    })
})

const getFlagCountry = country => {
  let val = listCountries.value.find(item => {
    return item.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") === country.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  })

  if(val)
    return 'https://hatscripts.github.io/circle-flags/flags/'+val.iso.toLowerCase()+'.svg'
  else
    return ''
}

const onSubmit = () => {
    refForm.value?.validate().then(({ valid }) => {
        if (valid) {

            let formData = new FormData()

            //company
            formData.append('company_name', company_name.value)
            formData.append('document_type_id', document_type_id.value)
            formData.append('main_document', main_document.value)
            formData.append('phone_contact', phone_contact.value)
            formData.append('file_nit', file_nit.value[0])
            formData.append('file_rut', file_rut.value[0])
            formData.append('ncc', ncc.value)

            //bank
            formData.append('type_account', Number(type_account.value))
            formData.append('name_bank', name_bank.value)
            formData.append('bank_account', bank_account.value)
            formData.append('file_account', file_account.value[0])

            //contact
            formData.append('name', name.value)
            formData.append('last_name', last_name.value)
            formData.append('email', email.value)
            formData.append('phone', phone.value)
            formData.append('province_id', province_id.value)
            formData.append('address', address.value)

            isRequestOngoing.value = true

            suppliersStores.addSupplier(formData)
                .then((res) => {
                    if (res.data.success) {
                        
                        let data = {
                            message: 'Proveedor creado!',
                            error: false
                        }

                        router.push({ name : 'dashboard-admin-suppliers'})
                        emitter.emit('toast', data)
                    }
                    isRequestOngoing.value = false
                })
                .catch((err) => {
                    
                    let data = {
                        message: err,
                        error: true
                    }

                    router.push({ name : 'dashboard-admin-suppliers'})
                    emitter.emit('toast', data)

                    isRequestOngoing.value = false
                })
        }
    })
}
</script>

<template>
    <section>
        <v-row>
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
        </v-row>
        <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit">
            <VCard flat class="px-10">
                <VCardText class="px-10">                
                    <VTabs
                        v-model="currentTab"
                        grow
                        stacked>
                        <VTab>
                            <VIcon
                            icon="mdi-domain"
                            class="mb-2"
                            />
                            <span>Empresa</span>
                        </VTab>
                        <VTab>
                            <VIcon
                            icon="mdi-bank"
                            class="mb-2"
                            />
                            <span>Datos Bancarios</span>
                        </VTab>
                        <VTab>
                            <VIcon
                            icon="mdi-account-tie"
                            class="mb-2"
                            />
                            <span>Contacto</span>
                        </VTab>
                    </VTabs>
                    <VCardText>
                        <VWindow v-model="currentTab" class="pt-3">
                            <!-- company -->
                            <VWindowItem>
                            <VRow>
                                <!-- Company Name -->
                                <VCol cols="12">
                                    <VTextField
                                        v-model="company_name"
                                        :rules="[requiredValidator]"
                                        label="Empresa"
                                    />
                                </VCol>

                                <!-- 👉 Document Types -->
                                <VCol cols="6">
                                    <VAutocomplete
                                        v-model="document_type_id"
                                        label="Tipo de Documento"
                                        :rules="[requiredValidator]"
                                        :items="getDocumentTypes"
                                        :menu-props="{ maxHeight: '200px' }"
                                    />
                                </VCol>

                                <!-- 👉 Document -->
                                <VCol cols="6">
                                    <VTextField
                                        v-model="main_document"
                                        :rules="[requiredValidator, phoneValidator]"
                                        label="Documento"
                                    />
                                </VCol>

                                <!-- NIT -->
                                <VCol cols="6">
                                    <VFileInput
                                        v-model="file_nit"
                                        label="NIT"
                                        prepend-icon="tabler-paperclip"
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
                                </VCol>

                                <!-- RUT -->
                                <VCol cols="6">
                                    <VFileInput
                                        v-model="file_rut"
                                        label="RUT"
                                        prepend-icon="tabler-paperclip"
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
                                </VCol>

                                <!-- 👉 NCC -->
                                <VCol cols="6">
                                    <VTextField
                                        v-model="ncc"
                                        :rules="[requiredValidator]"
                                        label="Número de la Cámara de Comercio"
                                    />
                                </VCol>

                                <!-- 👉 Phone -->
                                <VCol cols="6">
                                    <VTextField
                                        v-model="phone_contact"
                                        :rules="[requiredValidator, phoneValidator]"
                                        label="Teléfono"
                                    />
                                </VCol>
                            </VRow>
                            </VWindowItem>
                            <!-- bank -->
                            <VWindowItem>
                                <VRow>
                                    <!-- Type Account  -->
                                    <VCol cols="12" class="d-flex">
                                        <CustomRadiosWithIcon
                                            v-model:selected-radio="type_account"
                                            :radio-content="accountTypes"
                                            :grid-column="{ sm: '6', cols: '12' }"
                                        />
                                    </VCol>

                                    <!-- 👉 Name Bank-->
                                    <VCol cols="12">
                                        <VTextField
                                            v-model="name_bank"
                                            :rules="[requiredValidator]"
                                            label="Nombre del Banco"
                                        />
                                    </VCol>

                                    <!-- 👉 Name Bank-->
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="bank_account"
                                            :rules="[requiredValidator]"
                                            label="Nro. de Cuenta"
                                        />
                                    </VCol>

                                    <!-- ACCOUNT -->
                                    <VCol cols="6">
                                        <VFileInput
                                            v-model="file_account"
                                            label="Certificación Bancaria"
                                            prepend-icon="tabler-paperclip"
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
                                    </VCol>
                                </VRow>
                            </VWindowItem>
                            <!-- contact -->
                            <VWindowItem>
                                <VRow>
                                    <!-- 👉 Name -->
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="name"
                                            :rules="[requiredValidator]"
                                            label="Nombre"
                                        />
                                    </VCol>

                                    <!-- 👉 Last Name -->
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="last_name"
                                            :rules="[requiredValidator]"
                                            label="Apellido"
                                        />
                                    </VCol>

                                    <!-- 👉 Email -->
                                    <VCol cols="6">
                                        <VTextField
                                            :rules="[emailValidator, requiredValidator]"
                                            v-model="email"
                                            label="Email"
                                        />
                                    </VCol>

                                    <!-- 👉 Phone -->
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="phone"
                                            :rules="[requiredValidator, phoneValidator]"
                                            label="Teléfono"
                                        />
                                    </VCol>

                                    <!-- 👉 Paises -->
                                    <VCol cols="6">
                                        <VAutocomplete
                                            v-model="supplier_country_id"
                                            label="País"
                                            :rules="[requiredValidator]"
                                            :items="listCountries"
                                            item-title="name"
                                            item-value="name"
                                            :menu-props="{ maxHeight: '200px' }"
                                            @update:model-value="selectCountry"
                                        >
                                            <template
                                            v-if="supplier_country_id"
                                            #prepend
                                            >
                                            <VAvatar
                                                start
                                                style="margin-top: -8px;"
                                                size="36"
                                                :image="getFlagCountry(supplier_country_id)"
                                            />
                                            </template>
                                        </VAutocomplete>
                                    </VCol>

                                    <!-- 👉 Provincias -->
                                    <VCol cols="6">
                                        <v-autocomplete
                                            v-model="province_id"
                                            label="Provincias"
                                            :rules="[requiredValidator]"
                                            :items="getProvinces"
                                            :menu-props="{ maxHeight: '200px' }"
                                        />
                                    </VCol>

                                    <!-- 👉 Address -->
                                    <VCol cols="12">
                                        <VTextarea
                                            v-model="address"
                                            rows="4"
                                            label="Dirección"
                                            :rules="[requiredValidator]"
                                        />
                                    </VCol>
                                </VRow>
                            </VWindowItem>
                        </VWindow>
                    </VCardText>
                </VCardText>
            </VCard>
            <VRow class="mt-5">
                <!-- 👉 Submit and Cancel -->
                <VCol cols="12">
                    <div class="text-center align-center justify-content-center">
                        <VBtn
                            type="submit"
                            class="me-3">
                            Agregar
                        </VBtn>
                        <VBtn
                            type="reset"
                            variant="tonal"
                            color="secondary"
                            :to="{ name: 'dashboard-admin-suppliers' }"
                            >
                            Regresar
                        </VBtn>
                    </div>
                </VCol>
            </VRow>
        </VForm>
    </section>
</template>

<route lang="yaml">
    meta:
      action: crear
      subject: proveedores
  </route>