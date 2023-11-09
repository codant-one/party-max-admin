import axios from '@axios'

class Brands {

    get(params) {
        return axios.get('brands', {params})
    }

    create(data) {
        return axios.post('/brands', data)
    }

    update(data) {
        return axios.post(`/brands/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/brands/${id}`)
    }
    
}

const brands = new Brands();

export default brands;