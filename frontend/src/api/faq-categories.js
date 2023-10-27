import axios from '@axios'

class FaqCategories {

    get(params) {
        return axios.get('faq-categories', {params})
    }

    create(data) {
        return axios.post('/faq-categories', data)
    }

    show(id) {
        return axios.get(`/faq-categories/${id}`)
    }

    update(data) {
        return axios.post(`/faq-categories/${data.id}`, data.data)
    }

    delete(id){
        return axios.post(`/faq-categories/delete`, id)
    }

    faqs(){
        return axios.get('/faq-categories/faqs/all')
    }

}

const faqCategories = new FaqCategories();

export default faqCategories;