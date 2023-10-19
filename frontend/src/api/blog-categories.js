import axios from '@axios'

class BlogCategories {

    get(params) {
        return axios.get('blog-categories', {params})
    }

    create(data) {
        return axios.post('/blog-categories', data)
    }

    show(id) {
        return axios.get(`/blog-categories/${id}`)
    }

    update(data) {
        return axios.post(`/blog-categories/${data.id}`, data.data)
    }

    delete(id){
        return axios.post(`/blog-categories/delete`, id)
    }

    all(){
        return axios.get('/blog-categories/blogs/all')
    }

}

const blogCategories = new BlogCategories();

export default blogCategories;