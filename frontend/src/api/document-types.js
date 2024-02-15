import axios from '@axios'

class DocumentTypes {

    get() {
        return axios.get('document-types')
    }
}

const documentTypes = new DocumentTypes();

export default documentTypes;