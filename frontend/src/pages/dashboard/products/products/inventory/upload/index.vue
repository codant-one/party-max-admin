<script setup>

import { themeConfig } from '@themeConfig'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { useProductsStores } from '@/stores/useProducts'
import router from '@/router'
import xls from '@images/icons/file/xls.png'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const productsStores = useProductsStores()

const emitter = inject("emitter")

const isValid =  ref(null)
const isFormValid = ref(false)
const refForm = ref()

const isRequestOngoing = ref(false)
const filename = ref([])
const file = ref(null)
const name = ref(null)

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const download = async(data) => {
  try {
    const link = document.createElement('a');
    link.href = themeConfig.settings.urlPublic + 'files/example.xlsx'

    link.target = '_blank'
    document.body.appendChild(link);
    link.click();

    link.parentNode.removeChild(link);

    advisor.value.type = 'success'
    advisor.value.show = true
    advisor.value.message = 'Descarga Exitosa!'

    } catch (error) {
      advisor.value.type = 'error'
      advisor.value.show = true
      advisor.value.message = 'Error al descargar el documento:' + error
    }

    setTimeout(() => {
        advisor.value.show = false
        advisor.value.type = ''
        advisor.value.message = ''
    }, 5000)
}

const uploadFile = (event) => {
  file.value = event.target.files ? event.target.files[0] : null;
  if ( file.value)
    name.value =  file.value.name
}

const onSubmit = () => {

  refForm.value?.validate().then(({ valid }) => {
    isValid.value = valid
    if (valid) {
      isRequestOngoing.value = true

      let formData = new FormData()

      formData.append('file', file.value)

      productsStores.uploadProducts(formData)
        .then((res) => {
          if (res.data.success) {

              let data = {
                message: 'Productos Cargados!',
                error: false
              }

              router.push({ name : 'dashboard-products-products-referrals'})
              emitter.emit('toast', data)

          } else {

              let data = {
                message: 'ERROR',
                error: true
              }

              router.push({ name : 'dashboard-products-products-referrals'})
              emitter.emit('toast', data)
          }
        })
        .catch((err) => {
          let data = {
            message: err,
            error: true
          }

          router.push({ name : 'dashboard-products-products-referrals'})
          emitter.emit('toast', data)
        })

      }
  })
}
</script>

<template>
  <section>
    <VRow>
      <VCol cols="12">
        <VAlert
          v-if="advisor.show"
          :type="advisor.type"
          class="mb-6">
            {{ advisor.message }}
        </VAlert>
      </VCol>
    </VRow>
    <VForm
      ref="refForm"
      v-model="isFormValid"
      @submit.prevent="onSubmit">
      <div class="d-flex mt-3 flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
        <div class="d-flex flex-column justify-center">
          <h4 class="text-h4 font-weight-medium">
            Añadir items a tus productos 😃🌟
          </h4>
        </div>

        <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn
            color="default"
            variant="tonal"
            class="mb-2"
            :to="{ name: 'dashboard-products-products-inventory' }">
            Regresar
        </VBtn>
        <VBtn
          prepend-icon="tabler-plus"
          class="mb-2"
          type="submit">
            Cargar
        </VBtn>
        </div>
      </div>

      <VRow>
        <VCol md="9">
          <VTimeline
            align="start"
            side="end"
            truncate-line="start"
            :density="$vuetify.display.smAndDown ? 'compact' : 'default'"
          >
            <VTimelineItem fill-dot size="small">
              <template #icon>
                <div class="v-timeline-avatar-wrapper rounded-circle">
                  <VAvatar
                    size="32"
                    color="error"
                    variant="tonal"
                  >
                    <VIcon
                      icon="mdi-file-document-alert"
                      size="20"
                    />
                  </VAvatar>
                </div>
              </template>
              <VCard class="mt-n4">
                <VCardItem class="pb-2">
                  <VCardTitle>Descargar documento</VCardTitle>
                </VCardItem>
                <VCardText>
                  <p class="app-timeline-text mb-3">
                    Para asegurar una carga exitosa de tus productos y evitar errores durante el procesamiento de datos, 
                    te recomendamos descargar y utilizar el documento de ejemplo como referencia.
                  </p>
                  <div class="d-inline-flex align-items-center timeline-chip">
                    <VTooltip
                      open-on-focus
                      location="top"
                      activator="parent"
                      text="Descargar">
                      <template v-slot:activator="{ props }">
                        <VChip 
                          :label="false"
                          :prepend-avatar="xls"
                          pill
                          size="large" 
                          rounded="sm"
                          class="cursor-pointer"
                          @click="download()">                     
                          <span class="app-timeline-text font-weight-medium">
                            example.xlsx
                          </span>
                        </VChip>
                      </template>
                    </VTooltip>
                  </div>                
                </VCardText>
              </VCard>
            </VTimelineItem>
            <VTimelineItem fill-dot size="small">
              <template #icon>
                <div class="v-timeline-avatar-wrapper rounded-circle">
                  <VAvatar
                    size="32"
                    color="success"
                    variant="tonal"
                  >
                    <VIcon
                      size="20"
                      icon="mdi-file-upload-outline"
                    />
                  </VAvatar>
                </div>
              </template>
              <VCard class="mt-n4">
                <VCardItem class="pb-2">
                  <VCardTitle>Cargar documento</VCardTitle>
                </VCardItem>
                <VCardText>
                  <p class="mb-3">
                    Sube tu documento con la información precisa de tus productos (SKU) y la cantidad que deseas actualizar.
                  </p>
                  <div class="d-flex gap-4 flex-wrap">
                    <VFileInput
                      v-model="filename"
                      label="example.xls"
                      accept=".xls, .xlsx, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                      prepend-icon="mdi-microsoft-excel"
                      clearable
                      @change="uploadFile"
                      :rules="[requiredValidator]"
                    />
                  </div>
                </VCardText>
              </VCard>
            </VTimelineItem>
            <VTimelineItem fill-dot size="small" class="last">
              <template #icon>
                <div class="v-timeline-avatar-wrapper rounded-circle">
                  <VAvatar
                    size="32"
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon
                      size="18"
                      icon="mdi-file-document-check"
                    />
                  </VAvatar>
                </div>
              </template>
              <VCard class="mt-n4">
                <VCardItem class="pb-2">
                  <VCardTitle>¡Empieza la diversión! </VCardTitle>
                </VCardItem>
                <VCardText>
                  <p class="mb-3">
                    Haz clic en el botón 'Cargar' a tu derecha para subir los productos al inventario y espera a que el proceso se complete. 
                    Recuerda que, una vez cargados, deberás esperar su aprobación antes de que estén disponibles en tu tienda.
                  </p>
                  <div class="d-inline-flex align-items-center timeline-chip" v-if="isRequestOngoing">
                    <img
                      :src="xls"
                      height="20"
                      class="me-2"
                      alt="img"
                    >
                    <span class="app-timeline-text font-weight-medium">
                      {{name}}
                    </span>
                  </div>
                  <div class="d-flex gap-2 align-center" v-if="isRequestOngoing">
                    <div class="flex-grow-1">
                      <VProgressLinear
                        indeterminate
                        color="primary"
                      />
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VTimelineItem>
          </VTimeline>
        </VCol>

        <VCol md="3" cols="12"></VCol>
      </VRow>
    </VForm>
  </section>
</template>

<style lang="scss">

  .last {
    .v-timeline-item__body {
      padding-bottom: 0 !important;
    }
  }

  .v-chip--pill.v-chip.v-chip--size-large {
    .v-avatar--start {
      border-radius: 0 !important;
    }
  }

  .v-timeline {
    .v-timeline-divider__inner-dot {
      background: white !important;
    }
  }

  .inventory-card{
    .v-radio-group,
    .v-checkbox {
      .v-selection-control {
        align-items: start !important;

        .v-selection-control__wrapper{
          margin-block-start: -0.375rem !important;
        }
      }

      .v-label.custom-input {
        border: none !important;
      }
    }

    .v-tabs.v-tabs-pill {
      .v-slide-group-item--active.v-tab--selected.text-primary {
        h6{
          color: #fff !important
        }
      }
    }

  }

  .ProseMirror{
    p{
      margin-block-end: 0;
    }

    padding: 0.5rem;
    outline: none;

    p.is-editor-empty:first-child::before {
      block-size: 0;
      color: #adb5bd;
      content: attr(data-placeholder);
      float: inline-start;
      pointer-events: none;
    }
  }

  .justify-content-end{
    justify-content: end !important;
  }
    .editor {
        min-block-size: 450px;
        padding-block-end: 100px;
    }

    .ql-container {
        block-size: 300px !important;
        overflow-y: auto !important;
    }

    .p-0 {
        padding: 0;
    }

    .ql-editor .ql-video {
        block-size: inherit !important;
        inline-size: 100% !important;
        padding-block: 0 !important;
        padding-inline: 15% !important;
    }

    .ql-snow .ql-tooltip {
        inset-inline-start: 25% !important;
    }

    .ql-snow .ql-tooltip input[type="text"] {
        inline-size: 300px !important;
    }

    .border-img {
        border: 1.8px solid rgba(var(--v-border-color), var(--v-border-opacity));
        border-radius: 6px;
    }

    .border-error {
        border: 1.8px solid rgb(var(--v-theme-error));
    }

    .border-img .v-img__img--contain {
        padding: 10px;
    }
</style>
<route lang="yaml">
  meta:
    action: crear
    subject: productos
</route>
