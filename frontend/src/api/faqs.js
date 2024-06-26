import axios from '@axios'

class Faqs {

    get(params) {
        return axios.get('faqs', {params})
    }

    create(data) {
        return axios.post('/faqs', data)
    }

    update(data) {
        return axios.post(`/faqs/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/faqs/${id}`)
    }
    
    updateOrder(data) {
        return axios.post('/faqs/order_id',data)
    }
}

const faqs = new Faqs();

export default faqs;