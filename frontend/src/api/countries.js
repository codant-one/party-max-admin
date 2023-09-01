import axios from '@axios'

class Countries {

    get() {
        return axios.get('countries')
    }
}

const countries = new Countries();

export default countries;