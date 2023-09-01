<script setup>

import { useUserStatsStores } from '@/stores/useUserStats'
import sliderBar1 from '@images/illustrations/sidebar-pic-1.png'

const userStatsStores = useUserStatsStores()

const userDataJ = ref('')
const name = ref('')
const statistics = ref()
const  data = ref([])

watchEffect(fetchData)

async function fetchData() {

    const userData = localStorage.getItem('user_data')
    
    userDataJ.value = JSON.parse(userData)
    name.value = userDataJ.value.name + " " + userDataJ.value.last_name

    statistics.value = await userStatsStores.statistics()

    if(statistics.value !== null) {
        data.value = [
            { number: statistics.value.sales, text: 'Ventas' , path: 'dashboard-collaboration-sales' },
            { number: statistics.value.ideas, text: 'Ideas' , path: 'dashboard-collaboration-ideas' },
            { number: statistics.value.links, text: 'Enlaces' , path: 'dashboard-collaboration-links' },
            { number: '0', text: 'Com. Total($)', path: 'dashboard-collaboration-links' }
        ]
    } else {
        data.value = [
            { number: '0',  text: 'Ventas' },
            { number: '0', text: 'Ideas' },
            { number: '0', text: 'Enlaces' },
            { number: '0', text: 'Com. Total($)' }
        ]
    }
}

</script>

<template>
    <VCard color="primary">
        <VCardText>
            <VRow>
                <VCol cols="12">
                <h6 class="text-h6 text-white mb-1">
                    Análisis de Estadísticas
                </h6>
                <p class="text-sm mb-0">
                    {{ name }}
                </p>
                </VCol>

                <VCol
                    cols="12"
                    sm="6"
                    order="2"
                    order-sm="1"
                >
                <VRow>
                    <VCol
                        v-for="d in data"
                        :key="d.number"
                        cols="6"
                        class="text-no-wrap"
                    >
                    
                    <VChip
                        label
                        class="me-2 p-0"
                        >
                        <v-btn 
                            color="white"
                            variant="text"
                            :to="{ name: d.path }">
                            {{ d.number }} 
                        </v-btn>
                    </VChip>
                        <span>{{ d.text }}</span>
                    </VCol>
                </VRow>
                </VCol>
                <VCol
                    cols="12"
                    sm="6"
                    order="1"
                    order-sm="2"
                    class="position-relative text-center"
                >
                <img
                    :src="sliderBar1"
                    class="card-website-analytics-img"
                >
                </VCol>
            </VRow>
        </VCardText>
    </VCard>
</template>

<style lang="scss">
    .p-0 {
        padding: 0 !important;
    }

    .card-website-analytics-img {
        block-size: 160px;
    }

    @media screen and (min-width: 600px) {
        .card-website-analytics-img {
            position: absolute;
            margin: auto;
            inset-block-end: 40px;
            inset-block-start: -1rem;
            inset-inline-end: 1rem;
        }
    }
</style>