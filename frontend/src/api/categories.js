import axios from '@axios'

class Categories {

    get(params) {
        return axios.get('categories', {params})
    }

    create(data) {
        return axios.post('/categories', data)
    }

    update(data, id) {
        return axios.put(`/categories/${id}`, data)
    }

    delete(id){
        return axios.delete(`/categories/${id}`)
    }
    
}

const categories = new Categories();

export default categories;