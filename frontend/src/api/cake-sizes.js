import axios from '@axios'

class CakeSizes {

    get(params) {
        return axios.get('cake-sizes', {params})
    }

    create(data) {
        return axios.post('/cake-sizes', data)
    }

    update(data) {
        return axios.post(`/cake-sizes/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/cake-sizes/${id}`)
    }
    
}

const cakeSizes = new CakeSizes();

export default cakeSizes;