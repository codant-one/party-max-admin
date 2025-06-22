<script setup>

import { themeConfig } from '@themeConfig'
import { formatNumber } from '@/@core/utils/formatters'

const props = defineProps({
    service: {
        type: Object,
        required: true
    },
    rol: {
        type: String,
        required: true
    }
})

const emit = defineEmits([
    'alert',
    'copy',
    'open',
    'download',
    'updateLink',
    'show',
    'activeService',
    'editService',
    'deleteService'
])

const id = ref('')
const favourite = ref('')
const archived = ref('')
const discarded = ref(null)
const title = ref('')
const image = ref('')
const store = ref(null)
const state = ref(null)
const price = ref('')
const originalLink = ref('')
const rating = ref(null)
const comments = ref(null)
const sales =  ref(null)
const selling_price = ref(null)
const likes =  ref(null)

const categories = ref('')
const rol = ref(null)

watchEffect(() => {

    if (!(Object.entries(props.service).length === 0) && props.service.constructor === Object) {
        id.value = props.service.id
        favourite.value = props.service.favourite ?? -1
        archived.value = props.service.archived ?? null
        discarded.value = props.service.discarded ?? null
        title.value = props.service.title ?? null
        image.value = props.service.image === null ? '' : themeConfig.settings.urlStorage + props.service.image
        store.value = props.service.user.user_detail.store_name ?? (props.service.user.supplier?.company_name ?? (props.service.user.name + ' ' + (props.service.user.last_name ?? '')))
        state.value = props.service.state
        price.value = props.service.price ?? null
        originalLink.value = props.service.originalLink ?? null
        rating.value = props.service.rating ?? -1
        comments.value = props.service.comments ?? -1
        sales.value = props.service.sales ?? -1
        likes.value = props.service.likes ?? -1
        selling_price.value = props.service.selling_price ?? -1
        categories.value = props.service.categories

        rol.value = props.rol
    }
})

const copy = (link) => {
    emit('copy', link)
}

const download = (img) => {
    emit('download', img)
}

const open = (link) =>{
    emit('open', link)
}

const show = (id) => {
    emit('show', id)
}

const activeService = (id) => {
    emit('activeService', id)
}

const editService = (id) => {
    emit('editService', id)
}

const deleteService = (id) => {
    emit('deleteService', id)
}

const updateLink = (text, id) => {

    let value = 0

    if(text === 'favourite')
        value = (favourite.value === 1) ? 0 : 1
    else if(text === 'archived')
        value = (archived.value === 1) ? 0 : 1
    else if(text === 'discarded')
        value = (discarded.value === 1) ? 0 : 1

    let data = {
        text: text,
        value: value,
        id: id
    }

    emit('updateLink', data)
}


const colors = (id) => {
    let color = ''
    
    switch(id) {
        case 3:
            color = 'success'
            break
        case 4:
            color = 'error'
            break
        case 5:
            color = 'warning'
            break
        case 6:
            color = 'warning'
            break
    }

    return color
} 

</script>

<template>
    <section>
        <VCard >
            <template v-slot:title>
                <span class="text-uppercase">{{ title }}</span>
            </template>
            <template v-slot:subtitle>
                <div class="d-block">
                    <strong>Tienda: </strong>
                    {{ store.toUpperCase() }}
                </div>
                <div class="d-block">
                    <strong>Status: </strong>
                    <VChip label :color="colors(state.id)" >
                        {{ state.name }}
                    </VChip>
                </div>
            </template>
            <template #append class="d-flex align-end">
                <div class="d-flex align-end me-n2">
                    <VBtn
                        v-if="$can('editar','servicios') && state.id === 5"
                        icon
                        color="default"
                        size="x-small"
                        variant="plain"
                        @click="activeService(id)">
                        <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Activar
                        </VTooltip>
                        <VIcon
                            :size="22"
                            icon="tabler-check"/>
                    </VBtn>
                    <VBtn
                        v-if="$can('editar','servicios')"
                        icon
                        color="default"
                        size="x-small"
                        variant="plain"
                        :disabled="state.id === 5"
                        @click="editService(id)">
                        <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Editar
                        </VTooltip>
                        <VIcon
                            :size="22"
                            icon="tabler-edit"/>
                    </VBtn>
                    <VBtn
                        v-if="$can('eliminar','servicios')"
                        icon
                        color="default"
                        size="x-small"
                        variant="plain"
                        :disabled="state.id === 5"
                        @click="deleteService(id)">
                        <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Eliminar
                        </VTooltip>
                        <VIcon
                            :size="22"
                            icon="tabler-trash"/>
                    </VBtn>
                </div>
            </template>
            <VCardText>
                <VRow>
                    <VCol cols="12" md="5">
                        <p class="mb-0 mt-3 font-weight-semibold" v-if="price !== null">
                            {{ (parseFloat(price)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}
                        </p>
                        <span v-else>
                            <VIcon
                                size="20"
                                icon="tabler-alarm-filled"
                                class="me-1"
                            />
                        </span>
                    </VCol>
                    <VCol cols="12" md="7">
                        <VRow class="text-end pt-2">
                            <VCol :cols="rol !== 'Proveedor' ? '' : 12" class="text-center">
                                <VBtn
                                    @click="show(id)"
                                    icon
                                    variant="text"
                                    color="default"
                                    size="x-small">
                                    <VTooltip
                                        open-on-focus
                                        location="top"
                                        activator="parent">
                                        Ver
                                    </VTooltip>
                                    <VIcon
                                        size="28"
                                        icon="tabler-eye"
                                        class="me-1"
                                    />
                                </VBtn>                        
                            </VCol>
                            <VCol v-if="rol !== 'Proveedor'">
                                <span v-if="id">
                                    <VBtn
                                        @click="updateLink('archived', id)"
                                        icon
                                        variant="text"
                                        color="default"
                                        size="x-small">
                                        <VTooltip
                                            open-on-focus
                                            location="top"
                                            activator="parent">
                                            Archivar
                                        </VTooltip>
                                        <VIcon
                                            size="28"
                                            icon="tabler-building-store"
                                            class="me-1"
                                            :color="archived === 1 ? 'warning' : 'default'"
                                        />
                                    </VBtn>
                                </span>
                                <span v-else>
                                    <VIcon
                                        size="28"
                                        icon="tabler-building-store"
                                        class="me-1"
                                    />
                                </span>
                            </VCol>
                            <VCol v-if="rol !== 'Proveedor'">
                                <span v-if="id">
                                    <VBtn
                                        @click="updateLink('favourite', id)"
                                        icon
                                        variant="text"
                                        color="default"
                                        size="x-small">
                                        <VTooltip
                                            open-on-focus
                                            location="top"
                                            activator="parent">
                                            Favorito
                                        </VTooltip>
                                        <VIcon
                                            size="28"
                                            icon="tabler-star-filled"
                                            class="me-1"
                                            :color="favourite === 1 ? 'info' : 'default'"
                                        />
                                    </VBtn>
                                </span>
                                <span v-else>
                                    <VIcon
                                        size="28"
                                        icon="tabler-star-filled"
                                        class="me-1"
                                    />
                                </span>
                            </VCol>
                            <VCol v-if="discarded !== null && rol !== 'Proveedor'">
                                <span v-if="id">
                                    <VBtn
                                        @click="updateLink('discarded', id)"
                                        icon
                                        variant="text"
                                        color="default"
                                        size="x-small">
                                        <VTooltip
                                            open-on-focus
                                            location="top"
                                            activator="parent">
                                            Descartar
                                        </VTooltip>
                                        <VIcon
                                            size="28"
                                            icon="mdi-cart-remove"
                                            class="me-1"
                                            :color="discarded === 1 ? 'error' : 'default'"
                                        />
                                    </VBtn>
                                </span>
                                <span v-else>
                                    <VIcon
                                        size="28"
                                        icon="tabler-trash"
                                        class="me-1"
                                    />
                                </span>
                            </VCol>
                        </VRow>
                        <v-divider></v-divider>
                    </VCol>
                    <VCol cols="12" md="5">
                        <VImg
                            :height="220"
                            :width="220"
                            :src="image"
                            class="mx-auto border-img"
                            cover
                        />
                    </VCol>
                    <VCol cols="12" md="7">
                        <div>Envios: 
                            <span v-if="selling_price >= 0">{{ selling_price }}</span>
                            <span v-else>
                                <VIcon
                                    size="20"
                                    icon="tabler-alarm-filled"
                                    class="me-1"
                                />
                            </span>
                        </div>
                        <div>Ventas:
                            <span v-if="sales >= 0">COP {{ formatNumber(sales) ?? '0.00' }}</span>
                            <span v-else>
                                <VIcon
                                    size="20"
                                    icon="tabler-alarm-filled"
                                    class="me-1"
                                />
                            </span>
                        </div>
                        <div>Comentarios:
                            <span v-if="comments >= 0">{{ comments }}</span>
                            <span v-else>
                                <VIcon
                                    size="20"
                                    icon="tabler-alarm-filled"
                                    class="me-1"
                                />
                            </span>
                        </div>
                        <div>Favoritos:
                            <span v-if="favourite >= 0">{{ favourite }}</span>
                            <span v-else>
                                <VIcon
                                    size="20"
                                    icon="tabler-alarm-filled"
                                    class="me-1"
                                />
                            </span>
                        </div>  
                        <div class="d-flex">Valoración:
                            <VRating
                                class="ms-1"
                                v-model="rating"
                                half-increments
                                readonly
                                density="compact"
                                color="error"
                                active-color="error"
                                size="small"
                            />
                        </div>
                        <div>Likes:
                            <span v-if="likes >= 0">{{ likes }}</span>
                            <span v-else>
                                <VIcon
                                    size="20"
                                    icon="tabler-alarm-filled"
                                    class="me-1"
                                />
                            </span>
                        </div>
                        <div style="height: 150px;">Categorías:
                            <span v-for="category in categories">
                                <VChip
                                    class="me-4"
                                    label
                                    size="x-small"
                                    color="secondary"
                                    >
                                    {{ category }}
                                </VChip>
                            </span>
                        </div>                                  
                        <VAlert class="text-center mt-5">
                            <VRow>
                                <VCol cols="4">
                                    <VBtn
                                        @click="download(image)"
                                        icon
                                        variant="text"
                                        color="default"
                                        size="x-small">
                                        <VTooltip
                                            open-on-focus
                                            location="top"
                                            activator="parent">
                                            Descargar imagen
                                        </VTooltip>
                                        <VIcon
                                            size="20"
                                            icon="tabler-photo"
                                            class="me-1"
                                        />
                                    </VBtn>
                                </VCol>
                                <VCol cols="4">
                                    <VBtn
                                        @click="open(originalLink)"
                                        icon
                                        variant="text"
                                        color="default"
                                        size="x-small">
                                        <VTooltip
                                            open-on-focus
                                            location="top"
                                            activator="parent">
                                            Abrir Link
                                        </VTooltip>
                                        <VIcon
                                            size="20"
                                            icon="tabler-shopping-cart"
                                            class="me-1"
                                        />
                                    </VBtn>
                                </VCol>
                                <VCol cols="4">
                                    <VBtn
                                        @click="copy(originalLink)"
                                        icon
                                        variant="text"
                                        color="default"
                                        size="x-small">
                                        <VTooltip
                                            open-on-focus
                                            location="top"
                                            activator="parent">
                                            Copiar
                                        </VTooltip>
                                        <VIcon
                                            size="20"
                                            icon="tabler-layers-subtract"
                                            class="me-1"
                                        />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VAlert>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>      
    </section>
</template>

<style lang="scss" scoped>
    .title-truncate, .subtitle-truncate {
        white-space: break-spaces !important;
    }

    .border-img {
        border-radius: 16px !important;
        border: 1px solid #D9D9D9;
    }

    .header {
        height: 100px;
        display: block;

        @media (max-width: 767px) {
            height: auto;
        }
    }
</style>

