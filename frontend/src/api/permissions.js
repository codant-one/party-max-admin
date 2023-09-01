import axios from '@axios'

class Permissions {

    all(){
        return axios.get('permissions/permission/all')
    }
}

const permissions = new Permissions();

export default permissions;