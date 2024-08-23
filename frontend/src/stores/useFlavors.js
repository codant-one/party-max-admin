import { defineStore } from 'pinia'
import Flavors from '@/api/flavors'

export const useFlavorsStores = defineStore('flavors', {
    state: () => ({
        flavors: {},
        loading: false,
        last_page: 1,
        flavorsTotalCount: 6
    }),
    getters:{
        getFlavors(){
            return this.flavors
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchFlavors(params) {
            this.setLoading(true)
            
            return Flavors.get(params)
                .then((response) => {
                    this.flavors = response.data.data.flavors.data
                    this.last_page = response.data.data.flavors.last_page
                    this.flavorsTotalCount = response.data.data.flavorsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addFlavor(data) {
            this.setLoading(true)

            return Flavors.create(data)
                .then((response) => {
                    this.flavors.push(response.data.data.flavor)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateFlavor(data) {
            this.setLoading(true)
            
            return Flavors.update(data)
                .then((response) => {
                    let pos = this.flavors.findIndex((item) => item.id === response.data.data.flavor.id)
                    this.flavors[pos] = response.data.data.flavor
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteFlavor(id) {
            this.setLoading(true)

            return Flavors.delete(id)
                .then((response) => {
                    let index = this.flavors.findIndex((item) => item.id === id)
                    this.flavors.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
