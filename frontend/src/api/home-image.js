import axios from '@axios'

class HomeImages {

    get(params) {
        return axios.get('home-images', {params})
    }

    create(data) {
        return axios.post('/home-images', data)
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

    updateOrder(data) {
        return axios.post('/home-images/order_id',data)
    }
    
}

const homeImages = new HomeImages();

export default homeImages;