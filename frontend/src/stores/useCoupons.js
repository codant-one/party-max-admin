import { defineStore } from 'pinia'
import Coupons from '@/api/coupons'

export const useCouponsStores = defineStore('coupons', {
    state: () => ({
        coupons: {},
        loading: false,
        last_page: 1,
        couponsTotalCount: 6
    }),
    getters:{
        getCoupons(){
            return this.coupons
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchCoupons(params) {
            this.setLoading(true)
            
            return Coupons.get(params)
                .then((response) => {
                    this.coupons = response.data.data.coupons.data
                    this.last_page = response.data.data.coupons.last_page
                    this.couponsTotalCount = response.data.data.couponsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addCoupon(data) {
            this.setLoading(true)

            return Coupons.create(data)
                .then((response) => {
                    this.coupons.push(response.data.data.coupon)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
        },
        showCoupon(id) {
            this.setLoading(true)

            return Coupons.show(id)
                .then((response) => {
                    if(response.data.success)
                        return Promise.resolve(response.data.data.coupon)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        deleteCoupon(id) {
            this.setLoading(true)

            return Coupons.delete(id)
                .then((response) => {
                    let index = this.coupons.findIndex((item) => item.id === id)
                    this.coupons.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
