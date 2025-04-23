import axios from '@axios'

class Coupons {

    get(params) {
        return axios.get('coupons', {params})
    }

    show(id) {
        return axios.get(`/coupons/${id}`)
    }

    delete(id){
        return axios.delete(`/coupons/${id}`)
    }
    
}

const coupons = new Coupons();

export default coupons;