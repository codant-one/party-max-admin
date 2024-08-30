import { defineStore } from 'pinia'
import Colors from '@/api/colors'

export const useColorsStores = defineStore('colors', {
    state: () => ({
        colors: {},
        loading: false,
        last_page: 1,
        colorsTotalCount: 6
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
        },
        addColor(data) {
            this.setLoading(true)

            return Colors.create(data)
                .then((response) => {
                    this.colors.push(response.data.data.color)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateColor(data) {
            this.setLoading(true)
            
            return Colors.update(data)
                .then((response) => {
                    let pos = this.colors.findIndex((item) => item.id === response.data.data.color.id)
                    this.colors[pos] = response.data.data.color
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteColor(id) {
            this.setLoading(true)

            return Colors.delete(id)
                .then((response) => {
                    let index = this.colors.findIndex((item) => item.id === id)
                    this.colors.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
