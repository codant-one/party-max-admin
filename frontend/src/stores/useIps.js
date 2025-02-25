import { defineStore } from 'pinia'
import Ips from '@/api/ips'

export const useIpsStores = defineStore('ips', {
    state: () => ({
        ips: {},
        loading: false,
        last_page: 1,
        ipsTotalCount: 6
    }),
    getters:{
        getIps(){
            return this.ips
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchIps(params) {
            this.setLoading(true)
            
            return Ips.get(params)
                .then((response) => {
                    this.ips = response.data.data.ips.data
                    this.last_page = response.data.data.ips.last_page
                    this.ipsTotalCount = response.data.data.ipsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showIp(id) {
            this.setLoading(true)

            return Ips.show(id)
                .then((response) => {
                    if(response.data.success)
                        return Promise.resolve(response.data.data.ip)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateState(data, id) {
            this.setLoading(true)
            
            return Ips.updateState(data, id)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteIp(id) {
            this.setLoading(true)

            return Ips.delete(id)
                .then((response) => {
                    let index = this.ips.findIndex((item) => item.id === id)
                    this.ips.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
