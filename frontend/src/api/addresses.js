import axios from '@axios'

class Addresses {

    get(params) {
        return axios.get('addresses', {params})
    }

    create(data) {
        return axios.post('/addresses', data)
    }

    update(data) {
        return axios.put(`/addresses/${data.id}`, data)
    }

    delete(id){
        return axios.delete(`/addresses/${id}`)
    }
    
}

const addresses = new Addresses();

export default addresses;