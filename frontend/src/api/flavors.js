import axios from '@axios'

class Flavors {

    get(params) {
        return axios.get('flavors', {params})
    }

    create(data) {
        return axios.post('/flavors', data)
    }

    update(data) {
        return axios.post(`/flavors/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/flavors/${id}`)
    }
    
}

const flavors = new Flavors();

export default flavors;