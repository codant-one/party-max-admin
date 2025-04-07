import { defineStore } from 'pinia'
import Quotes from '@/api/quotes'

export const useQuotesStores = defineStore('quotes', {
    state: () => ({
        quotes: {},
        loading: false,
        last_page: 1,
        quotesTotalCount: 6,
        payments: {}
    }),
    getters:{
        getQuotes(){
            return this.quotes
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchQuotes(params) {
            this.setLoading(true)
            
            return Quotes.get(params)
                .then((response) => {
                    this.quotes = response.data.data.quotes.data
                    this.last_page = response.data.data.quotes.last_page
                    this.quotesTotalCount = response.data.data.quotesTotalCount
                    this.payments = response.data.data.payments
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        deleteQuote(id) {
            this.setLoading(true)

            return Quotes.delete(id)
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
