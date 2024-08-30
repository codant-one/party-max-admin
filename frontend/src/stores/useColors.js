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
        all(){
            this.setLoading(true)

            return Colors.all()
                .then((colors) => {
                    this.colors = colors.data.data
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                });
        },
        fetchColors(params){
            this.setLoading(true)

            return Colors.get(params)
                .then((response) => {
                    this.colors = response.data.data.colors.data
                    this.last_page = response.data.data.colors.last_page
                    this.colorsTotalCount = response.data.data.colorsTotalCount
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
