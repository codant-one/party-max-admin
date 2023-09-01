import { defineStore } from 'pinia'
import Stores from '@/api/stores'

export const useAffliatesStores = defineStore('affiliates', {
    state: () => ({
        affiliates: {},
        afiliado: {},
        loading: false,
        last_page: 1,
        affiliatesTotalCount: 6
    }),
    getters:{
        getAffiliates(){
            return this.affiliates
        },
        getafiliado(){
            return this.afiliado
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchAffiliates(params) {
            this.setLoading(true)
            
            return Stores.get(params)
                .then((response) => {
                    this.affiliates = response.data.data.stores.data
                    this.last_page = response.data.data.stores.last_page
                    this.affiliatesTotalCount = response.data.data.storesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
        },
        afilliateById(id) {
            this.setLoading(true)
            
            return Stores.getById(id)
                .then((response) => {
                    this.afiliado = response.data.data.store
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
        },
        
    }
})