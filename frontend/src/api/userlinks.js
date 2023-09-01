import axios from '@axios'

class Links {

    get(params) {
        return axios.get('/userLinks', {params})
    }
   
    create(data) {
        return axios.post('/userLinks', data)
    }

    update(data, id) {
        return axios.put(`/userLinks/updateStatus/${id}`, data)
    }

}

const links = new Links();

export default links;