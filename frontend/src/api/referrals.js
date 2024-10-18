import axios from '@axios'

class Referrals {

    get(params) {
        return axios.get('referrals', {params})
    }

    products(params) {
        return axios.get('referrals/products/all', {params})
    }

    update(params) {
        return axios.get('referrals/products/update', {params})
    }

    user(params) {
        return axios.get('referrals/products/user', {params})
    }

    upload(data) {
        return axios.post('/referrals/products/upload', data)
    }
}

const referrals = new Referrals();

export default referrals;