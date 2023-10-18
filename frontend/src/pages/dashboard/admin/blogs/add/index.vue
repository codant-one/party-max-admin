<script setup>

import ImageUploader from 'quill-image-uploader'
import router from '@/router'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { themeConfig } from '@themeConfig'
import { useBlogsStores } from '@/stores/useBlogs'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const blogsStores = useBlogsStores()

const refForm = ref()
const isFormValid = ref(false)

const emitter = inject("emitter")

const title = ref(null)
const description = ref('')
const date = ref('')
const image = ref('')
const avatar = ref('')
const filename = ref([])

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: 'Y-m-d'
  }

  return config
})

const modules = {
  name: 'imageUploader',
  module: ImageUploader,
  options: {
    upload: file => {
    
      return new Promise((resolve, reject) => {
        
        const formData = new FormData()
              
        formData.append("image", file)

        axios.post('/blogs/upload-image', formData)
          .then(res => {
            resolve(themeConfig.settings.urlStorage + res.data.url)
          })
          .catch(err => {
            reject("Upload failed")
            console.error("Error:", err)
          })
      })
    },
  },
}

const onImageSelected = event => {
  const file = event.target.files[0]

  if (!file) return
  // image.value = file

  URL.createObjectURL(file)

  resizeImage(file, 400, 400, 0.9)
    .then(async blob => {
        image.value = blob
        let r = await blobToBase64(blob)
        avatar.value = 'data:image/jpeg;base64,' + r
    })
}

const resizeImage = function(file, maxWidth, maxHeight, quality) {
  return new Promise((resolve, reject) => {
    const img = new Image()

    img.src = URL.createObjectURL(file)
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')

      let width = img.width
      let height = img.height

      if (maxWidth && width > maxWidth) {
        height *= maxWidth / width
        width = maxWidth
      }

      if (maxHeight && height > maxHeight) {
        width *= maxHeight / height
        height = maxHeight
      }

      canvas.width = width
      canvas.height = height

      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(blob => {
        resolve(blob)
      }, file.type, quality)
    }
    img.onerror = error => {
      reject(error)
    }
  })
}

const blobToBase64 = blob => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.readAsDataURL(blob)
    reader.onload = () => {
      resolve(reader.result.split(',')[1])
    }
    reader.onerror = error => {
      reject(error)
    }
  })
}

const onSubmit = () => {

  refForm.value?.validate().then(({ valid }) => {
    if (valid) {

      let formData = new FormData()

      formData.append('title', title.value)
      formData.append('description', description.value)
      formData.append('image', image.value)
      formData.append('date', date.value)

      blogsStores.addBlog(formData)
        .then(res => {
          if (res.data.success) {
            let data = {
              message: 'Blog creado!',
              error: false,
            }

            router.push({ name : 'dashboard-admin-blogs' })
            emitter.emit('toast', data)

          } else {

            let data = {
              message: 'ERROR',
              error: true,
            }

            router.push({ name : 'dashboard-admin-blogs' })
            emitter.emit('toast', data)
          }
        })
        .catch(err => {
          let data = {
            message: err,
            error: true,
          }

          router.push({ name : 'dashboard-admin-blogs' })
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
      @submit.prevent="onSubmit"
    >
      <VRow>
        <VCol
          cols="12"
          md="9"
        >
          <VCard class="mb-8">
            <VCardText>
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                    <VTextField
                        v-model="title"
                        :rules="[requiredValidator]"
                        label="Título"
                    />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                    <AppDateTimePicker
                        :key="JSON.stringify(startDateTimePickerConfig)"
                        v-model="date"
                        :rules="[requiredValidator]"
                        label="Fecha"
                        :config="startDateTimePickerConfig"
                        />
                </VCol>
              
                <VCol cols="12">
                    <VImg
                        v-if="avatar !== null"
                        :src="avatar"
                        :height="200"
                        aspect-ratio="16/9"
                        class="border-img"
                    />
                </VCol>
                <VCol cols="12">
                    <VFileInput
                        v-model="filename"
                        label="Imagen"
                        class="mb-2"
                        accept="image/png, image/jpeg, image/bmp"
                        prepend-icon="tabler-camera"
                        @change="onImageSelected"
                        @click:clear="avatar = null"
                        :rules="[requiredValidator]"
                    />
                </VCol>
                <VCol
                  cols="12"
                  class="editor"
                >
                  <QuillEditor
                    v-model:content="description" 
                    :modules="modules" 
                    content-type="html"
                    toolbar="full"
                  />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>

        <VCol
          cols="12"
          md="3"
        >
          <VCard class="mb-8">
            <VCardText>
              <!-- 👉 Send Blog -->
              <VBtn
                block
                prepend-icon="tabler-plus"
                class="mb-2"
                type="submit"
              >
                Agregar
              </VBtn>

              <!-- 👉 Preview -->
              <VBtn
                block
                color="default"
                variant="tonal"
                class="mb-2"
                :to="{ name: 'dashboard-admin-blogs' }"
              >
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
    subject: blogs
</route>

<style>
    .editor {
        min-block-size: 450px;
        padding-block-end: 100px;
    }

    .ql-container {
        block-size: 450px !important;
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

    .border-img .v-img__img--contain {
        padding: 10px;
    }
</style>
