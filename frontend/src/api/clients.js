import axios from '@axios'

class Clients {

    get(params) {
        return axios.get('clients', {params})
    }

    create(data) {
        return axios.post('/clients', data)
    }

    show(id) {
        return axios.get(`/clients/${id}`)
    }

    update(data) {
        return axios.post(`/clients/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/clients/${id}`)
    }

    updateState(data, id) {
        return axios.put(`/clients/updateStates/${id}`, data)
    }
    
}

const clients = new Clients();

export default clients;