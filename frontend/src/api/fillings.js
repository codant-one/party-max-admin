import axios from '@axios'

class Fillings {

    get(params) {
        return axios.get('fillings', {params})
    }

    create(data) {
        return axios.post('/fillings', data)
    }

    update(data) {
        return axios.post(`/fillings/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/fillings/${id}`)
    }
    
}

const fillings = new Fillings();

export default fillings;