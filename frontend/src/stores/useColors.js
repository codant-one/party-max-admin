import { defineStore } from 'pinia'
import Colors from '@/api/colors'

export const useColorsStores = defineStore('colors', {
    state: () => ({
        colors: {},
        loading: false
    }),
    getters:{
        getColors() {
          return this.colors
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        }, 
        fetchColors(){
            this.setLoading(true)

            return Colors.get()
                .then((colors) => {
                    this.colors = colors.data.data
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                });
        }
    }
})
