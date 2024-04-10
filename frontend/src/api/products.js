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

    updateLink(data, id) {
        return axios.put(`/products/updateStatus/${id}`, data)
    }

    updateState(data, id) {
        return axios.put(`/products/updateStates/${id}`, data)
    }

    updateOrder(data) {
        return axios.post('/products/order_id',data)
    }
}

const products = new Products();

export default products;