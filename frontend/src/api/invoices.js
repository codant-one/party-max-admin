import axios from '@axios'

class Invoices {

    get(params) {
        return axios.get('invoices', {params})
    }

    pending(params) {
        return axios.get('invoices/pending/all', {params})
    }

    byPay(params) {
        return axios.get('invoices/bypay/all', {params})
    }

    paid(params) {
        return axios.get('invoices/paid/all', {params})
    }

    all(params) {
        return axios.get('invoices/all/all', {params})
    }

    create(data) {
        return axios.post('/invoices', data)
    }

    show(id) {
        return axios.get(`/invoices/${id}`)
    }

    update(data) {
        return axios.put(`/invoices/${data.id}`, data.data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    }

    delete(id){
        return axios.delete(`/invoices/${id}`)
    }

    invoicesByUser(id, type=0, invoice_id=null) {
        return axios.get(`/invoices/users/${id}/${type}/${invoice_id}`)
    }

    updatePayment(data) {
        return axios.post(`/invoices/updatepayment/${data.id}`, data.data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    }

    suppliers(params) {
        return axios.get('invoices/suppliers/all', {params})
    }
}

const invoices = new Invoices();

export default invoices;