<script setup>

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    is_affiliated: {
        type: Boolean,
        required: false
    },
    message: {
        type: String,
        required: false
    }
})

const emit = defineEmits([
    'alert',
    'copy',
    'open',
    'download',
    'updateLink'
])

const id = ref('')
const favourite = ref('')
const archived = ref('')
const discarded = ref(null)
const title = ref('')
const image = ref('')
const description = ref(null)
const price = ref('')
const currency = ref('')
const originalLink = ref('')
const rating = ref(null)
const comments = ref(null)
const sales =  ref(null)
const selling_price = ref(null)

const is_affiliated = ref(false)
const message = ref('')

watchEffect(() => {

    if (!(Object.entries(props.product).length === 0) && props.product.constructor === Object) {
        id.value = props.product.id
        favourite.value = props.product.favourite ?? -1
        archived.value = props.product.archived ?? null
        discarded.value = props.product.discarded ?? null
        title.value = props.product.title ?? null
        image.value = props.product.image ?? null
        description.value = props.product.description ?? null
        price.value = props.product.price ?? null
        currency.value = props.product.currency ?? null
        originalLink.value = props.product.originalLink ?? null
        rating.value = props.product.rating ?? -1
        comments.value = props.product.comments ?? -1
        sales.value = props.product.sales ?? -1
        selling_price.value = props.product.selling_price ?? -1
    }

    is_affiliated.value = props.is_affiliated ?? false
    message.value = props.message ?? ''
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

</script>

<template>
    <section>
        <VCard>
            <VCardItem class="header">
                <VCardTitle class="text-h6 title-truncate"> {{ title }} </VCardTitle>
                <VCardSubtitle v-if="description" class="subtitle-truncate mb-3"> {{ description }} </VCardSubtitle>
            </VCardItem>
            <VCardText>
                <VRow>
                    <VCol cols="12" md="6">
                        <p class="mb-0 mt-3 font-weight-semibold" v-if="price !== null">
                            {{ price }} {{ currency }}
                        </p>
                        <span v-else>
                            <VIcon
                                size="20"
                                icon="tabler-alarm-filled"
                                class="me-1"
                            />
                        </span>
                    </VCol>
                <VCol cols="12" md="6">
                    <VRow class="text-center pt-2">
                        <VCol>
                            <span v-if="id">
                                <VTooltip
                                    open-on-focus
                                    location="top"
                                    activator="parent">
                                    Numeros de ventas
                                </VTooltip>
                                <VIcon
                                    size="28"
                                    icon="tabler-coin-euro"
                                    class="me-1"
                                />
                             </span>
                            <span v-else>
                                <VIcon
                                    size="28"
                                    icon="tabler-coin-euro"
                                    class="me-1"
                                />
                            </span>
                        </VCol>
                        <VCol>
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
                        <VCol>
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
                        <VCol v-if="discarded !== null">
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
                                        icon="tabler-trash"
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
                <VCol cols="12" md="6">
                    <VImg
                        :height="200"
                        :src="image"
                        class="mx-auto"
                    />
                </VCol>
                <VCol cols="12" md="6">
                    <div>Envio: 
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
                        <span v-if="sales >= 0">{{ sales }}</span>
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
                    <div>Valoracion:
                        <span v-if="rating >= 0">{{ rating }}</span>
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
                <VCol cols="12" md="6" v-if="is_affiliated">
                    <VCheckbox
                        v-model="is_affiliated"
                        class="checkbox-opacity text-center"
                        label="Es afiliado"
                        readonly
                        />
                </VCol>
                <VCol cols="12"  md="12"  sm="12" v-if="message">
                    <p style="font-weight: bold;">
                        {{ message }}
                    </p>
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

    .header {
        height: 150px;
        display: block;

        @media (max-width: 767px) {
            height: auto;
        }
    }
</style>

