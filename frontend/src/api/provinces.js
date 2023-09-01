import axios from '@axios'

class Provinces {

    get() {
        return axios.get('provinces')
    }
}

const provinces = new Provinces();

export default provinces;