import { defineStore } from 'pinia'
import HomeImages from '@/api/home-image'

export const useHomeImagesStores = defineStore('home-images', {
    state: () => ({
        homeImages: {},
        loading: false,
        last_page: 1,
        homeImagesTotalCount: 6
    }),
    getters:{
        getHomeImages(){
            return this.homeImages
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
                    this.homeImages = response.data.data.homeImages.data
                    this.last_page = response.data.data.homeImages.last_page
                    this.homeImagesTotalCount = response.data.data.homeImagesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addHomeImage(data) {
            this.setLoading(true)

            return HomeImages.create(data)
                .then((response) => {
                    this.homeImages.push(response.data.data.homeImages)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateHomeImage(data) {
            this.setLoading(true)
            
            return HomeImages.update(data)
                .then((response) => {
                    let pos = this.homeImages.findIndex((item) => item.id === response.data.data.homeImage.id)
                    this.homeImages[pos] = response.data.data.homeImage
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteHomeImage(id) {
            this.setLoading(true)

            return HomeImages.delete(id)
                .then((response) => {
                    let index = this.homeImages.findIndex((item) => item.id === id)
                    this.homeImages.splice(index, 1)
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
                    this.homeImages = response.data.data.homeImages
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateOrder(data){
            this.setLoading(true)
            
            return HomeImages.updateOrder(data)
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
