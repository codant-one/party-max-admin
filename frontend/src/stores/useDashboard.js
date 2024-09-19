import { defineStore } from 'pinia'
import Dashboard from '@/api/dashboard'

export const useDashboardStores = defineStore('dashboard', {
    state: () => ({
        data: {},
        loading: false
    }),
    getters:{
        getData() {
          return this.data
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        }, 
        fetchData(){
            this.setLoading(true)

            return Dashboard.get()
                .then((response) => {
                    this.data = response.data.data
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                });
        }
    }
})
