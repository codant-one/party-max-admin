<script setup>

import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useAuthStores } from '@/stores/useAuth'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg'

const authStores = useAuthStores()
const route = useRoute()
const router = useRouter()

const load = ref(false)
const otp = ref('')

const token  = localStorage.getItem('token')

const handleOtp = (value) => {
    otp.value = value
}

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const onSubmit = () => {

    if (otp.value.length === 6) {
        load.value = true
        
        let data = {
            token_2fa: otp.value,
            token: localStorage.getItem('token')
        }

        authStores.validate(data)
            .then(response => {
                // Redirect to `to` query if exist or redirect to index route
                router.replace(route.query.to ? String(route.query.to) : '/info')
            }).catch(err => {

                load.value = false

                if(err.message === 'invalid_code'){
                  advisor.value.show = true
                  advisor.value.type = 'error'
                  advisor.value.message = err.errors
                }

                setTimeout(() => {
                  advisor.value.show = false
                  advisor.value.type = ''
                  advisor.value.message = ''
                }, 5000)

                console.error(err.message)
            })
    }
}

</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VImg
        :src="authV1TopShape"
        class="auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VImg
        :src="authV1BottomShape"
        class="auth-v1-bottom-shape d-none d-sm-block"
      />

      <v-alert
        v-if="advisor.show"
        :type="advisor.type"
        class="mb-6"
      >
        {{ advisor.message }}
      </v-alert>

      <!-- 👉 Auth card -->
      <VCard
        class="auth-card auth pa-4"
        max-width="448"
      >
        <VCardItem class="justify-center">
          <template #prepend>
            <div class="d-flex">
                <VNodeRenderer
                    :nodes="themeConfig.app.logoSlogan"
                    class="mb-5 d-flex align-center"
                    size="100"
                />
            </div>
          </template>
        </VCardItem>

        <VCardText>
            <span class="d-flex justify-center"> 
                <VImg
                    height="200"
                    :src="themeConfig.settings.urlPublic + 'images/smartphone.svg'"
                  />
            </span>
          <h5 class="text-h5 font-weight-semibold mb-1">
            Google Authenticator 💬
          </h5>
          <p class="mb-1">
            Alternativamente, puede usar el código <strong>{{ token }}</strong>.
          </p>
        </VCardText>

        <VCardText>
          <VForm
            @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppOtpInput @updateOtp="handleOtp"/>
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                >
                  Enviar
                  <VProgressCircular
                    v-if="load"
                    indeterminate
                    color="#fff"
                  />
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
    @use "@core/scss/template/pages/page-auth.scss";

    .auth .v-card-item__prepend {
        padding-inline-end: 0 !important;
    }

    .auth .v-card-item {
        padding: 0 24px !important;
    }

    @media(max-width: 991px){
        .auth .v-card--variant-elevated {
            box-shadow: none !important;
        }
    }

    @media(max-width: 991px){
        .v-card--variant-elevated {
            box-shadow: none !important;
        }
    }
</style>

<route lang="yaml">
    meta:
      layout: blank
      action: ver
      subject: Auth
      redirectIfLoggedIn: false
</route>
