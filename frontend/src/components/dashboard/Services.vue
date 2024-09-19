<script setup>

import { themeConfig } from '@themeConfig'

const props = defineProps({
  services: {
    type: Object,
    required: true
  }
})

const items = ref(props.services)

</script>

<template>
  <VCard
    title="Servicios Populares"
    subtitle="Top 6 servicios más vendidos"
    :style="{ minHeight: '498px' }"
  >
    <VCardText>
      <VList class="card-list">
        <VListItem
          v-for="service in items"
          :key="service.title"
        >
          <template #prepend>
            <VAvatar
              size="46"
              rounded
              :image="themeConfig.settings.urlStorage + service.image"
            />
          </template>

          <VListItemTitle class="font-weight-medium" style="width: 230px;">
            {{ service.name }}
          </VListItemTitle>
          <VListItemSubtitle class="text-disabled d-flex" style="width: 230px;">
            SKU: {{ service.sku }} 
          </VListItemSubtitle>
          <template #append>
            <span >({{ service.total_sold }})</span>
          </template>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>
