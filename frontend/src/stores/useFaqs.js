import { defineStore } from 'pinia'
import Faqs from '@/api/faqs'

export const useFaqsStores = defineStore('faqs', {
    state: () => ({
        faqs: {},
        loading: false,
        last_page: 1,
        faqsTotalCount: 6
    }),
    getters:{
        getFaqs(){
            return this.faqs
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchFaqs(params) {
            this.setLoading(true)
            
            return Faqs.get(params)
                .then((response) => {
                    this.faqs = response.data.faqs.data
                    this.last_page = response.data.faqs.last_page
                    this.faqsTotalCount = response.data.faqsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addFaq(data) {
            this.setLoading(true)

            return Faqs.create(data)
                .then((response) => {
                    this.faqs.push(response.data.faq)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateFaq(data) {
            this.setLoading(true)
            
            return Faqs.update(data)
                .then((response) => {
                    let pos = this.faqs.findIndex((item) => item.id === response.data.faq.id)
                    this.faqs[pos] = response.data.faq
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteFaq(id) {
            this.setLoading(true)

            return Faqs.delete(id)
                .then((response) => {
                    let index = this.faqs.findIndex((item) => item.id === id)
                    this.faqs.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        fetchFaqsOrder(params) {
            this.setLoading(true)
            
            return Faqs.order(params)
                .then((response) => {
                    this.faqs = response.data.faqs
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
    }
})
