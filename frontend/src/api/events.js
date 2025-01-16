import axios from '@axios'

class Events {

    get(params) {
        return axios.get('events', {params})
    }

    create(data) {
        return axios.post('/events', data)
    }

    show(id) {
        return axios.get(`/events/${id}`)
    }

    update(data) {
        return axios.put(`/events/${data.id}`, data)
    }

    delete(id){
        return axios.delete(`/events/${id}`)
    }

    status(params) {
        return axios.get(`/events/status/change`, { params })
    }

    events(params) {
        return axios.get(`/events/status/all`, { params })
    }

    deleteBatch(params) {
        return axios.post(`/events/delete/batch`, { params })
    }

    getUsers() {
        return axios.get('/events/users/all')
    }

    getPendings() {
        return axios.get('/events/pendings/all')
    }
}

const events = new Events();

export default events;