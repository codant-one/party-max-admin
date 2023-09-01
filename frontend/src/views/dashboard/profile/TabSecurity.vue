<script setup>

import { confirmedValidator, passwordValidator, requiredValidator } from '@/@core/utils/validators'
import { useProfileStores } from '@/stores/useProfile'

const profileStores = useProfileStores()
const refVForm = ref()
const password = ref()
const passwordConfirmation = ref()
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

const alert = ref({
    message: '',
    show: false,  
    type: '',
})

const onSubmit = () =>{
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
                alert.value.message = 'Ocurrió un error, intente nuevamente o contacte con el administrador...!'
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
    </VCol>
  </VRow>
</template>
