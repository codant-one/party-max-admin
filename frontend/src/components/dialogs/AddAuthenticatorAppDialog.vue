<script setup>

const props = defineProps({
  authCode: {
    type: String,
    required: false,
  },
  qr: {
    type: String,
    required: false,
  }, 
  token: {
    type: String,
    required: false,
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  is_2fa: {
    type: Boolean,
    required: true,
  }
})

const emit = defineEmits([
  'update:isDialogVisible',
  'submit',
  'close'
])

const authCode = ref(structuredClone(toRaw(props.authCode)))

const formSubmit = () => {
  if (authCode.value) {
    emit('submit', authCode.value)
    emit('update:isDialogVisible', false)
  }
}

const resetAuthCode = () => {
  authCode.value = structuredClone(toRaw(props.authCode))
  emit('update:isDialogVisible', false)
  emit('close')
}

const handleOtp = (value) => {
    authCode.value = value
}

</script>

<template>
  <VDialog
    max-width="787"
    :model-value="props.isDialogVisible"
    @update:model-value="(val) => $emit('update:isDialogVisible', val)"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="resetAuthCode" />

    <VCard class="pa-5 pa-sm-8">
      <VCardItem>
        <VCardTitle class="text-h5 font-weight-medium text-center">
            Agregar aplicación de autenticación
        </VCardTitle>
      </VCardItem>

      <VCardText class="pt-3">
        <h6 class="text-lg font-weight-medium mb-2">
          Authenticator Apps
        </h6>

        <p class="mb-6">
            Utilizando una aplicación de autenticación como Google Authenticator, Microsoft Authenticator, Authy o 1Password, escanee el código QR. Generará un código de 6 dígitos para que lo ingrese a continuación.
        </p>

        <div class="mb-4">
          <VImg
            width="200"
            :src="props.qr"
            class="mx-auto"
          />
        </div>

        <VAlert
          color="light-warning"
          class="text-warning"
        >
          <span class="text-lg font-weight-medium">{{ props.token }}</span>
          <p class="mb-0">
            Si no puede escanear el código QR, puede ingresar manualmente la clave secreta a continuación.
          </p>
        </VAlert>
        <VForm @submit.prevent="() => {}">
            <AppOtpInput @updateOtp="handleOtp"/>
          <!-- <AppTextField
            v-model="authCode"
            name="auth-code"
            label="Ingrese el código de autenticación"
            placeholder="123 456"
            class="mb-4"
          /> -->

          <div class="d-flex justify-end flex-wrap gap-3">
            <VBtn
              color="secondary"
              variant="tonal"
              @click="resetAuthCode"
            >
              Cancelar
            </VBtn>

            <VBtn
              type="submit"
              @click="formSubmit"
            >
              {{ props.is_2fa ? 'Habilitar' : 'Deshabilitar' }}
              <VIcon
                end
                icon="tabler-arrow-right"
                class="flip-in-rtl"
              />
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
