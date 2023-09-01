import axios from '@axios'

class Affiliates {

    get(params) {
        return axios.get('stores', {params})
    }
    getById(id) {
        return axios.get(`/stores/${id}`)
    }
}

const affiliates = new Affiliates();

export default affiliates;