import { defineStore } from 'pinia'
import Banners from '@/api/banners'

export const useBannersStores = defineStore('banners', {
    state: () => ({
        banners: {},
        loading: false,
        last_page: 1,
        bannersTotalCount: 6
    }),
    getters:{
        getBanners(){
            return this.banners
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchBanners(params) {
            this.setLoading(true)
            
            return Banners.get(params)
                .then((response) => {
                    this.banners = response.data.data.banners.data
                    this.last_page = response.data.data.banners.last_page
                    this.bannersTotalCount = response.data.data.bannersTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addBanner(data) {
            this.setLoading(true)

            return Banners.create(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showBanner(id) {
            this.setLoading(true)

            return Banners.show(id)
                .then((response) => {
                    if(response.data.success)
                    return Promise.resolve(response.data.data.banner)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateBanner(data) {
            this.setLoading(true)
            
            return Banners.update(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteBanner(id) {
            this.setLoading(true)

            return Banners.delete(id)
                .then((response) => {
                    let index = this.banners.findIndex((item) => item.id === id)
                    this.banners.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
