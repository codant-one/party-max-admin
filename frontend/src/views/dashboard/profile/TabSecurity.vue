<script setup>

import { confirmedValidator, passwordValidator, requiredValidator } from '@/@core/utils/validators'
import { useProfileStores } from '@/stores/useProfile'
import { useAuthStores } from '@/stores/useAuth'
import AddAuthenticatorAppDialog from "@/components/dialogs/AddAuthenticatorAppDialog.vue";
import QRCode from 'qrcode-generator';

const profileStores = useProfileStores()
const authStores = useAuthStores()

const refVForm = ref()
const password = ref()
const passwordConfirmation = ref()
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const isRequestOngoing = ref(true)

const isDialogVisible = ref(false)
const is_2fa = ref(false)
const data = ref(null)
const qr = ref(null)
const token = ref(null)

const alert = ref({
    message: '',
    show: false,  
    type: '',
})

const enabled2fa = () => {
  isDialogVisible.value = true
}

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  data.value = await authStores.generateQR()

  const typeNumber = 0;
  const errorCorrectionLevel = 'L';
  const qr_ = QRCode(typeNumber, errorCorrectionLevel);
  qr_.addData(data.value.qr);
  qr_.make();

  qr.value = qr_.createDataURL(4)
  token.value = data.value.token
  is_2fa.value = data.value.is_2fa

  isRequestOngoing.value = false
}

const chance2fa = (code) => {

  let data = {
    panel: true,
    token_2fa: code,
    token: token.value
  }

  authStores.validate(data)
    .then(response => {
      alert.value.show = true
      alert.value.message = 'Información actualizada'
      alert.value.type = 'success' 
    }).catch(err => {

      window.scrollTo(0, 0)

      if(err.message === 'invalid_code'){
        alert.value.show = true
        alert.value.type = 'error'
        alert.value.message = err.errors
      }

      console.error(err.message)
    })

  setTimeout(() => {
    alert.value.show = false
    alert.value.type = ''
    alert.value.message = ''
  }, 5000)

  fetchData()
}

const onSubmit = () => {
    refVForm.value?.validate().then(({ valid: isValid }) => {
        if(isValid) {
            
          let data = {
              password: password.value
            }

            profileStores.updatePassword(data)
              .then(response => {

                window.scrollTo(0, 0)

                alert.value.show = true
                alert.value.message = 'Contraseña cambiada'
                alert.value.type = 'success'

                localStorage.setItem('user_data', JSON.stringify(response.user_data))
                
                password.value = undefined
                passwordConfirmation.value = undefined

                setTimeout(() => {
                    alert.value.show = false
                    alert.value.message = ''
                }, 5000)

            }).catch(error =>{

                window.scrollTo(0, 0)

                alert.value.show = true
                alert.value.message = 'Se ha producido un error...! (Server Error)'
                alert.value.type = 'error'
                
                password.value = undefined
                passwordConfirmation.value = undefined
                
                setTimeout(() => {
                    alert.value.show = false
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
        <VCard title="Cambiar Contraseña">
          <VCardText>
            <VAlert
              variant="tonal"
              color="warning"
              class="mb-4"
            >
              <VAlertTitle class="mb-1">
                Asegúrese de que se cumplan estos requisitos
              </VAlertTitle>
              <span>Mínimo 8 caracteres, mayúsculas, minúsculas y números</span>
            </VAlert>

            <VForm
              ref="refVForm"
              @submit.prevent="onSubmit"
            >
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="password"
                    label="Nueva contraseña"
                    :type="isNewPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    :rules="[requiredValidator, passwordValidator]"
                    @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="passwordConfirmation"
                    label="Confirmar Contraseña"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    :rules="[requiredValidator, confirmedValidator(passwordConfirmation, password)]"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  />
                </VCol>

                <VCol cols="12">
                  <VBtn type="submit">
                    Cambiar Contraseña
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
        <VCard title="Habilitar" class="mt-2">
          <VCardText>
            <VTable class="text-no-wrap rounded border">
              <thead>
                <tr>
                  <th scope="col">
                    TIPO
                  </th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col">HABILITAR</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td> Factor doble autenticación (2FA)  </td>
                  <td> </td>
                  <td> </td>
                  <td>
                    <VCheckbox 
                      v-model="is_2fa"
                      @update:model-value="enabled2fa" />
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <AddAuthenticatorAppDialog
      v-model:isDialogVisible="isDialogVisible"
      :qr="qr"
      :token="token"
      :is_2fa="is_2fa"
      @submit="chance2fa"
      @close="fetchData"
    />
  </section>
</template>
