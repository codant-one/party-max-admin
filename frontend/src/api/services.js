import axios from '@axios'

class Services {

    get(params) {
        return axios.get('services', {params})
    }

    create(data) {
        return axios.post('/services', data)
    }

    show(id) {
        return axios.get(`/services/${id}`)
    }

    update(data) {
        return axios.post(`/services/${data.id}`, data.data)
    }

    delete(id){
        return axios.post(`/services/delete`, id)
    }

    updateLink(data, id) {
        return axios.put(`/services/updateStatus/${id}`, data)
    }

    updateState(data, id) {
        return axios.put(`/services/updateStates/${id}`, data)
    }

    updateOrder(data) {
        return axios.post('/services/order_id', data)
    }
}

const services = new Services();

export default services;