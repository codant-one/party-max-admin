import axios from '@axios'

class Miscellaneous {

    getDataCupcake() {
        return axios.get('miscellaneous/cupcakes')
    }
}

const miscellaneous = new Miscellaneous();

export default miscellaneous;