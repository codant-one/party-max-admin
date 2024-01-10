import axios from '@axios'

class Orders {

    get(params) {
        return axios.get('orders', {params})
    }

    create(data) {
        return axios.post('/orders', data)
    }

    update(data) {
        return axios.post(`/orders/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/orders/${id}`)
    }
    
}

const orders = new Orders();

export default orders;