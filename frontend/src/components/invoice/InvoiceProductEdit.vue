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
  }
})

const emit = defineEmits([
  'removeProduct',
  'deleteProduct',
  'settingProduct',
  'editProduct'
])

const localProductData = ref(props.data)


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
  <!-- eslint-disable vue/no-mutating-props -->
  <div class="add-products-header mb-4 d-none d-md-flex ps-5 pe-16">
    <VRow class="font-weight-medium">
      <VCol
        cols="12"
        md="2"
      >
        <h6 class="text-sm font-weight-medium">
          <span class="text-base">
            IMAGEN
          </span>
        </h6>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <span class="text-base">
          DESCRIPCIÓN
        </span>
      </VCol>     

      <VCol
        cols="12"
        md="2"
        class="text-center"
      >
        <span class="text-base">
          CANTIDAD
        </span>
      </VCol>

      <VCol
        cols="12"
        md="2"
        class="text-center"
      >
        <span class="text-base">
          PRECIO
        </span>
      </VCol>

      <VCol
        cols="12"
        md="2"
        class="text-center"
      >
        <span class="text-base">
          TOTAL
        </span>
      </VCol>
    </VRow>
  </div>

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
          md="2"
        >
          <VAvatar
            size="50"
            variant="outlined" 
            rounded
            :image="imageUrl"
          />
        </VCol>

        <VCol
          cols="12"
          md="4"
          sm="4"
        >
          {{ localProductData.description }}
        </VCol>

        <VCol
          cols="12"
          md="2"
          sm="4"
        class="text-center"
      >
          {{ localProductData.quantity }}
        </VCol>

        <VCol
          cols="12"
          md="2"
          sm="4"
        class="text-end"
      >
          ${{ formatNumber(localProductData.price) }}
        </VCol>

        <VCol
          cols="12"
          md="2"
          sm="4"
        class="text-end"
      >
          ${{ formatNumber(localProductData.total) }}
        </VCol>

      </VRow>
    </div>

    <!-- 👉 Item Actions -->
    <div class="d-flex flex-column justify-space-between border-s pa-0">
      <VBtn 
        icon="tabler-x"
        variant="text"
        @click="removeProduct">
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
