import axios from '@axios'

class Ips {

    get(params) {
        return axios.get('ips', {params})
    }

    show(id) {
        return axios.get(`/ips/${id}`)
    }

    updateState(data, id) {
        return axios.put(`/ips/updateStates/${id}`, data)
    }

    delete(id){
        return axios.delete(`/ips/${id}`)
    }
}

const ips = new Ips();

export default ips;