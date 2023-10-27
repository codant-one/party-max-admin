import { defineStore } from 'pinia'
import Categories from '@/api/faq-categories'

export const useCategoriesStores = defineStore('faq-categories', {
    state: () => ({
        categories: {},
        loading: false,
        last_page: 1,
        categoriesTotalCount: 6
    }),
    getters:{
        getCategories(){
            return this.categories
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchCategories(params) {
            this.setLoading(true)
            
            return Categories.get(params)
                .then((response) => {
                    this.categories = response.data.data.categories.data
                    this.last_page = response.data.data.categories.last_page
                    this.categoriesTotalCount = response.data.data.categoriesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addCategory(data) {
            this.setLoading(true)

            return Categories.create(data)
                .then((response) => {
                    this.categories.push(response.data.data.category)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showCategory(id) {
            this.setLoading(true)

            return Categories.show(id)
                .then((response) => {
                    if(response.data.success)
                        return Promise.resolve(response.data.data.category)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateCategory(data) {
            this.setLoading(true)
            
            return Categories.update(data)
                .then((response) => {
                    let pos = this.categories.findIndex((item) => item.id === response.data.data.category.id)
                    this.categories[pos] = response.data.data.category
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteCategory(id) {
            this.setLoading(true)

            return Categories.delete(id)
                .then((response) => {
                    let index = this.categories.findIndex((item) => item.id === id)
                    this.categories.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        allFaqs() {
            this.setLoading(true)
            
            return Categories.faqs()
                .then((response) => {
                    return Promise.resolve(response.data.data)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
    }
})
