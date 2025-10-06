<script setup>

import { useReviewsStores } from '@/stores/reviews'
import { requiredValidator } from '@validators'
import Loader from '@/components/common/Loader.vue'
import check_circle from '@assets/icons/check-circle.svg';
import error_circle from '@assets/icons/error-circle.svg';

const reviewsStores = useReviewsStores()
const route = useRoute()

const refVForm = ref()
const isDialogVisible = ref(false)
const message = ref()
const isError = ref(false)

const client_id = ref(null)
const data = ref(null)
const isLoading = ref(true)
const product = ref(null)
const review = ref(null)
const rating = ref(null)
const comments = ref(null)
const review_id = ref(null)

const baseURL = ref(import.meta.env.VITE_APP_DOMAIN_API_URL + '/storage/')
const isMobile = /Mobi/i.test(navigator.userAgent);

watchEffect(fetchData)

async function fetchData() {
    
    if(localStorage.getItem('user_data')){
        const userData = localStorage.getItem('user_data')
        const userDataJ = JSON.parse(userData)

        client_id.value = userDataJ.client.id

        isLoading.value = true
        data.value = await reviewsStores.show_by_client({client_id: client_id.value}, route.params.id)

        product.value = data.value.product
        review.value = data.value.review

        if(review.value) {
            review_id.value = review.value.id
            rating.value = review.value.rating
            comments.value = review.value.comments
        } else {
            review_id.value = null
            rating.value = 0
            comments.value = null
        }

        isLoading.value = false
    }
}

const save = () => {

    refVForm.value?.validate().then(({ valid: isValid }) => {
        if (isValid) {
            isLoading.value = true

            let data = {
                client_id: client_id.value,
                product_id: Number(route.params.id),
                rating: rating.value,
                comments: comments.value,
                order_id: Number(route.params.orderId)
            }

            if(review_id.value) {//update
                reviewsStores.updateReview(data, review_id.value)
                    .then(response => {

                        isDialogVisible.value = true
                        message.value = 'Review actualizado exitosamente'

                        fetchData()

                        setTimeout(() => {
                            isDialogVisible.value = false
                            message.value = ''
                            isError.value = false
                        }, 3000)

                        isLoading.value = false
                    }).catch(err => {

                        if(err.message === 'error'){
                            isDialogVisible.value = true
                            message.value = err.errors
                            isError.value = true
                        } else {
                            isDialogVisible.value = true
                            isError.value = true
                            message.value = 'Se ha producido un error...! (Server Error)'
                        }                    

                        setTimeout(() => {
                            isDialogVisible.value = false
                            message.value = ''
                            isError.value = false
                        }, 3000)

                        // console.error(err.message)
                        isLoading.value = false
                    })
            } else {//add
                reviewsStores.addReview(data)
                    .then(response => {

                        isDialogVisible.value = true
                        message.value = 'Review creado exitosamente'

                        fetchData()

                        setTimeout(() => {
                            isDialogVisible.value = false
                            message.value = ''
                            isError.value = false
                        }, 3000)               

                        isLoading.value = false
                    }).catch(err => {

                        if(err.message === 'error'){
                            isDialogVisible.value = true
                            message.value = err.errors
                            isError.value = true
                        } else {
                            isDialogVisible.value = true
                            isError.value = true
                            message.value = 'Se ha producido un error...! (Server Error)'
                        }                    

                        setTimeout(() => {
                            isDialogVisible.value = false
                            message.value = ''
                            isError.value = false
                        }, 3000)

                        // console.error(err.message)
                        isLoading.value = false
                    })
            }
        }
    })
}

const remove = () => {

    isLoading.value = true
    reviewsStores.deleteReview({ ids: [review_id.value], product_id: route.params.id })
        .then(response => {

            isDialogVisible.value = true
            message.value = 'Review eliminado exitosamente'

            fetchData()

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
            }, 3000)               

            isLoading.value = false
        }).catch(err => {

            if(err.message === 'error'){
                isDialogVisible.value = true
                message.value = err.errors
                isError.value = true
            } else {
                isDialogVisible.value = true
                isError.value = true
                message.value = 'Se ha producido un error...! (Server Error)'
            }                    

            setTimeout(() => {
                isDialogVisible.value = false
                message.value = ''
                isError.value = false
            }, 3000)

            // console.error(err.message)
            isLoading.value = false
        })

}

</script>

<template>
    <Loader :isLoading="isLoading"/>
    <VContainer class="my-1 my-md-10 container-dashboard" v-if="data">
        <VForm
            ref="refVForm"
            @submit.prevent="save"
            > 
            <VCard class="card-profile mb-5 p-0 pt-5 mx-auto">
                <VCardText class="px-5 px-md-10 d-flex flex-column justify-content-center align-center text-center">
                    <VImg :src="baseURL + product.image" class="image-product"/>
                    <span class="text-question tw-text-primary my-5">¿Qué te pareció tu producto?</span>
                    <span class="name-product tw-text-tertiary">{{ product.name}}</span>
                </VCardText>
                <VCardText class="p-rating justify-content-center align-center text-center">
                    <VRating
                        half-increments
                        :length="5"
                        :size="isMobile ? 50 : 'x-large'"
                        v-model="rating"
                        hover
                        color="yellow-darken-2"
                        active-color="yellow-darken-2"
                    />
                </VCardText>
                <VCardText class="p-rating d-flex justify-content-center align-center text-center">
                    <span class="ml-5 mb-10">Malo</span>
                    <VSpacer />
                    <span class="mr-5 mb-10">Excelente</span>
                </VCardText>
            </VCard>

            <VCard class="card-profile my-5 p-0 pt-5 mx-auto">
                <VCardText class="px-5 px-md-10 d-flex flex-column justify-content-center align-center text-center">
                    <span class="text-question tw-text-primary my-3">Cuéntanos más acerca de tu producto</span>
                    <span class="text-status mb-3">Opcional</span>
                    <VTextarea
                        v-model="comments"
                        class="w-100"
                        rows="5"
                        label="Comentario"
                        placeholder="Dejanos saber tus comentarios."
                        variant="outlined"
                        counter
                        :rules="[requiredValidator]"
                    />
                </VCardText>
            </VCard>
            <VCardText class="px-5 px-md-10 d-flex flex-column justify-content-center align-center text-center pt-0">
                <VBtn class="btn-save tw-bg-primary tw-text-white" type="submit">
                    Guardar
                </VBtn>
                <span 
                    v-if="review_id"
                    class="text-status my-3 hover:tw-text-primary"
                    @click="remove">
                    Eliminar opinión
                </span>
            </VCardText>
        </VForm>
    </VContainer>
     <!--PopUp Message-->
     <VDialog v-model="isDialogVisible" >
            <VCard
                class="px-10 py-14 pb-2 pb-md-4 no-shadown card-register d-block text-center mx-auto">
                <VImg :width="isMobile ? '120' : '180'" :src="isError ? error_circle : check_circle" class="mx-auto"/>
                <VCardText class="text-message mt-10 mb-5">
                    {{ message }}
                </VCardText>
            </VCard>
        </VDialog>
</template>

<style scoped>
    .text-message {
        color:  #FF0090;
        text-align: center;
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: 30px; 
        padding: 0 80px !important;
    }

    .card-register {
        width: 500px;
        border-radius: 32px!important;
    }

    .v-textarea::v-deep(.v-field-label) {
        top: 10% !important;
        font-size: 14px !important;
    }

    .v-textarea::v-deep(.v-field) { 
        border-radius: 8px !important;
    }

    .container-dashboard {
        padding: 2% 15%;
    }

    .card-profile {
        width: 600px;
        border-radius: 16px;
        box-shadow: none;
    }

    .p-rating {
        padding: 0 135px !important;
    }

    .text-question {
        font-size: 36px;
        font-style: normal;
        font-weight: 700;
        line-height: 40px;
    } 

    .image-product {
        width: 173.96px;
        height: 173.96px;
        max-width: 173.96px;
        border-radius: 16px;
        border: 1px solid var(--Light-Cyan-1, #E2F8FC);
    }

    .text-status {
        font-size: 16px;
        font-style: normal;
        line-height: 16px;
        color: #0A1B33;
    }

    .name-product {
        font-size: 24px;
        font-style: normal;
        font-weight: 400;
        line-height: 16px;
    }

    .btn-save {
        border-radius: 32px;
        border: none;
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 14px;
        box-shadow: none;
        height: 60px;
        width: 150px;
    }

    .btn-save:hover {
        background: var(--Magenta-Party-500, #FF27B3);
        box-shadow: 0px 0px 24px 0px #FF27B3;
    }

    .v-rating::v-deep(.v-icon--size-default) {
        font-size: 50px;
    }

    @media only screen and (max-width: 767px)  {
        .container-dashboard {
            padding: 0 5%;
        }

        .btn-save {
            width: 100%;
        }

        .card-register {
            padding: 20px;
            width: auto;
        }

        .text-message {
            padding: 0 30px !important;
            font-size: 18px;
        }

        .card-profile {
            width: 340px;
        }

        .text-question {
            font-size: 30px;
            line-height: 30px;
        }

        .name-product {
            font-size: 24px;
            line-height: 30px;
        }

        .p-rating {
            padding: 0 35px !important;
        }
    }
</style>