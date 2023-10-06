import axios from '@axios'

class Products {

    get(params) {
        return axios.get('products', {params})
    }

    create(data) {
        return axios.post('/products', data)
    }

    show(id) {
        return axios.get(`/products/${id}`)
    }

    update(data) {
        return axios.post(`/products/${data.id}`, data.data)
    }

    delete(id){
        return axios.post(`/products/delete`, id)
    }
}

const products = new Products();

export default products;