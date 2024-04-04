import { defineStore } from 'pinia'
import Blogs from '@/api/blogs'

export const useBlogsStores = defineStore('blogs', {
    state: () => ({
        blogs: {},
        loading: false,
        last_page: 1,
        blogsTotalCount: 10
    }),
    getters:{
        getBlogs(){
            return this.blogs
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchBlogs(params) {
            this.setLoading(true)
            
            return Blogs.get(params)
                .then((response) => {
                    this.blogs = response.data.data.blogs.data
                    this.last_page = response.data.data.blogs.last_page
                    this.blogsTotalCount = response.data.data.blogsTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addBlog(data) {
            this.setLoading(true)

            return Blogs.create(data)
                .then((response) => {
                    this.blogs.push(response.data.data.blog)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showBlog(id) {
            this.setLoading(true)

            return Blogs.show(id)
                .then((response) => {
                    if(response.data.success)
                    return Promise.resolve(response.data.data.blog)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateBlog(data) {
            this.setLoading(true)
            
            return Blogs.update(data)
                .then((response) => {
                    let pos = this.blogs.findIndex((item) => item.id === response.data.data.blog.id)
                    this.blogs[pos] = response.data.data.blog
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteBlog(id) {
            this.setLoading(true)

            return Blogs.delete(id)
                .then((response) => {
                    let index = this.blogs.findIndex((item) => item.id === id)
                    this.blogs.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        updateOrder(data){
            this.setLoading(true)
            
            return Blogs.updateOrder(data)
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
