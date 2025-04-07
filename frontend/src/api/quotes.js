import axios from '@axios'

class Quotes {

    get(params) {
        return axios.get('quotes', {params})
    }

    delete(id){
        return axios.post(`/quotes/delete`, id)
    }
    
}

const quotes = new Quotes();

export default quotes;