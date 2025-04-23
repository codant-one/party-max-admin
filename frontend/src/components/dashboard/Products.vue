<script setup>

import { themeConfig } from '@themeConfig'
import router from '@/router'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    required: true
  },
  isPopular: {
    type: String,
    required: true
  },
  products: {
    type: Object,
    required: true
  }
})

const items = ref(props.products)

const go = (id) => {
  router.push({ name : 'dashboard-products-products-edit-id', params: { id: id } })
}

</script>

<template>
  <VCard
    :title="props.title"
    :subtitle="props.subtitle"
    :style="{ minHeight: '498px' }"
  >
  <template #append  v-if="isPopular === '0'">
    <VBtn
      :to="{ name: 'dashboard-products-products-inventory' }"
      icon
      variant="text"
      color="default"
      size="x-small">
      <VTooltip
        open-on-focus
        location="top"
        activator="parent">
        Abrir
      </VTooltip>
      <VIcon
        size="20"
        icon="mdi-open-in-new"
        class="me-1"
      />
    </VBtn>
  </template>
    <VCardText>
      <VList class="card-list">
        <VListItem
          v-for="product in items"
          :key="product.title"
        >
          <template #prepend>
            <VProgressCircular
              v-if="isPopular === '0'"
              v-model="product.stock_percentage"
              :size="46"
              class="me-4"
              :color="product.stock_percentage > 50 ? 'info' : 'warning'"
            >
              <span class="text-xs text-high-emphasis font-weight-medium">
                {{ product.stock_percentage }}%
              </span>
            </VProgressCircular>
            <VAvatar
              v-else
              size="46"
              rounded
              :image="themeConfig.settings.urlStorage + product.image"
            />
          </template>

          <VListItemTitle class="font-weight-medium" style="width: 230px;">
            {{ product.name }}
          </VListItemTitle>
          <VListItemSubtitle class="text-disabled d-flex" style="width: 230px;">
            SKU: {{ isPopular === '0' ? product.sku : product.colors[0].sku }} 
            <VSpacer />
            <span v-if="isPopular === '0'">({{ product.stock }}/{{ product.wholesale_min }})</span>
          </VListItemSubtitle>
          <template #append>
            <VBtn
              v-if="isPopular === '0'"
              variant="tonal"
              color="default"
              class="rounded-sm"
              size="30"
              @click="go(product.id)">
              <VIcon
                icon="tabler-chevron-right"
                class="flip-in-rtl"
              />
            </VBtn>
            <span v-else>({{ product.total_sold }})</span>
          </template>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>
