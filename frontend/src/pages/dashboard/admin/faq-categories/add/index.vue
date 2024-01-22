<script setup>

import router from '@/router'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { useCategoriesStores } from '@/stores/useFaqCategories'

const categoriesStores = useCategoriesStores()

const refForm = ref()
const isFormValid = ref(false)
const toggleIcons = ref(-1)
const toggleColors = ref(-1)
const error = ref(undefined)

const emitter = inject("emitter")

const name = ref(null)
const description = ref('')
const icon = ref('')
const iconText = ref('')
const color = ref('default')

const colorsList = ref([
    'Default',
    'Primary',
    'Secondary',
    'Success',
    'Info',
    'Warning',
    'Error'      
])

const iconsList = ref([
    'tabler-2fa',
    'tabler-3d-cube-sphere-off',
    'tabler-3d-cube-sphere',
    'tabler-3d-rotate',
    'tabler-a-b-2',
    'tabler-a-b-off',
    'tabler-a-b',
    'tabler-abacus-off',
    'tabler-abacus',
    'tabler-access-point-off',
    'tabler-access-point',
    'tabler-accessible-off',
    'tabler-accessible',
    'tabler-activity-heartbeat',
    'tabler-activity',
    'tabler-ad-2',
    'tabler-ad-off',
    'tabler-ad',
    'tabler-address-book-off',
    'tabler-address-book',
    'tabler-adjustments-alt',
    'tabler-adjustments-horizontal',
    'tabler-adjustments-off',
    'tabler-adjustments',
    'tabler-aerial-lift',
    'tabler-affiliate',
    'tabler-air-balloon',
    'tabler-air-conditioning',
    'tabler-alarm-off',
    'tabler-alarm',
    'tabler-album-off',
    'tabler-album',
    'tabler-alert-circle',
    'tabler-alert-octagon',
    'tabler-alert-triangle',
    'tabler-alien',
    'tabler-align-center',
    'tabler-align-justified',
    'tabler-align-left',
    'tabler-align-right',
    'tabler-alphabet-cyrillic',
    'tabler-alphabet-greek',
    'tabler-alphabet-latin',
    'tabler-ambulance',
    'tabler-ampersand',
    'tabler-analyze-off',
    'tabler-analyze',
    'tabler-anchor-off',
    'tabler-anchor',
    'tabler-angle',
    'tabler-ankh',
    'tabler-antenna-bars-1',
    'tabler-antenna-bars-2',
    'tabler-antenna-bars-3',
    'tabler-antenna-bars-4',
    'tabler-antenna-bars-5',
    'tabler-antenna-bars-off',
    'tabler-antenna-off',
    'tabler-antenna',
    'tabler-aperture-off',
    'tabler-aperture',
    'tabler-api-app-off',
    'tabler-api-app',
    'tabler-api-off',
    'tabler-api',
    'tabler-app-window',
    'tabler-apple',
    'tabler-apps-off',
    'tabler-apps',
    'tabler-archive-off',
    'tabler-archive',
    'tabler-armchair-2-off',
    'tabler-armchair-2',
    'tabler-armchair-off',
    'tabler-armchair',
    'tabler-arrow-autofit-content',
    'tabler-arrow-autofit-down',
    'tabler-arrow-autofit-height',
    'tabler-arrow-autofit-left',
    'tabler-arrow-autofit-right',
    'tabler-arrow-autofit-up',
    'tabler-arrow-autofit-width',
    'tabler-arrow-back-up',
    'tabler-arrow-back',
    'tabler-arrow-bar-down',
    'tabler-arrow-bar-left',
    'tabler-arrow-bar-right',
    'tabler-arrow-bar-to-down',
    'tabler-arrow-bar-to-left',
    'tabler-arrow-bar-to-right',
    'tabler-arrow-bar-to-up',
    'tabler-arrow-bar-up',
    'tabler-arrow-bear-left-2',
    'tabler-arrow-bear-left',
    'tabler-arrow-bear-right-2',
    'tabler-arrow-bear-right',
    'tabler-arrow-big-down-line',
    'tabler-arrow-big-down-lines',
    'tabler-arrow-big-down',
    'tabler-arrow-big-left-line',
    'tabler-arrow-big-left-lines',
    'tabler-arrow-big-left',
    'tabler-arrow-big-right-line',
    'tabler-arrow-big-right-lines',
    'tabler-arrow-big-right',
    'tabler-arrow-big-top',
    'tabler-arrow-big-up-line',
    'tabler-arrow-big-up-lines',
    'tabler-arrow-bounce',
    'tabler-arrow-curve-left',
    'tabler-arrow-curve-right',
    'tabler-arrow-down-bar',
    'tabler-arrow-down-circle',
    'tabler-arrow-down-left-circle',
    'tabler-arrow-down-left',
    'tabler-arrow-down-right-circle',
    'tabler-arrow-down-right',
    'tabler-arrow-down-square',
    'tabler-arrow-down-tail',
    'tabler-arrow-down',
    'tabler-arrow-fork',
    'tabler-arrow-forward-up',
    'tabler-arrow-forward',
    'tabler-arrow-guide',
    'tabler-arrow-left-bar',
    'tabler-arrow-left-circle',
    'tabler-arrow-left-right',
    'tabler-arrow-left-square',
])

const selectColor = colorText => {
   color.value = colorText
}

const selectIcon = iconLabel => {

    error.value = 
        (toggleIcons.value === -1 && iconText.value === '') ? 
        'Agregar icono o seleccionar uno' : 
        undefined

    icon.value = iconLabel
    iconText.value = ''
}

const removePrefix = (text, prefix) => {
  if(text.startsWith(prefix)) {
    return text.replace(prefix, '').trim();
  }
  return text;
}

const changeInput = () => {

    iconText.value = (iconText.value === '' || iconText.value === null) ? iconText.value : removePrefix(iconText.value, "mdi ")

    error.value = 
        (toggleIcons.value === -1 && iconText.value === '') ? 
        'Agregar icono o seleccionar uno' : 
        undefined
       
    icon.value = 
        (iconText.value === '' || iconText.value === null) ? 
        iconsList.value[toggleIcons.value] : 
        iconText.value

    if(iconText.value !== null && iconText.value !== '')
        toggleIcons.value = -1
}

const onSubmit = () => {

    refForm.value?.validate().then(({ valid }) => {
        
        if (valid) {

            if(toggleIcons.value === -1 && iconText.value === '') {
                error.value = 'Agregar icono o seleccionar uno'
                return
            }

            error.value = undefined

            let formData = new FormData()

            formData.append('name', name.value)
            formData.append('description', description.value)
            formData.append('icon', icon.value)
            formData.append('color', color.value)
            
            categoriesStores.addCategory(formData)
                .then((res) => {
                if (res.data.success) {

                    let data = {
                    message: 'Categoría creada!',
                    error: false
                    }

                    router.push({ name : 'dashboard-admin-faq-categories'})
                    emitter.emit('toast', data)

                } else {

                    let data = {
                    message: 'ERROR',
                    error: true
                    }

                    router.push({ name : 'dashboard-admin-faq-categories'})
                    emitter.emit('toast', data)
                }
                })
                .catch((err) => {
                    let data = {
                    message: err,
                    error: true
                    }

                    router.push({ name : 'dashboard-admin-faq-categories'})
                    emitter.emit('toast', data)
                })

        }
  })
}
</script>

<template>
    <section>
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
                                <VCol cols="12" md="5">
                                    <VRow>
                                        <VCol cols="12">
                                            <VTextField
                                                v-model="name"
                                                :rules="[requiredValidator]"
                                                label="Nombre"
                                            />
                                        </VCol>
                                        <VCol cols="12">
                                            <VTextarea
                                                v-model="description"
                                                label="Descripción"
                                            />
                                        </VCol>
                                    </VRow>
                                </VCol>

                                <VCol cols="12" md="7">
                                    <VRow>
                                        <VCol cols="12" md="2"></VCol>
                                        <VCol cols="12" md="10" class="p-0">
                                        <VCol cols="12">
                                            <VTextField
                                                v-model="iconText"
                                                clearable
                                                clear-icon="tabler-circle-x"
                                                label="Icono"
                                                type="text"
                                                :error-messages="error"
                                                @click:clear="changeInput"
                                                @input="changeInput"
                                                >
                                                <!-- Prepend -->
                                                <template #prepend>
                                                    <VTooltip location="bottom">
                                                        <template #activator="{ props }">
                                                            <VIcon
                                                                v-bind="props"
                                                                icon="tabler-help"
                                                            />
                                                        </template>
                                                        Puede agregar un texto de icono si no selecciona alguno.<br><br>
                                                        En la página de "Pictogrammers" debes seleccionar el icono de tu preferencia.<br>
                                                        Ir al TAG "WEBFONT", el cual mostrara una etiqueta con una clase, por ejemplo:<br>
                                                        <strong style="font-style: italic;">`class='mdi mdi-abugida-devanagari'`</strong><br>
                                                        Copiar la segunda clase, por ejemplo: <strong> `mdi-abugida-devanagari`</strong><br><br>
                                                        <strong>NOTA: </strong> Si copias la clase completa 
                                                        <strong style="font-style: italic;">mdi mdi-abugida-devanagari</strong> borra la primera clase <strong style="font-style: italic;">mdi</strong><br><br>
                                                        Pegar el texto para generar el icono
                                                    </VTooltip>
                                                </template>
                                            </VTextField>
                                        </VCol>
                                    </VCol>
                                    </VRow>

                                    <VRow>
                                        <VCol cols="12">
                                            <VCard>
                                                <VCardTitle class="text-base font-weight-semibold">
                                                    <VTooltip location="bottom">
                                                        <template #activator="{ props }">
                                                            <VIcon
                                                                v-bind="props"
                                                                icon="mdi-information"
                                                            />
                                                        </template>
                                                        El icono que se refleja en esta seccion es el que se mostrará en instrucciones
                                                    </VTooltip>
                                                    CUSTOM ICON
                                                </VCardTitle>
                                                <VCardItem class="text-center pt-0">
                                                    <VAvatar
                                                        :icon="icon"
                                                        rounded
                                                        :color="color"
                                                        variant="tonal"
                                                    />
                                                </VCardItem>
                                                <VCardActions>
                                                <VBtnToggle
                                                    v-model="toggleColors"
                                                    variant="plain"
                                                    class="d-flex align-center flex-wrap"
                                                    style="height: auto;"
                                                >
                                                    <VBtn 
                                                        v-for="color in colorsList" 
                                                        class="button-color"
                                                        @click="selectColor(color.toLowerCase())">
                                                        <VChip label :color="color.toLowerCase()">
                                                            {{ color }}
                                                        </VChip>
                                                    </VBtn>
                                                </VBtnToggle>
                                            </VCardActions>
                                            </VCard>
                                        </VCol>
                                    </VRow>
                                </VCol>
                            
                                <VCol cols="12">
                                    <span class="text-h6 font-weight-semibold">
                                        ICONO
                                    </span>
                                    <VBtnToggle
                                        v-model="toggleIcons"
                                        color="primary"
                                        class="d-flex align-center flex-wrap ms-5"
                                        style="height: auto;"
                                    >
                                        <VBtn 
                                            v-for="icon in iconsList" 
                                            class="button-icon"
                                            @click="selectIcon(icon)">
                                            <VIcon size="30" :icon="icon"/>
                                        </VBtn>
                                    </VBtnToggle>
                                    <!-- more icons -->
                                    <div class="text-center">
                                        <VBtn
                                            href="https://materialdesignicons.com/"
                                            rel="noopener noreferrer"
                                            color="primary"
                                            target="_blank"
                                        >
                                        Ver todos los iconos
                                        </VBtn>
                                    </div>
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
                            <v-btn
                                block
                                color="default"
                                variant="tonal"
                                class="mb-2"
                                :to="{ name: 'dashboard-admin-faq-categories' }">
                                Regresar
                            </v-btn>
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
        subject: categorías-faqs
</route>

<style>
    .p-0 {
        padding: 0;
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
</style>
<route lang="yaml">
    meta:
      action: crear
      subject: categorías-faqs
</route>
