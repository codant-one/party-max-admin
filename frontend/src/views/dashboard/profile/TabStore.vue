<script setup>

import { requiredValidator } from '@/@core/utils/validators'
import { useProfileStores } from '@/stores/useProfile'
import { useAuthStores } from '@/stores/useAuth'
import { themeConfig } from '@themeConfig'
import banner from '@images/logos/banner3.png'
import supplier from '@images/logos/supplier.png';

const authStores = useAuthStores()
const profileStores = useProfileStores()

const refVForm = ref()
const isRequestOngoing = ref(true)

const data = ref(null)
const userData = ref(null)
const rol = ref(null)
const store_name = ref(null)
const logo = ref(null)
const logoOld = ref(null)
const banner_ = ref(null)
const banner_Old = ref(null)
const about_us = ref(' ')
const filename1 = ref([])
const filename2 = ref([])
const address = ref(null)

const alert = ref({
    message: '',
    show: false,  
    type: '',
})

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
  data.value = await authStores.store()

  rol.value = userData.value.roles[0].name
  store_name.value = userData.value.user_details.store_name

  if(rol.value === 'Proveedor'){
    banner_.value = (data.value.supplier.banner !== null) ? themeConfig.settings.urlStorage + data.value.supplier.banner : null
    logo.value = (data.value.supplier.logo !== null) ? themeConfig.settings.urlStorage + data.value.supplier.logo : null
    address.value = data.value.supplier.address ?? null
    about_us.value = data.value.supplier.about_us ?? ' '
  }

  isRequestOngoing.value = false
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

const onImageSelected2 = event => {
  const file = event.target.files[0]

  if (!file) return
  // logoOld.value = file

  URL.createObjectURL(file)

  resizeImage(file, 400, 400, 0.9)
    .then(async blob => {
        logoOld.value = blob
      let r = await blobToBase64(blob)
        logo.value = 'data:image/jpeg;base64,' + r
    })
}

const onImageSelected = event => {
  const file = event.target.files[0]

  if (!file) return
  // banner_Old.value = file

  URL.createObjectURL(file)

  resizeImage(file, 400, 400, 0.9)
    .then(async blob => {
        banner_Old.value = blob
      let r = await blobToBase64(blob)
        banner_.value = 'data:image/jpeg;base64,' + r
    })
}

const onSubmit = () => {
    
    refVForm.value?.validate().then(({ valid: isValid }) => {
        if (isValid) {

            let formData = new FormData()

            formData.append('store_name', store_name.value)
            formData.append('address', address.value)
            formData.append('logo', logoOld.value)
            formData.append('banner', banner_Old.value)
            formData.append('about_us', about_us.value)
            

            profileStores.updateStore(formData)
                .then(response => {    

                    window.scrollTo(0, 0)
                    
                    alert.value.type = 'success'
                    alert.value.message = 'Información personal actualizada. se recargara la página automaticamente para observar los efectos..!'
                    alert.value.show = true
                    
                    localStorage.setItem('user_data', JSON.stringify(response.user_data))
                    
                    fetchData()

                    setTimeout(() => {
                        alert.value.show = false,
                        alert.value.message = ''
                        location.reload()
                    }, 5000)

                }).catch(error => {
                    alert.value.type = 'error'
                    alert.value.show = true
                    alert.value.message = 'Se ha producido un error...! (Server Error)'
                    
                    setTimeout(() => {
                        alert.value.show = false,
                        alert.value.message = ''
                    }, 5000) 
                })
            }
    })
}

</script>

<template>
  <section>
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
            class="mb-0"
          />
        </VCardText>
      </VCard>
    </VDialog>

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

        <VCol cols="12">
            <VCard>
                <VCardText class="p-0">
                    <VImg :src="banner_ ?? banner" class="banner-img" cover/>
                </VCardText>

                <VCardText class="tw-bg-tertiary p-0">
                    <VRow no-gutters>
                        <VCol cols="12" md="4" class="d-flex col-logo">
                            <img :src="logo ?? supplier" class="logo-store"/>
                        </VCol>
                        <VCol cols="12" md="8" class="d-flex">
                            <VCardItem class="px-0">
                                <span class="store-name pb-3">{{ store_name }}</span>
                            </VCardItem>
                            <VCardItem class="px-0" v-if="address !== null">
                                <span class="store-address pb-3 ms-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="18" viewBox="0 0 15 18" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25736 3.25736C4.38258 2.13214 5.9087 1.5 7.5 1.5C9.0913 1.5 10.6174 2.13214 11.7426 3.25736C12.8679 4.38258 13.5 5.9087 13.5 7.5C13.5 9.82354 11.9882 12.0782 10.3305 13.8279C9.51704 14.6866 8.701 15.3896 8.08749 15.8781C7.85916 16.0599 7.65973 16.2114 7.5 16.3294C7.34027 16.2114 7.14084 16.0599 6.91251 15.8781C6.299 15.3896 5.48296 14.6866 4.66946 13.8279C3.0118 12.0782 1.5 9.82354 1.5 7.5C1.5 5.9087 2.13214 4.38258 3.25736 3.25736ZM7.08357 17.8738C7.08379 17.8739 7.08397 17.874 7.5 17.25L7.91603 17.874C7.6641 18.042 7.33549 18.0417 7.08357 17.8738ZM7.08357 17.8738L7.5 17.25C7.91603 17.874 7.91678 17.8735 7.91699 17.8734L7.91857 17.8723L7.92357 17.869L7.94076 17.8574C7.95536 17.8474 7.97619 17.8332 8.00283 17.8147C8.0561 17.7778 8.13265 17.7241 8.22916 17.6544C8.42209 17.5151 8.69523 17.3117 9.02188 17.0516C9.674 16.5323 10.5455 15.7821 11.4195 14.8596C13.1368 13.0468 15 10.4265 15 7.5C15 5.51088 14.2098 3.60322 12.8033 2.1967C11.3968 0.790176 9.48912 0 7.5 0C5.51088 0 3.60322 0.790176 2.1967 2.1967C0.790176 3.60322 0 5.51088 0 7.5C0 10.4265 1.8632 13.0468 3.58054 14.8596C4.45454 15.7821 5.326 16.5323 5.97812 17.0516C6.30477 17.3117 6.57791 17.5151 6.77084 17.6544C6.86735 17.7241 6.9439 17.7778 6.99717 17.8147C7.02381 17.8332 7.04464 17.8474 7.05924 17.8574L7.07643 17.869L7.08143 17.8723L7.08357 17.8738ZM6 7.5C6 6.67157 6.67157 6 7.5 6C8.32843 6 9 6.67157 9 7.5C9 8.32843 8.32843 9 7.5 9C6.67157 9 6 8.32843 6 7.5ZM7.5 4.5C5.84315 4.5 4.5 5.84315 4.5 7.5C4.5 9.15685 5.84315 10.5 7.5 10.5C9.15685 10.5 10.5 9.15685 10.5 7.5C10.5 5.84315 9.15685 4.5 7.5 4.5Z" fill="white"/>
                                    </svg>
                                    <span>&nbsp;&nbsp;&nbsp;{{ address }}&nbsp;&nbsp;&nbsp;</span>
                                </span>
                            </VCardItem>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VCol>
        <VCol cols="12">
            <VCard title="Editar Información de la Empresa">
                <VCardText>
                    <VForm
                        ref="refVForm"
                        @submit.prevent="onSubmit">
                        <VRow>
                            <VCol cols="12" md="6">
                                <VTextField
                                    v-model="store_name"
                                    label="Nombre de la empresa"
                                    :rules="[requiredValidator]"
                                />
                            </VCol>
                            <VCol cols="12" md="6" v-if="rol === 'Proveedor'">
                                <VTextField
                                    v-model="address"
                                    label="Dirección"
                                    :rules="[requiredValidator]"
                                />
                            </VCol>
                            <VCol cols="12" md="6" v-if="rol === 'Proveedor'">
                                <VFileInput 
                                    v-model="filename1"
                                    label="Banner"
                                    class="mb-2"
                                    accept="image/png, image/jpeg, image/bmp"
                                    prepend-icon="tabler-camera"
                                    @change="onImageSelected"
                                />
                                
                            </VCol>
                            <VCol cols="12" md="6" v-if="rol === 'Proveedor'">
                                <VFileInput 
                                    v-model="filename2"
                                    label="Logo"
                                    class="mb-2"
                                    accept="image/png, image/jpeg, image/bmp"
                                    prepend-icon="tabler-camera"
                                    @change="onImageSelected2"
                                />
                            </VCol>
                            <VCol cols="12" v-if="rol === 'Proveedor'">
                                <span class="mb-1">Acerca de nosotros</span>
                                <TiptapEditor
                                    placeholder=" "
                                    v-model="about_us"
                                    class="border rounded"
                                    :rules="[requiredValidator]"
                                />
                            </VCol>
                            <VCol cols="12">
                                <VBtn type="submit">
                                    Enviar
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
  </section>
</template>
<style scoped>
    .banner-img {
        width: 100%;
        height: 170px;
    }

    .logo-store {
        width: 173.96px;
        height: 173.96px;
        max-width: 173.96px;
        border-radius: 16px;
        border: 1px solid #E1E1E1;
        margin-top: -50%;
        margin-left: 10%;
        z-index: 9999;
    }

    .tw-bg-tertiary {
        opacity: 1 !important;
        background-color: #0A1B33 !important
    }

    .store-name {
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: 16px;
        color: white;
    }

    .store-address {
        font-size: 16px;
        font-style: normal;
        font-weight: 600;
        line-height: 16px;
        color: white;
    }
</style>