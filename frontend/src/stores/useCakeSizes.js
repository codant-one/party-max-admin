import { defineStore } from 'pinia'
import CakeSizes from '@/api/cake-sizes'

export const useCakeSizesStores = defineStore('cake-sizes', {
    state: () => ({
        cakeSizes: {},
        loading: false,
        last_page: 1,
        cakeSizesTotalCount: 6
    }),
    getters:{
        getCakeSizes(){
            return this.cakeSizes
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchCakeSizes(params) {
            this.setLoading(true)
            
            return CakeSizes.get(params)
                .then((response) => {
                    this.cakeSizes = response.data.data.cakeSizes.data
                    this.last_page = response.data.data.cakeSizes.last_page
                    this.cakeSizesTotalCount = response.data.data.cakeSizesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addCakeSize(data) {
            this.setLoading(true)

            return CakeSizes.create(data)
                .then((response) => {
                    this.cakeSizes.push(response.data.data.cakeSize)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateCakeSize(data) {
            this.setLoading(true)
            
            return CakeSizes.update(data)
                .then((response) => {
                    let pos = this.cakeSizes.findIndex((item) => item.id === response.data.data.cakeSize.id)
                    this.cakeSizes[pos] = response.data.data.cakeSize
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteCakeSize(id) {
            this.setLoading(true)

            return CakeSizes.delete(id)
                .then((response) => {
                    let index = this.cakeSizes.findIndex((item) => item.id === id)
                    this.cakeSizes.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
