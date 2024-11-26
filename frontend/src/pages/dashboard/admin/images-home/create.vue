<script setup>
import { themeConfig } from '@themeConfig'
import { requiredValidator } from '@/@core/utils/validators'
import { useHomeImagesStores } from '@/stores/useHomeImages'

const isHomeImageCreateDialog = ref(false)
const isHomeImageSelectedDialog = ref(false)
const slider_option = ref(null)
const filename = ref([])
const url = ref(null)
const sliderOptions = ref([
  { value: 1, text: 'Pertenece al Slider'},
  { value: 0, text: 'No pertenece al Slider'}
])

const emit = defineEmits([
  'alert',
  'data'
])

const homeimagesStores = useHomeImagesStores()

const refFormCreate = ref()

const name = ref('')
const assignedPermissions = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const closeHomeImageCreateDialog = function(){
    isHomeImageCreateDialog.value = false
}


const onImageSelected = event => {
  const file = event.target.files[0]
  
  if (!file) return
  filename.value = file
  URL.createObjectURL(file)
}

const onSubmitCreate = () => {
    refFormCreate.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      const formData = new FormData()
      formData.append('image', filename.value) // Agregamos la imagen
      formData.append('is_slider', slider_option.value)
      formData.append('url', url.value)
      formData.append('order_id', 1)
      console.log(filename.value)
      homeimagesStores
        .addHomeImages(formData)
        .then(response => {
          closeHomeImageCreateDialog()
          window.scrollTo(0, 0)

          advisor.value.show = true
          advisor.value.type = 'success'
          advisor.value.message = 'Imagen agregada!'

          emit('alert', advisor)
          emit('data')

          setTimeout(() => {
            advisor.value.show = false
            advisor.value.type = ''
            advisor.value.message = ''
            emit('alert', advisor)
          }, 5000)
        })
        .catch(error => {
          closeHomeImageCreateDialog()
          window.scrollTo(0, 0)

          advisor.value.show = true
          advisor.value.type = 'error'

          if (error.feedback === 'params_validation_failed') {
            if (error.message.hasOwnProperty('name'))
              advisor.value.message = error.message.name[0]
            else if (error.message.hasOwnProperty('permissions'))
              advisor.value.message = error.message.permissions[0]
          } else {
            advisor.value.message = 'Se ha producido un error...! (Server Error)'
          }

          emit('alert', advisor)
          emit('data')

          setTimeout(() => {
            advisor.value.show = false
            advisor.value.type = ''
            advisor.value.message = ''
            emit('alert', advisor)
          }, 5000)
        })
    }
  })
}

</script>

<template>
    <!-- 👉 crear Imagen -->
    <VDialog
        v-model="isHomeImageCreateDialog"
        max-width="600"
        persistent
        >
        <!-- Dialog Activator -->
        <template #activator="{ props }">
            <VBtn
              v-bind="props"
              prepend-icon="tabler-plus"
            >
              Crear Imagen
            </VBtn>
        </template>

        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeHomeImageCreateDialog" />

        <!-- Dialog Content -->
        <VCard title="Crear Imagen">
            <VDivider class="mt-4"/>
            <VCardText>
                <VForm
                    ref="refFormCreate"
                    @submit.prevent="onSubmitCreate"
                >
                    <VRow>
                        <VCol cols="12">
                            <VFileInput
                                v-model="filename"
                                label="Imagen"
                                class="mb-2"
                                accept="image/png, image/jpeg, image/bmp, image/webp"
                                prepend-icon="tabler-camera"
                               @change="onImageSelected"
                                @click:clear="avatar = null"
                                :rules="[requiredValidator]"
                            />        
                        </VCol>
                        <VCol cols="12">
                            <AppSelect
                                v-model="slider_option"
                                :items="sliderOptions"
                                item-value="value"
                                item-title="text"
                                placeholder="Slider"
                                :rules="[requiredValidator]"
                                />
                        </VCol>
                        <VCol>
                            <AppTextField
                                v-model="url"
                                label="Enlace"
                                placeholder="URL Destino"
                                :rules="[requiredValidator]"
                            />
                        </VCol>
                    </VRow>
                    <VCardText class="d-flex justify-end gap-3 flex-wrap">
                        <VBtn
                            color="secondary"
                            variant="tonal"
                            @click="closeHomeImageCreateDialog"
                        >
                            Cancelar
                        </VBtn>
                        <VBtn type="submit">
                            Crear
                        </VBtn>
                    </VCardText>
                </VForm>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<route lang="yaml">
    meta:
      action: crear
      subject: images-home
  </route>
