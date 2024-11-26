import { defineStore } from 'pinia'
import HomeImages from '@/api/homeimage'

export const useHomeImagesStores = defineStore('homeimages', {
    state: () => ({
        homeimages: {},
        loading: false,
        last_page: 1,
        homeimagesTotalCount: 6
    }),
    getters:{
        getHomeImages(){
            return this.homeimages
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchHomeImages(params) {
            this.setLoading(true)
            
            return HomeImages.get(params)
                .then((response) => {
                    this.homeimages = response.data.data.homeimages.data
                    this.last_page = response.data.data.homeimages.last_page
                    this.homeimagesTotalCount = response.data.data.homeimagesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addHomeImages(data) {
            this.setLoading(true)

            return HomeImages.create(data)
                .then((response) => {
                    this.homeimages.push(response.data.data.homeimages)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showHomeImage(id) {
            this.setLoading(true)

            return HomeImages.show(id)
                .then((response) => {
                    if(response.data.success)
                    return Promise.resolve(response.data.data.homeimage)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        fetchHomeImagesOrder(params) {
            this.setLoading(true)
            
            return HomeImages.order(params)
                .then((response) => {
                    this.homeimages = response.data.data.homeimages
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
    }
})
