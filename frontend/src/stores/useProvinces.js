import { defineStore } from 'pinia'
import Provinces from '@/api/provinces'

export const useProvincesStores = defineStore('provinces', {
    state: () => ({
        provinces: {},
        loading: false
    }),
    getters:{
        getProvinces() {
          return this.provinces
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        }, 
        fetchProvinces(){
            this.setLoading(true)

            return Provinces.get()
                .then((provinces) => {
                    this.provinces = provinces.data.data
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                });
        }
    }
})
