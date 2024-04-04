import axios from '@axios'

class Blogs {

    get(params) {
        return axios.get('blogs', {params})
    }

    create(data) {
        return axios.post('/blogs', data)
    }

    show(id) {
        return axios.get(`/blogs/${id}`)
    }

    update(data) {
        return axios.post(`/blogs/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/blogs/${id}`)
    }

    updateOrder(data) {
        return axios.post('/blogs/order_id',data)
    }
}

const blogs = new Blogs();

export default blogs;