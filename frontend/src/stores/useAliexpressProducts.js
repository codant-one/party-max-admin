import { defineStore } from 'pinia'
import AliexpressProducts from '@/api/aliexpressProduct'

export const useAliexpressProductsStores = defineStore('roles', {
    state: () => ({
        aliexpressproducts: {},
        loading: false,
        last_page: 1,
        aliexpressTotalCount: 6
    }),
    getters:{
        getAliexpressProducts(){
            return this.aliexpressproducts
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchAliexpressProducts(params) {
            this.setLoading(true)
            
            return AliexpressProducts.get(params)
                .then((response) => {
                    this.aliexpressproducts = response.data.data.aliexpressProduct.data
                    this.last_page = response.data.data.aliexpressProduct.last_page
                    this.aliexpressTotalCount = response.data.data.aliexpressTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addAliexpressProduct(data) {
            this.setLoading(true)

            return AliexpressProducts.create(data)
                .then((response) => {
                    this.aliexpressproducts.push(response.data.data.aliexpressProduct)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateAliexpressProduct(data, id) {
            this.setLoading(true)
            
            return AliexpressProducts.update(data, id)
                .then((response) => {
                    let pos = this.aliexpressproducts.findIndex((item) => item.id === response.data.data.aliexpressProduct.id)
                    this.aliexpressproducts[pos] = response.data.data.aliexpressProduct
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteAliexpressProduct(id) {
            this.setLoading(true)

            return AliexpressProducts.delete(id)
                .then((response) => {
                    let index = this.aliexpressproducts.findIndex((item) => item.id === id)
                    this.aliexpressproducts.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
