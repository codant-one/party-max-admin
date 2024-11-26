import axios from '@axios'

class HomeImages {

    get(params) {
        return axios.get('home-images', {params})
    }

    create(data) {
        return axios.post('home-images', data)
    }

    show(id) {
        return axios.get(`/home-images/${id}`)
    }

    update(data) {
        return axios.post(`/home-images/${data.id}`, data.data)
    }

    delete(id) {
        return axios.post(`/home-images/delete`, id)
    }

    order(params) {
        return axios.get(`/home-images/list/order`, {params})
    }
    
}

const homeimages = new HomeImages();

export default homeimages;