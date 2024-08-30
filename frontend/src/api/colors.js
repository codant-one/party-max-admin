import axios from '@axios'

class Colors {

    get() {
        return axios.get('colors')
    }
    create(data) {
        return axios.post('/colors', data)
    }

    update(data) {
        return axios.post(`/colors/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/colors/${id}`)
    }
}

const colors = new Colors();

export default colors;