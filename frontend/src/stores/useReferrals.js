import { defineStore } from 'pinia'
import Referrals from '@/api/referrals'

export const useReferralsStores = defineStore('referrals', {
    state: () => ({
        referrals: {},
        loading: false,
        last_page: 1,
        referralsTotalCount: 6
    }),
    getters:{
        getReferrals(){
            return this.referrals
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchProducts(params) {
            this.setLoading(true)
            
            return Referrals.products(params)
                .then((response) => {
                    this.referrals = response.data.data.referrals.data
                    this.last_page = response.data.data.referrals.last_page
                    this.referralsTotalCount = response.data.data.referralsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        fetchReferrals(params) {
            this.setLoading(true)
            
            return Referrals.get(params)
                .then((response) => {
                    this.referrals = response.data.data.referrals.data
                    this.last_page = response.data.data.referrals.last_page
                    this.referralsTotalCount = response.data.data.referralsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateProduct(data) {
            this.setLoading(true)
            
            return Referrals.update(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        userDetails(data) {
            this.setLoading(true)
            
            return Referrals.user(data)
                .then((response) => {
                    return Promise.resolve(response.data.data)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        upload(data) {
            this.setLoading(true)

            return Referrals.upload(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
    }
})
