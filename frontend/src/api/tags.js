import axios from '@axios'

class Tags {

    get(params) {
        return axios.get('tags', {params})
    }

    create(data) {
        return axios.post('/tags', data)
    }

    update(data) {
        return axios.post(`/tags/${data.id}`, data.data)
    }

    delete(id){
        return axios.delete(`/tags/${id}`)
    }
    
}

const tags = new Tags();

export default tags;