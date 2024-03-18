<script setup>

import { formatNumber } from '@/@core/utils/formatters'

const props = defineProps({
  customerData: {
    type: Object,
    required: false,
  },
  isSupplier: {
    type: Boolean,
    required: true,
  }
})

const emit = defineEmits([
  'copy',
  'download'
])

const document_nit = ref(null)
const icon_type_nit = ref(null)
const document_rut = ref(null)
const icon_type_rut = ref(null)

watchEffect(fetchData)

async function fetchData() {
  if(props.isSupplier) {

    if(props.customerData.document.file_nit) {
      document_nit.value = props.customerData.document.file_nit.split('documents/')[1]
      switch (document_nit.value.split('.')[1]) {
        case 'pdf':
          icon_type_nit.value = 'tabler-file-type-pdf'
          break;
        case 'docx':
          icon_type_nit.value = 'mdi-file-word'
          break;
        case 'doc':
          icon_type_nit.value = 'mdi-file-word'
          break;
        case 'jpg':
          icon_type_nit.value = 'tabler-file-type-jpg'
          break;
        default:
          icon_type_nit.value = 'tabler-file-type-png'
          break;
      }
    }

    if(props.customerData.document.file_rut) {
      document_rut.value = props.customerData.document.file_rut.split('documents/')[1]
      switch (document_rut.value.split('.')[1]) {
        case 'pdf':
          icon_type_rut.value = 'tabler-file-type-pdf'
          break;
        case 'docx':
          icon_type_rut.value = 'mdi-file-word'
          break;
        case 'doc':
          icon_type_rut.value = 'mdi-file-word'
          break;
        case 'jpg':
          icon_type_rut.value = 'tabler-file-type-jpg'
          break;
        default:
          icon_type_rut.value = 'tabler-file-type-png'
          break;
      }
    }
  }
}

const download = (file, type) => {
  let data = {
    icon: type === 'nit' ? document_nit.value.split('.')[1] : document_rut.value.split('.')[1],
    document: file
  }
  emit('download', data)
}

const copy = (account) => {
  emit('copy', account)
}

</script>

<template>
  <!-- eslint-disable vue/no-v-html -->
  <!-- 👉 Payment Methods -->
  <VRow>
    <VCol cols="12" v-if="props.isSupplier">
      <VCard title="Información general de la empresa">
        <VCardText class="d-flex flex-column gap-y-4">
          <VCard border flat>
            <VCardText class="d-flex flex-sm-row flex-column pa-4">
              <VRow>
                <VCol cols="12" md="12">
                  <VList class="card-list mt-2">
                    <VListItem>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Nombre:
                          <span class="text-body-2">
                            {{ props.customerData.company_name }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Documento:
                          <span class="text-body-2">
                            ({{ props.customerData.document.type.code }}) - {{ props.customerData.document.main_document }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Número de la Cámara de Comercio:
                          <span class="text-body-2">
                             {{ props.customerData.document.ncc }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Teléfono:
                          <span class="text-body-2">
                             {{ props.customerData.phone_contact }}
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          Cámara de Comercio:
                          <span class="text-body-2" v-if="document_nit">
                            {{ document_nit }}

                            <VTooltip
                              open-on-focus
                              location="top"
                              activator="parent"
                              text="Descargar">
                              <template v-slot:activator="{ props }">
                                <VIcon color="primary" :icon="icon_type_nit" size="x-large" @click="download(document_nit, 'nit')" />
                              </template>
                            </VTooltip>
                          </span>
                        </h6>
                      </VListItemTitle>
                      <VListItemTitle>
                        <h6 class="text-base font-weight-semibold">
                          RUT:
                          <span class="text-body-2" v-if="document_rut">
                            {{ document_rut }}

                            <VTooltip
                              open-on-focus
                              location="top"
                              activator="parent"
                              text="Descargar">
                              <template v-slot:activator="{ props }">
                                <VIcon color="primary" :icon="icon_type_rut" size="x-large" @click="download(document_rut, 'rut')" />
                              </template>
                            </VTooltip>
                          </span>
                        </h6>
                      </VListItemTitle>
                    </VListItem>
                  </VList>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style>
  .iconsAddress .v-btn--icon.v-btn--density-default {
    width: calc(var(--v-btn-height) + 0px) !important;
    height: calc(var(--v-btn-height) + 0px) !important;
  }
</style>
