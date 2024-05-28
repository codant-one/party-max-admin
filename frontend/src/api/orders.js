import axios from '@axios'

class Orders {

    get(params) {
        return axios.get('orders', {params})
    }

    create(data) {
        return axios.post('/orders', data)
    }
    
    show(id) {
        return axios.get(`/orders/${id}`)
    }

    update(data) {
        return axios.post(`/orders/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/orders/${id}`)
    }

    send(id, params){
        return axios.get(`/orders/send/${id}`, {params})
    }
    
}

const orders = new Orders();

export default orders;