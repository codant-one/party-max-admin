import axios from '@axios'

class Categories {

    get(params) {
        return axios.get('categories', {params})
    }

    create(data) {
        return axios.post('/categories', data)
    }

    show(id) {
        return axios.get(`/categories/${id}`)
    }

    update(data) {
        return axios.post(`/categories/${data.id}`, data.data)
    }

    delete(id) {
        return axios.post(`/categories/delete`, id)
    }

    order(params) {
        return axios.get(`/categories/list/order`, {params})
    }

    events(){
        return axios.get('/categories/events/all')
    }
    
}

const categories = new Categories();

export default categories;