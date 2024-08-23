import { defineStore } from 'pinia'
import CakeTypes from '@/api/cake-types'

export const useCakeTypesStores = defineStore('cake-types', {
    state: () => ({
        cakeTypes: {},
        loading: false,
        last_page: 1,
        cakeTypesTotalCount: 6
    }),
    getters:{
        getCakeTypes(){
            return this.cakeTypes
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchCakeTypes(params) {
            this.setLoading(true)
            
            return CakeTypes.get(params)
                .then((response) => {
                    this.cakeTypes = response.data.data.cakeTypes.data
                    this.last_page = response.data.data.cakeTypes.last_page
                    this.cakeTypesTotalCount = response.data.data.cakeTypesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addCakeType(data) {
            this.setLoading(true)

            return CakeTypes.create(data)
                .then((response) => {
                    this.cakeTypes.push(response.data.data.cakeType)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateCakeType(data) {
            this.setLoading(true)
            
            return CakeTypes.update(data)
                .then((response) => {
                    let pos = this.cakeTypes.findIndex((item) => item.id === response.data.data.cakeType.id)
                    this.cakeTypes[pos] = response.data.data.cakeType
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteCakeType(id) {
            this.setLoading(true)

            return CakeTypes.delete(id)
                .then((response) => {
                    let index = this.cakeTypes.findIndex((item) => item.id === id)
                    this.cakeTypes.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
