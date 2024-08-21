<script setup>

import router from '@/router'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { useCategoriesStores } from '@/stores/useCategories'

const categoriesStores = useCategoriesStores()

const isRequestOngoing = ref(true)

const categories = ref([])
const refForm = ref()
const isFormValid = ref(false)
const error = ref(undefined)

const emitter = inject("emitter")

const name = ref('')
const category_id = ref()
const banners = ref([])
const category_type_id = ref(1)
const categoryTypes = ref([
  {id: 1, name: 'Productos'},
  {id: 2, name: 'Servicios'},
])

const isValid =  ref(null)

watchEffect(fetchData)

async function fetchData() {

    isRequestOngoing.value = true

    let data = { 
        limit: -1, 
        category_type_id: category_type_id .value
    }

    await categoriesStores.fetchCategoriesOrder(data)

    categories.value = categoriesStores.getCategories

    isRequestOngoing.value = false
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
}

const onSubmit = () => {

    refForm.value?.validate().then(({ valid }) => {
        isValid.value = valid
        if (valid) {
            error.value = undefined

            let formData = new FormData()

            formData.append('banner', banners.value[0] ?? null)
            formData.append('banner_2', banners.value[1] ?? null)
            formData.append('banner_3', banners.value[2] ?? null)
            formData.append('banner_4', banners.value[3] ?? null)
            formData.append('name', name.value)
            formData.append('category_type_id', category_type_id.value)
            formData.append('is_category', (typeof category_id.value === 'undefined' || category_id.value === null) ? 0 : 1)
            formData.append('category_id', category_id.value)

            categoriesStores.addCategory(formData)
                .then((res) => {
                if (res.data.success) {

                    let data = {
                        message: 'Categoría Creada!',
                        error: false
                    }

                    router.push({ name : 'dashboard-products-categories'})
                    emitter.emit('toast', data)

                } else {

                    let data = {
                        message: 'ERROR',
                        error: true
                    }

                    router.push({ name : 'dashboard-products-categories'})
                    emitter.emit('toast', data)
                }
                })
                .catch((err) => {
                    let data = {
                        message: err,
                        error: true
                    }

                    router.push({ name : 'dashboard-products-categories'})
                    emitter.emit('toast', data)
                })

        }
  })
}
</script>

<template>
    <section>
        <VRow>
            <VDialog
                v-model="isRequestOngoing"
                width="300"
                persistent>
                <VCard
                    color="primary"
                    width="300">
                    <VCardText class="pt-3">
                        Cargando
                        <VProgressLinear
                            indeterminate
                            color="white"
                            class="mb-0"
                        />
                    </VCardText>
                </VCard>
            </VDialog>
        </VRow>
        <!-- 👉 Form -->
        <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit">
            <VRow>
                <VCol cols="12" md="9">
                    <VCard class="mb-8">
                        <VCardText>
                            <VRow>
                                <VCol cols="12" md="8">
                                    <VTextField
                                        v-model="name"
                                        :rules="[requiredValidator]"
                                        label="Nombre"
                                    />
                                </VCol>
                                <VCol cols="12" md="4">
                                    <VSelect
                                        label="Tipo de categoría"
                                        v-model="category_type_id"
                                        :items="categoryTypes"
                                        item-value="id"
                                        item-title="name"
                                        density="compact"
                                        variant="outlined"
                                        :rules="[requiredValidator]"/>
                                </VCol>
                                <VCol cols="12"  md="12">
                                    <VAutocomplete
                                        id="selectCategory"
                                        v-model="category_id"
                                        label="Categoría"
                                        :items="categories"
                                        :item-title="item => item.name"
                                        :item-value="item => item.id"
                                        autocomplete="off"
                                        :menu-props="{ maxHeight: '300px' }">
                                        <template v-slot:selection="{ item, index }">
                                            <VChip v-if="index < 2">
                                                <span>{{ item.title }}</span>
                                            </VChip>
                                            <span
                                                v-if="index === 2"
                                                class="text-grey text-caption align-self-center"
                                            >
                                                (+{{ category_id.length - 2 }} otros)
                                            </span>
                                        </template>
                                        <template v-slot:item="{ props, item }">
                                            <VListItem
                                                v-bind="props"
                                                :title="item?.raw?.name"
                                                :style="{ 
                                                    paddingLeft: `${(item?.raw?.level) * 20}px !important`,
                                                    paddingTop: `0 !important`,
                                                    height: `10px !important`
                                                }"
                                            >
                                                <template v-slot:prepend="{ isActive }">
                                                    <VListItemAction start>
                                                        <VCheckboxBtn :model-value="isActive"></VCheckboxBtn>
                                                    </VListItemAction>
                                                </template>
                                            </VListItem>
                                        </template>
                                        <template v-slot:append-item>
                                            <VDivider class="mt-2"></VDivider>
                                            <VListItem title="Cerrar Opciones" class="text-right">
                                                <template v-slot:append>
                                                    <VBtn
                                                    size="small"
                                                    variant="plain"
                                                    icon="tabler-x"
                                                    color="black"
                                                    :ripple="false"
                                                    @click="closeDropdown"/>
                                                </template>
                                            </VListItem>
                                        </template>
                                    </VAutocomplete>
                                </VCol>
                            </VRow>
                        </VCardText>
                    </VCard>
                </VCol>

                <VCol cols="12" md="3">
                    <VCard class="mb-8">
                        <VCardText>
                            <!-- 👉 Send Category -->
                            <VBtn
                                block
                                prepend-icon="tabler-plus"
                                class="mb-2"
                                type="submit">
                                Agregar
                            </VBtn>

                            <!-- 👉 Preview -->
                            <VBtn
                                block
                                color="default"
                                variant="tonal"
                                class="mb-2"
                                :to="{ name: 'dashboard-products-categories' }">
                                Regresar
                            </VBtn>
                        </VCardText>
                    </VCard>  
                </VCol>
            </VRow>
        </VForm>
    </section>
</template>

<route lang="yaml">
    meta:
        action: crear
        subject: productos
</route>

<style>
    .p-0 {
        padding: 0;
    }

    .p-1 {
        padding: 1rem;
    }

    .button-icon {
        height: 60px !important; 
        border-radius: 8px !important;
        margin: 2px;
    }

    .button-color {
        height: 40px !important; 
        border-radius: 8px !important;
        margin: 1px !important;
        font-size: 10px !important;
        padding: 5px !important;
    } 

    .border-img {
        border: 1.8px solid rgba(var(--v-border-color), var(--v-border-opacity));
        border-radius: 6px;
    }

    .border-img .v-img__img--contain {
        padding: 10px;
    }

    .border-error {
        border: 1.8px solid rgb(var(--v-theme-error));
    }
</style>
<route lang="yaml">
    meta:
      action: crear
      subject: categorías
</route>