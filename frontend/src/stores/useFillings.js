import { defineStore } from 'pinia'
import Fillings from '@/api/fillings'

export const useFillingsStores = defineStore('fillings', {
    state: () => ({
        fillings: {},
        loading: false,
        last_page: 1,
        fillingsTotalCount: 6
    }),
    getters:{
        getFillings(){
            return this.fillings
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchFillings(params) {
            this.setLoading(true)
            
            return Fillings.get(params)
                .then((response) => {
                    this.fillings = response.data.data.fillings.data
                    this.last_page = response.data.data.fillings.last_page
                    this.fillingsTotalCount = response.data.data.fillingsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addFilling(data) {
            this.setLoading(true)

            return Fillings.create(data)
                .then((response) => {
                    this.fillings.push(response.data.data.filling)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateFilling(data) {
            this.setLoading(true)
            
            return Fillings.update(data)
                .then((response) => {
                    let pos = this.fillings.findIndex((item) => item.id === response.data.data.filling.id)
                    this.fillings[pos] = response.data.data.filling
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteFilling(id) {
            this.setLoading(true)

            return Fillings.delete(id)
                .then((response) => {
                    let index = this.fillings.findIndex((item) => item.id === id)
                    this.fillings.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
