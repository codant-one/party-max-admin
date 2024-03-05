<script setup>

import congoImg from '@images/illustrations/congo-illustration.png'

const userDataJ = ref('')
const name = ref('')

watchEffect(fetchData)

async function fetchData() {

    const userData = localStorage.getItem('user_data')
    
    userDataJ.value = JSON.parse(userData)
    name.value = userDataJ.value.name
}

</script>

<template>
  <VCard>
    <VRow no-gutters>
      <VCol cols="8">
        <VCardText>
          <h6 class="text-lg text-no-wrap font-weight-medium">
            Felicidades {{ name }}! 🎉
          </h6>
          <p class="mb-2">
            Progreso de ventas
          </p>
          <h4 class="text-h4 font-weight-medium text-primary mb-1">
            $48.90
          </h4>
          <VBtn>Ver pedidos</VBtn>
        </VCardText>
      </VCol>

      <VCol cols="4">
        <VCardText class="pb-0 px-0 position-relative h-100">
          <VImg
            :src="congoImg"
            height="147"
            class="congo-john-img w-100"
          />
        </VCardText>
      </VCol>
    </VRow>
  </VCard>
</template>

<style lang="scss" scoped>
    .congo-john-img {
    position: absolute;
    inset-block-end: 0;
    inset-inline-end: 1.25rem;
    }
</style>
