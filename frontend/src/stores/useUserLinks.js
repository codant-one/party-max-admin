import { defineStore } from 'pinia'
import Links from '@/api/userlinks.js'

export const useUserlinksStores = defineStore('userlinks', {
    state: () => ({
        userlinks: {},
        loading: false,
        last_page: 1,
        linksTotalCount: 6
    }),
    getters:{
        getUserlinks(){
            return this.userlinks
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchUserlinks(params) {
            this.setLoading(true)
            
            return Links.get(params)
                .then((response) => {
                    this.userlinks = response.data.data.UserLinks.data
                    this.last_page = response.data.data.UserLinks.last_page
                    this.linksTotalCount = response.data.data.UserLinksTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateLink(data, id) {
            this.setLoading(true)
            
            return Links.update(data, id)
                .then((response) => {
                    let pos = this.userlinks.findIndex((item) => item.id === response.data.data.userLink.id)
                    this.userlinks[pos] = response.data.data.userLink
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        }
    }
})
