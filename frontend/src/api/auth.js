import axios from '@axios'

class Auth {

    me(hash) {
        return axios.post('auth/me', { hash: hash })
    }

    login(data) {
        return axios.post('auth/login', data)
    }

    logout() {
        return axios.post('auth/logout')
    }

}

const auth = new Auth();

export default auth;