import { defineStore } from 'pinia'
import UserStats from '@/api/userStats'

export const useUserStatsStores = defineStore('userStats', {
    state: () => ({
        URIErrorserStats: {},
        loading: false
    }),
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        statistics() {
            this.setLoading(true)
            
            return UserStats.statistics()
                .then((response) => {
                    return Promise.resolve(response.data.data)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        }
    }
})
