import axios from '@axios'

class Dashboard {

    get() {
        return axios.get('dashboard')
    }
}

const dashboard = new Dashboard();

export default dashboard;