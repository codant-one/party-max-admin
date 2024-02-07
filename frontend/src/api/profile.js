import axios from '@axios'

class Profile {

    updateData(data) {
        return axios.post('users/update/profile',  data , { headers: { 'Content-Type': 'multipart/form-data' } } )
    }
    
    updatePassword(data) {
        return axios.post('users/update/password', data)
    }

    updateStore(data) {
        return axios.post('users/update/store', data)
    }

}

const profile = new Profile();

export default profile;