import axios from '@axios'

class Suppliers {

    get(params) {
        return axios.get('suppliers', {params})
    }

    create(data) {
        return axios.post('/suppliers', data)
    }

    show(id) {
        return axios.get(`/suppliers/${id}`)
    }

    update(data) {
        return axios.post(`/suppliers/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/suppliers/${id}`)
    }
    
    updateCommission(id, data) {
        return axios.put(`/suppliers/updateCommission/${id}`, data)
    }

    updateBalance(id, data) {
        return axios.put(`/suppliers/updateBalance/${id}`, data)
    }
}

const suppliers = new Suppliers();

export default suppliers;