<script setup>

import { avatarText } from '@/@core/utils/formatters'
import { emailValidator, requiredValidator, phoneValidator } from '@/@core/utils/validators'
import { initialAbility } from '@/plugins/casl/ability'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import { useAuthStores } from '@/stores/useAuth'
import { useProfileStores } from '@/stores/useProfile'
import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'

const router = useRouter()
const ability = useAppAbility()
const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()
const authStores = useAuthStores()
const profileStores = useProfileStores()

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])

const refVForm = ref()
const user_id = ref('')
const email = ref('')
const username = ref('')
const name = ref('')
const last_name = ref('')
const phone = ref('')
const address = ref('')
const province_id = ref('')
const country_id = ref('')
const avatar = ref('')

const avatarOld = ref('')

const alert = ref({
  message: '',
  show: false,  
  type: '',
})

const getCountries = computed(() => {
  return listCountries.value.map((country) => {
    return {
      title: country.name,
      value: country.id,
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

onMounted(async () => {

  await countriesStores.fetchCountries();
  await provincesStores.fetchProvinces();
  
  loadCountries()
  loadProvinces()
})

watchEffect(fetchData)

async function fetchData() {

    let data = JSON.parse(localStorage.getItem('user_data') || 'null')
    
    user_id.value = data.id
    email.value = data.email
    username.value = data.username
    name.value = data.name
    last_name.value = data.last_name
    phone.value = data.user_details?.phone
    address.value = data.user_details?.address
    province_id.value = data.user_details?.province_id
    country_id.value = data.user_details?.country_id
    
    avatarOld.value = data.user_details?.avatar
    
}

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
}

const selectCountry = country => {
  if (country) {
    let _country = listCountries.value.find(item => item.id === country)
    country_id.value = _country.id
 
    province_id.value = ''
    listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
  }
}


const logout = () => {

  authStores.logout()
    .then(response => {
    // Remove "user_data" from localStorage
    localStorage.removeItem('user_data')

    // Remove "accessToken" from localStorage
    localStorage.removeItem('accessToken')
    
    // Remove "userAbilities" from localStorage
    localStorage.removeItem('userAbilities')

    // Reset ability to initial ability
    ability.update(initialAbility)
    router.push('/login')

  })
}

const resetAvatar = () => {
  avatar.value = null
}

const onSubmit = () =>{
  refVForm.value?.validate().then(({ valid: isValid }) => {

    if (isValid) {

      let formData = new FormData()
      
      formData.append('user_id', user_id.value)
      formData.append('email', email.value)
      formData.append('username', username.value)
      formData.append('name', name.value)
      formData.append('last_name', last_name.value)
      formData.append('phone', phone.value)
      formData.append('address', address.value)
      formData.append('province_id', province_id.value)
      formData.append('image', avatarOld.value)

      profileStores.updateData(formData)
        .then(response => {    

          window.scrollTo(0, 0)
          
          alert.value.type = 'success'
          alert.value.message = 'Información personal actualizada. se recargara la página automaticamente para observar los efectos..!'
          alert.value.show = true
          
          localStorage.setItem('user_data', JSON.stringify(response.user_data))
          
          setTimeout(() => {
            alert.value.show=false,
            alert.value.message = ''
            location.reload()
          }, 5000)

        }).catch(error => {
          alert.value.type = 'error'
          alert.value.show = true
          alert.value.message = 'Ocurrió un error, intente nuevamente o contacte con el administrador...!'
          
          setTimeout(() => {
            alert.value.show = false,
            alert.value.message = ''
          }, 5000) 
        })
    }
  })
}

const onImageSelected = event => {
  const file = event.target.files[0]

  if (!file) return
  // avatarOld.value = file

  URL.createObjectURL(file)

  resizeImage(file, 400, 400, 0.9)
    .then(async blob => {
      avatarOld.value = blob
      let r = await blobToBase64(blob)
      avatar.value = 'data:image/jpeg;base64,' + r
    })
}

const resizeImage = function(file, maxWidth, maxHeight, quality) {
  return new Promise((resolve, reject) => {
    const img = new Image()

    img.src = URL.createObjectURL(file)
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')

      let width = img.width
      let height = img.height

      if (maxWidth && width > maxWidth) {
        height *= maxWidth / width
        width = maxWidth
      }

      if (maxHeight && height > maxHeight) {
        width *= maxHeight / height
        height = maxHeight
      }

      canvas.width = width
      canvas.height = height

      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(blob => {
        resolve(blob)
      }, file.type, quality)
    }
    img.onerror = error => {
      reject(error)
    }
  })
}

const blobToBase64 = blob => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.readAsDataURL(blob)
    reader.onload = () => {
      resolve(reader.result.split(',')[1])
    }
    reader.onerror = error => {
      reject(error)
    }
  })
}

</script>

<template>
  <VRow class="justify-center m-0">
    <VCol
      cols="12"
      md="8"
    >
      <h1 class="text-center">
        Completar Perfil del Usuario
      </h1>
      <VCard
        title="¡Atención!"
        class="mb-5"
      >
        <VCardText>
          <p>Para poder utilizar nuestro panel con 
            normalidad, necesitamos que la primera vez que ingreses a él, nos rellenes el formulario con tus datos. Para que luego puedas utilizar todas las funciones que tenemos preparadas para ti.</p>
          <p>¡Bienvenid@ a PARTYMAX! Cualquier duda, puedes ponerte en contacto con nosotros 📩.</p>
        </VCardText>
      </VCard>
      <VDivider />
      <VRow>
        <VCol 
          v-if="alert.show" 
          cols="12" 
        >
          <VAlert
            v-if="alert.show"
            :type="alert.type"
          >
            {{ alert.message }}
          </VAlert>
        </VCol>
        <VForm
          ref="refVForm"
          @submit.prevent="onSubmit"
        >
          <VCardText class="d-flex">
            <VAvatar
              rounded
              size="100"
              class="me-6"
              color="primary"
              variant="tonal"
            >
              <VImg
                v-if="avatar"
                style="border-radius: 6px;"
                :src="avatar"
              />
              <span
                v-else
                class="text-5xl font-weight-semibold"
              >
                {{ avatarText(name) }}
              </span>
            </VAvatar>

            <!-- 👉 Upload Photo -->
            <div class="d-flex flex-column justify-center gap-4">
              <div class="d-flex flex-wrap gap-2">
                <VFileInput                          
                  label="Avatar"
                  accept="image/png, image/jpeg, image/bmp"
                  placeholder="Avatar"
                  prepend-icon="tabler-camera"
                  :rules="[requiredValidator]"
                  @change="onImageSelected"
                  @click:clear="resetAvatar"
                />
              </div>
              <p class="text-body-1 mb-0">
                Formatos Permitidos JPG, GIF, PNG.
              </p>
            </div>
          </VCardText>

          <VDivider />

          <VCardText class="pt-2 mt-6">
            <VRow>
              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="name"
                  label="Nombre"
                  :rules="[requiredValidator]"
                  readonly
                />
              </VCol>
              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="last_name"
                  label="Apellidos"
                  :rules="[requiredValidator]"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="email"
                  label="E-mail"
                  type="email"
                  :rules="[requiredValidator, emailValidator]"
                  readonly
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="phone"
                  label="Teléfono"
                  placeholder="+(XX) XXXXXXXXX"
                  :rules="[phoneValidator, requiredValidator]"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="address"
                  label="Dirección"
                  :rules="[requiredValidator]"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <v-autocomplete
                  v-model="country_id"
                  label="País"
                  :rules="[requiredValidator]"
                  :items="getCountries"
                  :menu-props="{ maxHeight: '200px' }"
                  @update:model-value="selectCountry"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
              <v-autocomplete
                    v-model="province_id"
                    label="Estado"
                    :rules="[requiredValidator]"
                    :items="getProvinces"
                    :menu-props="{ maxHeight: '200px' }"
                  />
                
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol cols="12" md="6"></VCol>
              <VCol
                cols="12" md="6"
                class="d-flex flex-wrap gap-4 justify-buttons"
              >
                <VBtn type="submit">
                  Guardar Cambios
                </VBtn>
                <VBtn @click="logout">
                  Cerrar Sesión
                </VBtn>
              </VCol>
            </VRow>
          </VCardText>
        </VForm>
      </VRow>
    </VCol>
  </VRow>
</template>

<style lang="scss">
  .m-0 {
    margin: 0;
  }

  .justify-buttons {
    justify-content: right !important;

    @media (max-width: 767px) {
      justify-content: center !important;
    }
  }
</style>

<route lang="yaml">
  meta:
    layout: blank
    action: ver
    subject: Auth
    parar: true
  </route>
