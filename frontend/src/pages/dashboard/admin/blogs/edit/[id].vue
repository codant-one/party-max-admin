<script setup>

import ImageUploader from 'quill-image-uploader';
import router from '@/router'
import { inject } from "vue"
import { requiredValidator } from '@validators'
import { themeConfig } from '@themeConfig'
import { useBlogsStores } from '@/stores/useBlogs'
import { useCategoriesStores } from '@/stores/useBlogCategories'
import { QuillEditor } from '@vueup/vue-quill'
import { useTagsStores } from '@/stores/useTags'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const categoriesStores = useCategoriesStores()
const blogsStores = useBlogsStores()
const tagsStores = useTagsStores() 
const route = useRoute()

const refForm = ref()
const isFormValid = ref(false)

const emitter = inject("emitter")

const listTags = ref([])
const tag_id = ref()

const categories = ref([])
const blog_category_id = ref('')
const is_popular_blog = ref(false)
const blog = ref(null)
const title = ref(null)
const description = ref('')
const date = ref('')
const image = ref('')
const avatar = ref('')
const avatarOld = ref('')
const filename = ref([])

const isRequestOngoing = ref(true)

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
        
        const formData = new FormData();
              
        formData.append("image", file);

        axios.post('/blogs/upload-image', formData)
          .then(res => {
            resolve(themeConfig.configuraciones.urlStorage + res.data.url);
          })
          .catch(err => {
            reject("Upload failed");
            console.error("Error:", err)
          })
      })
    }
  }
}

const getCategories = computed(() => {
  return categories.value.map((category) => {
    return {
      title: category.name,
      value: category.id,
    }
  })
})

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  let data = { limit: -1 }

  await categoriesStores.fetchCategories(data)
  categories.value = categoriesStores.getCategories

  let tags = { 
    tag_type_id: 2,
    limit: -1 
  }

  await tagsStores.fetchTags(tags)
  listTags.value = tagsStores.getTags
  
  if(Number(route.params.id)) {
    blog.value = await blogsStores.showBlog(Number(route.params.id))

    blog_category_id.value = blog.value.blog_category_id
    is_popular_blog.value = blog.value.is_popular_blog === 1 ? true : false
    title.value = blog.value.title
    date.value = blog.value.date
    description.value = blog.value.description
    avatar.value = blog.value.image === null ? '' : themeConfig.settings.urlStorage + blog.value.image
    avatarOld.value = blog.value.image === null ? '' : themeConfig.settings.urlStorage + blog.value.image

    tag_id.value = blog.value.tags.map(element => element.tag_id)
  }

  isRequestOngoing.value = false
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

      formData.append('blog_category_id', blog_category_id.value)
      formData.append('is_popular_blog', (is_popular_blog.value === true) ? 1 : 0)
      formData.append('title', title.value)
      formData.append('date', date.value)
      formData.append('description', description.value)
      formData.append('image', image.value)
      formData.append('_method', 'PUT')

      //blog_tags
      formData.append('tag_id', tag_id.value)
            
      let data = {
        data: formData, 
        id: Number(route.params.id)
      }

      blogsStores.updateBlog(data)
        .then((res) => {

          if (res.data.success) {

            let data = {
              message: 'Blog actualizado!',
              error: false
            }

            router.push({ name : 'dashboard-admin-blogs'})
            emitter.emit('toast', data)

          } else {

            let data = {
              message: 'ERROR',
              error: true
            }

            router.push({ name : 'dashboard-admin-blogs'})
            emitter.emit('toast', data)
          }
        })
        .catch((err) => {
            let data = {
              message: err,
              error: true
            }

            router.push({ name : 'dashboard-admin-blogs'})
            emitter.emit('toast', data)
        })

    }
  })
}

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
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
                class="mb-0"/>
            </VCardText>
          </VCard>
        </VDialog>
        
        <VCol cols="12" md="9">
          <VCard class="mb-8">
            <VCardText>
              <VRow>
                <VCol cols="12" md="8"></VCol>
                <VCol
                  cols="12"
                  md="4"
                  class="d-flex justify-content-end"
                  >
                    <VCheckbox
                      v-model="is_popular_blog"
                      :label="capitalizedLabel('es Popular?')"
                    />
                </VCol>
                <VCol cols="12" md="6">
                    <VTextField
                        v-model="title"
                        :rules="[requiredValidator]"
                        label="Título"
                        />
                </VCol>
                <VCol
                  cols="12"
                  md="3"
                >
                  <v-autocomplete
                        v-model="blog_category_id"
                        label="Categoría"
                        :rules="[requiredValidator]"
                        :items="getCategories"
                        :menu-props="{ maxHeight: '200px' }"
                      />
                </VCol>
                <VCol
                  cols="12"
                  md="3"
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
                  <AppSelect
                    v-model="tag_id"
                    chips
                    multiple
                    closable-chips
                    :items="listTags"
                    item-value="id"
                    item-title="name"
                    placeholder="Tags"
                    color="primary"
                  />
                </VCol>
                <VCol cols="12">
                    <VImg
                        v-if="avatar !== null"
                        :src="avatar"
                        :height="300"
                        aspect-ratio="1/1"
                        class="border-img"
                        cover
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
                        @click:clear="avatar = avatarOld"
                    />
                </VCol>
                <VCol cols="12" class="editor">
                  <QuillEditor
                    v-model:content="description" 
                    :modules="modules" 
                    contentType="html"
                    toolbar="full" />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="3">
          <VCard class="mb-8">
            <VCardText>
              <!-- 👉 Send Blog -->
              <VBtn
                block
                prepend-icon="tabler-pencil"
                class="mb-2"
                type="submit">
                  Actualizar
              </VBtn>

              <!-- 👉 Preview -->
              <v-btn
                block
                color="default"
                variant="tonal"
                class="mb-2"
                :to="{ name: 'dashboard-admin-blogs' }">
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
    action: editar
    subject: blogs
</route>

<style>
 .justify-content-end{
    justify-content: end !important;
  }
  
    .editor {
        min-height: 450px;
        padding-bottom: 100px;
    }

    .ql-container {
        overflow-y: auto !important;
        height: 450px !important;
    }

    .ql-editor .ql-video {
        padding: 0 15% !important;
        width: 100% !important;
        height: inherit !important;
    }

    .ql-snow .ql-tooltip {
        left: 25% !important;
    }

    .ql-snow .ql-tooltip input[type=text] {
        width: 300px !important;
    }
    .border-img {
        border: 1.8px solid rgba(var(--v-border-color), var(--v-border-opacity));
        border-radius: 6px;
    }

    .border-img .v-img__img--contain {
        padding: 10px;
    }
</style>
<route lang="yaml">
  meta:
    action: editar
    subject: blogs
</route>
