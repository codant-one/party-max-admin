<script setup>

import { useAppAbility } from '@/plugins/casl/useAppAbility'
import { useAlertStore } from '@/stores/useAlerts.js'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { VForm } from 'vuetify/components'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { emailValidator, requiredValidator } from '@validators'
import { useAuthStores } from '@/stores/useAuth'

import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-desktop-wallpaper.jpg'
import festin from '@images/pages/auth-v2-login-illustration-light.png'

const alertStore = useAlertStore()
const authStores = useAuthStores()

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const isPasswordVisible = ref(false)
const route = useRoute()
const router = useRouter()
const ability = useAppAbility()

const errors = ref({
  email: undefined,
  password: undefined,
})

const refVForm = ref()
const email = ref('')
const password = ref('')
const remember_me = ref(true)
const load = ref(false)

watchEffect(fetchData)

async function fetchData() {

  remember_me.value = (localStorage.getItem('remember_me') === 'true') ? true : false
  email.value = localStorage.getItem('email') ?? ''
  password.value = localStorage.getItem('password') ?? ''
  
}

const inputChange = () => {
  errors.value = {
    email: undefined, 
    password: undefined
  }
}

const login = () => {
  load.value = true

  let data = {
    email: email.value,
    password: password.value,
  }

  authStores.login(data)
    .then(response => {
      load.value = false

      const { qr, token, accessToken, user_data, userAbilities } = response.data     
      const two_factor = { generate_qr: (response.message === '2fa-generate') ? true : false }       

      ability.update(userAbilities)

      localStorage.setItem('userAbilities', JSON.stringify(userAbilities))      
      localStorage.setItem('user_data', JSON.stringify(user_data))
      localStorage.setItem('accessToken', accessToken)     
      localStorage.setItem('qr', qr)
      localStorage.setItem('token', token)
      localStorage.setItem('two_factor', JSON.stringify(two_factor))      
      localStorage.setItem('remember_me', remember_me.value);

      if(remember_me.value){
        localStorage.setItem('email', email.value);
        localStorage.setItem('password', password.value);
      } else {
        localStorage.setItem('email','');
        localStorage.setItem('password',''); 
      }

      // Redirect to `to` query if exist or redirect to index route
      router.replace(route.query.to ? String(route.query.to) : (response.message === 'login_success' ? '/info': '/'))
    }).catch(err => {

      load.value = false

      errors.value = {
        email: err.errors, 
        password: ''
      }

      console.error(err.message)
    })
}

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid)
      login()
  })
}
</script>

<template>
  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      cols="12"
      md="8"
      class="d-none d-lg-flex"
    >
      <div class="position-relative w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            :src="authThemeImg"
            class="auth-illustration rounded-lg"            
          />
        </div>
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="d-flex align-center justify-center px-5"
    >

      <div class="d-block">
        <div  class="d-flex align-center justify-center festin d-lg-none">
          <VImg
            :src="festin"
            class="auth-illustration"            
          />
      </div>

        <VCard
          flat
          :max-width="400"
          class="mt-2 mt-lg-12 pa-4"
        >
          <VAlert
            v-if="alertStore.message"
            :type="alertStore.type"
          >
            {{ alertStore.message }}
          </VAlert>
          <VCardText>
            <VNodeRenderer
              :nodes="themeConfig.app.logoSlogan"
              class="mb-6 d-flex align-center"
              size="100"
            />
            
            <h5 class="text-h5 font-weight-semibold mb-1 d-none d-md-block">
              Bienvenido a tu Panel! 👋🏻
            </h5>
            <p class="mb-0">
              Inicia sesión en tu cuenta
            </p>
          </VCardText>
          <VCardText>
            <VForm
              ref="refVForm"
              @submit.prevent="onSubmit"
            >
              <VRow>
                <!-- email -->
                <VCol cols="12" class="pb-0">
                  <VTextField
                    class="login"
                    v-model="email"
                    label="Correo electrónico"
                    type="email"
                    :rules="[requiredValidator, emailValidator]"
                    :error-messages="errors.email"
                    @input="inputChange()"
                  />
                </VCol>

                <!-- password -->
                <VCol cols="12">
                  <VTextField
                    class="login"
                    v-model="password"
                    label="Contraseña"
                    :error-messages="errors.password"
                    :rules="[requiredValidator]"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @input="inputChange()"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  />

                  <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                    <VCheckbox
                      v-model="remember_me"
                      label="Recuérdame"
                      class="letter"
                    />
                
                      <RouterLink
                        class="text-primary mb-1 letter"
                        :to="{ name: 'forgot-password' }"
                      >
                      ¿Olvido su contraseña?
                      </RouterLink>
                  </div>

                  <VBtn
                    block
                    type="submit"
                  > 
                    Iniciar Sesión
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
    </VCol>
  </VRow>
</template>

<style lang="scss">
  @use "@core/scss/template/pages/page-auth.scss";

  @media(max-width: 991px){
    .v-card-text {
      padding: 10px !important;
    }

    .letter, .v-selection-control--inline .v-label {
      font-size: 11.5px !important;
    }

    .v-selection-control__wrapper {
      width: 28px !important;
      margin-left: 4px;
    }
  }

  .festin {
    padding-right: 30% !important;
    padding-left: 30% !important;
  }
</style>

<route lang="yaml">
meta:
  layout: blank
  action: ver
  subject: Auth
  redirectIfLoggedIn: true
</route>
