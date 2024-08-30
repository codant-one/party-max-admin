import axios from '@axios'

class Colors {

    get(params) {
        return axios.get('colors', {params})
    }

    create(data) {
        return axios.post('/colors', data)
    }

    update(data) {
        return axios.post(`/colors/${data.id}`, data.data)
    }

    delete(id) {
        return axios.delete(`/colors/${id}`)
    }

    all() {
        return axios.get('colors/color/all')
    }
}

const colors = new Colors();

export default colors;