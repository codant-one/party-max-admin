<!-- eslint-disable vue/no-mutating-props -->
<script setup>

import { themeConfig } from '@themeConfig'
import { formatNumber } from '@/@core/utils/formatters'

const props = defineProps({
  id: {
    type: Number,
    required: true,
  },
  data: {
    type: Object,
    required: true
  },
  typeInvoice: {
    type: String,
    required: true
  }
})

const emit = defineEmits([
  'removeProduct',
  'deleteProduct',
  'settingProduct',
  'editProduct'
])

const localProductData = ref(props.data)
const typeInvoice = ref(props.typeInvoice)


const fetchData =() => {
  localProductData.value = props.data
}

watchEffect(fetchData)

const imageUrl = computed(() => {
  return localProductData.value && localProductData.value.image
    ? themeConfig.settings.urlStorage + localProductData.value.image
    : ''
})

const removeProduct = () => {
  if(localProductData.value.disabled)
    emit('removeProduct', props.id)
  else
    emit('deleteProduct', props.id)
}

const settingProduct = () => {
  emit('settingProduct', props.id)
}

const resolveType = data => {
  // Si es un producto o servicio del nuevo sistema
  if (data.type) {
    return data.type === 'product' ? 'Producto' : 'Servicio';
  }

  // Mapeo para el sistema anterior (mantener compatibilidad)
  const mappings = [
    { key: 'video_id', type: 'Video' },
    { key: 'title_optimization_id', type: 'Optimización' },
    { key: 'ia_image_id', type: 'Imágen' },
    { key: 'redaction_id', type: 'Redación' },
    { key: 'task_id', type: 'Tarea' }
  ];

  const match = mappings.find(mapping => data[mapping.key] !== null);

  return match ? match.type : 'Producto';
};

</script>

<template>
  <VCard
    flat
    border
    class="d-flex flex-row"
  >
    <!-- 👉 Left Form -->
    <div class="pa-5 flex-grow-1">
      <VRow>
        <VCol
          cols="12"
          md="7"
        >
          <div class="d-flex align-center gap-x-2">
            <VAvatar
              size="75"
              variant="outlined" 
              rounded
              :image="imageUrl"
            />
            <div class="d-flex flex-column">
              <span class="text-body-1 font-weight-medium"> {{ localProductData.description }}</span>
              <span class="text-sm text-disabled">SKU: {{ localProductData.sku }}</span>
            </div>
          </div>
        </VCol>

        <VCol
          cols="12"
          md="1"
        class="text-center"
      >
          {{ localProductData.quantity }}
        </VCol>

        <VCol
          cols="12"
          md="2"
        class="text-end"
      >
          COP {{ formatNumber(localProductData.price) }}
        </VCol>

        <VCol
          cols="12"
          md="2"
        class="text-end"
      >
          COP {{ formatNumber(localProductData.total) }}
        </VCol>

      </VRow>
    </div>

    <!-- 👉 Item Actions -->
    <div class="d-flex flex-column justify-space-between border-s pa-0">
      <VBtn 
        v-if="typeInvoice == '0'"
        icon="tabler-x"
        variant="text"
        @click="removeProduct">
        
      </VBtn>
      <VBtn 
        v-if="typeInvoice !== '0'"
        variant="text">
        
      </VBtn>

      <!-- <VBtn 
        v-show="localProductData.disabled"
        icon="tabler-settings"
        variant="text"
        @click="settingProduct">
      </VBtn> -->
    </div>
  </VCard>
</template>
