import { defineStore } from 'pinia'
import Tags from '@/api/tags'

export const useTagsStores = defineStore('tags', {
    state: () => ({
        tags: {},
        loading: false,
        last_page: 1,
        tagsTotalCount: 6
    }),
    getters:{
        getTags(){
            return this.tags
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchTags(params) {
            this.setLoading(true)
            
            return Tags.get(params)
                .then((response) => {
                    this.tags = response.data.data.tags.data
                    this.last_page = response.data.data.tags.last_page
                    this.tagsTotalCount = response.data.data.tagsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addTag(data) {
            this.setLoading(true)

            return Tags.create(data)
                .then((response) => {
                    this.tags.push(response.data.data.tag)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateTag(data) {
            this.setLoading(true)
            
            return Tags.update(data)
                .then((response) => {
                    let pos = this.tags.findIndex((item) => item.id === response.data.data.tag.id)
                    this.tags[pos] = response.data.data.tag
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteTag(id) {
            this.setLoading(true)

            return Tags.delete(id)
                .then((response) => {
                    let index = this.tags.findIndex((item) => item.id === id)
                    this.tags.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
