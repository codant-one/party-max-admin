<script setup>

import { useCountriesStores } from '@/stores/useCountries'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    required: true
  },
  rolesList: {
    type: Object,
    required: true
  }

})

const emit = defineEmits([
  'update:isDrawerOpen',
  'close'
])

const countriesStores = useCountriesStores()

const email = ref('')
const name = ref('')
const password = ref('')
const last_name = ref('')
const phone = ref('')
const address = ref('')
const username = ref('')
const document = ref('')
const provinceOld_id = ref('')
const province_id = ref('')
const province = ref('')
const country_id = ref('')
const country = ref('')
const assignedRoles = ref([])
const listCountries = ref([])

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

onMounted(async () => {

    await countriesStores.fetchCountries()

    loadCountries()
})

watchEffect(() => {
    if (props.isDrawerOpen) {

        if (!(Object.entries(props.user).length === 0) && props.user.constructor === Object) {
            email.value = props.user.email
            password.value = props.user.password
            name.value = props.user.name
            last_name.value = props.user.last_name
            phone.value = props.user.user_detail?.phone
            address.value = props.user.user_detail?.address
            username.value = props.user.username
            document.value = props.user.user_detail?.document
            province_id.value = props.user.user_detail?.province.name
            provinceOld_id.value = props.user.user_detail?.province_id
            country_id.value = props.user.user_detail?.province.country.name
            province.value = props.user.user_detail?.province.name
            country.value = props.user.user_detail?.province.country.name
            assignedRoles.value = props.user.assignedRoles
        }
    }
})

const closeUserDetailDialog = function() {
    emit('update:isDrawerOpen', false)
    emit('close')
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
    <!-- DIALOGO DE VER -->
    <VDialog
        :model-value="props.isDrawerOpen"
        max-width="600"
        persistent
        >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeUserDetailDialog" />

        <!-- Dialog Content -->
        <VCard title="Detalle usuario">
            <VCardText>
                <VRow>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="name"
                            label="Nombres"
                            readonly
                        />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="last_name"
                            label="Apellidos"
                            readonly
                        />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="email"
                            label="E-mail"
                            readonly
                        />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="username"
                            label="Username"
                            readonly
                        />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="phone"
                            label="Teléfono"
                            readonly
                        />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="address"
                            label="Dirección"
                            readonly
                        />
                    </VCol>
                    <VCol md="1" cols="1">
                        <VAvatar
                            class="mt-2"
                            start
                            size="25"
                            :image="getFlagCountry(country)"
                         />
                    </VCol>
                    <VCol md="5" cols="11">
                        <VTextField
                            v-model="country"
                            label="País"
                            readonly
                        />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="province"
                            label="Estado"
                            readonly
                        />
                    </VCol>
                    <VCol md="6" cols="12">
                        <VTextField
                            v-model="document"
                            label="Documento"
                            readonly
                        />
                    </VCol>                    
                    <VCol md="12" cols="12">
                        <VCombobox
                            v-model="assignedRoles"
                            chips
                            multiple
                            :items="rolesList"
                            label="Roles asignados al usuario"
                            readonly
                        />
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
