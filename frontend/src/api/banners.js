import axios from '@axios'

class Banners {

    get(params) {
        return axios.get('banners', {params})
    }

    create(data) {
        return axios.post('/banners', data)
    }

    show(id) {
        return axios.get(`/banners/${id}`)
    }

    update(data) {
        return axios.post(`/banners/${data.id}`, data.data)
    }

    delete(id) {
        return axios.post(`/banners/delete`, id)
    }
    
}

const banners = new Banners();

export default banners;