import { defineStore } from 'pinia'
import Products from '@/api/products'

export const useProductsStores = defineStore('products', {
    state: () => ({
        products: {},
        loading: false,
        last_page: 1,
        productsTotalCount: 6,
        data: {}
    }),
    getters:{
        getProducts(){
            return this.products
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchProducts(params) {
            this.setLoading(true)
            
            return Products.get(params)
                .then((response) => {
                    this.products = response.data.data.products.data
                    this.last_page = response.data.data.products.last_page
                    this.productsTotalCount = response.data.data.productsTotalCount
                    this.data = response.data.data.data
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addProduct(data) {
            this.setLoading(true)

            return Products.create(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showProduct(id) {
            this.setLoading(true)

            return Products.show(id)
                .then((response) => {
                    if(response.data.success)
                        return Promise.resolve(response.data.data.product)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateProduct(data) {
            this.setLoading(true)
            
            return Products.update(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteProduct(id) {
            this.setLoading(true)

            return Products.delete(id)
                .then((response) => {
                    let index = this.products.findIndex((item) => item.id === id)
                    this.products.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        updateLink(data, id) {
            this.setLoading(true)
            
            return Products.updateLink(data, id)
                .then((response) => {
                    let pos = this.products.findIndex((item) => item.id === response.data.data.product.id)
                    this.products[pos] = response.data.data.product
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        updateState(data, id) {
            this.setLoading(true)
            
            return Products.updateState(data, id)
                .then((response) => {
                    let pos = this.products.findIndex((item) => item.id === response.data.data.product.id)
                    this.products.splice(pos, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        updateOrder(data){
            this.setLoading(true)
            
            return Products.updateOrder(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
        },
        uploadProducts(data){
            this.setLoading(true)
            
            return Products.uploadProducts(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
        }
    }
})
