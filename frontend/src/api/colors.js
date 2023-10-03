import axios from '@axios'

class Colors {

    get() {
        return axios.get('colors')
    }
}

const colors = new Colors();

export default colors;