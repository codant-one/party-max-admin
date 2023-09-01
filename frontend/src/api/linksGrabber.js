import axios from '@axios'

class LinksGrabber {

    getconsult(url) {
        return axios.get(`linksGrabber/consult?url=${url}`)
    }
   
    getconvert(params) {
        return axios.get('linksGrabber/convert', {params})
    }
}

const linksGrabber = new LinksGrabber();

export default linksGrabber;