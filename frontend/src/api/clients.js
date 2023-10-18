import axios from '@axios'

class Clients {

    get(params) {
        return axios.get('clients', {params})
    }

    create(data) {
        return axios.post('/clients', data)
    }

    update(data) {
        return axios.post(`/clients/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/clients/${id}`)
    }
    
}

const clients = new Clients();

export default clients;