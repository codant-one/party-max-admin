import axios from '@axios'

class CakeTypes {

    get(params) {
        return axios.get('cake-types', {params})
    }

    create(data) {
        return axios.post('/cake-types', data)
    }

    update(data) {
        return axios.post(`/cake-types/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/cake-types/${id}`)
    }
    
}

const cakeTypes = new CakeTypes();

export default cakeTypes;