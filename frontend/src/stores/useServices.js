import { defineStore } from 'pinia'
import Services from '@/api/services'

export const useServicesStores = defineStore('services', {
    state: () => ({
        services: {},
        loading: false,
        last_page: 1,
        servicesTotalCount: 6,
        data: {}
    }),
    getters:{
        getServices(){
            return this.services
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchServices(params) {
            this.setLoading(true)
            
            return Services.get(params)
                .then((response) => {
                    this.services = response.data.data.services.data
                    this.last_page = response.data.data.services.last_page
                    this.servicesTotalCount = response.data.data.servicesTotalCount
                    this.data = response.data.data.data
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addService(data) {
            this.setLoading(true)

            return Services.create(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showService(id) {
            this.setLoading(true)

            return Services.show(id)
                .then((response) => {
                    if(response.data.success)
                        return Promise.resolve(response.data.data.service)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateService(data) {
            this.setLoading(true)
            
            return Services.update(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteService(id) {
            this.setLoading(true)

            return Services.delete(id)
                .then((response) => {
                    let index = this.services.findIndex((item) => item.id === id)
                    this.services.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        updateLink(data, id) {
            this.setLoading(true)
            
            return Services.updateLink(data, id)
                .then((response) => {
                    let pos = this.services.findIndex((item) => item.id === response.data.data.service.id)
                    this.services[pos] = response.data.data.service
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        updateState(data, id) {
            this.setLoading(true)
            
            return Services.updateState(data, id)
                .then((response) => {
                    let pos = this.services.findIndex((item) => item.id === response.data.data.service.id)
                    this.services.splice(pos, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        updateOrder(data){
            this.setLoading(true)
            
            return Services.updateOrder(data)
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
