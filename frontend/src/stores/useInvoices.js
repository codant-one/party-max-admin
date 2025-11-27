import { defineStore } from 'pinia'
import Invoices from '@/api/invoices'

export const useInvoicesStores = defineStore('invoices', {
    state: () => ({
        invoices: {},
        loading: false,
        last_page: 1,
        invoicesTotalCount: 6
    }),
    getters:{
        getInvoices(){
            return this.invoices
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchPending(params) {
            this.setLoading(true)
            
            return Invoices.pending(params)
                .then((response) => {
                    this.invoices = response.data.data.invoices.data
                    this.last_page = response.data.data.invoices.last_page
                    this.invoicesTotalCount = response.data.data.invoicesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        fetchByPay(params) {
            this.setLoading(true)
            
            return Invoices.byPay(params)
                .then((response) => {
                    this.invoices = response.data.data.invoices.data
                    this.last_page = response.data.data.invoices.last_page
                    this.invoicesTotalCount = response.data.data.invoicesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        fetchPaid(params) {
            this.setLoading(true)
            
            return Invoices.paid(params)
                .then((response) => {
                    this.invoices = response.data.data.invoices.data
                    this.last_page = response.data.data.invoices.last_page
                    this.invoicesTotalCount = response.data.data.invoicesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        fetchAll(params) {
            this.setLoading(true)
            
            return Invoices.all(params)
                .then((response) => {
                    this.invoices = response.data.data.invoices.data
                    this.last_page = response.data.data.invoices.last_page
                    this.invoicesTotalCount = response.data.data.invoicesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        fetchInvoices(params) {
            this.setLoading(true)
            
            return Invoices.get(params)
                .then((response) => {
                    this.invoices = response.data.data.invoices.data
                    this.last_page = response.data.data.invoices.last_page
                    this.invoicesTotalCount = response.data.data.invoicesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addInvoice(data) {
            this.setLoading(true)

            return Invoices.create(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showInvoice(id) {
            this.setLoading(true)

            return Invoices.show(id)
                .then((response) => {
                    if(response.data.success)
                        return Promise.resolve(response.data.data.invoice)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateInvoice(data) {
            this.setLoading(true)
            
            return Invoices.update(data)
                .then((response) => {
                    let pos = this.invoices.findIndex((item) => item.id === response.data.data.invoice.id)
                    this.invoices[pos] = response.data.data.invoice
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteInvoice(id) {
            this.setLoading(true)

            return Invoices.delete(id)
                .then((response) => {
                    let index = this.invoices.findIndex((item) => item.id === id)
                    this.invoices.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        invoicesByUser(id, type=0, invoice_id=null) {
            this.setLoading(true)
            
            return Invoices.invoicesByUser(id, type, invoice_id)
                .then((response) => {
                    return Promise.resolve(response.data)
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
        },
        updatePayment(data) {
            this.setLoading(true)
            
            return Invoices.updatePayment(data)
                .then((response) => {
                    let pos = this.invoices.findIndex((item) => item.id === response.data.data.invoice.id)
                    this.invoices[pos] = response.data.data.invoice
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        fetchSuppliers(params) {
            this.setLoading(true)
            
            return Invoices.suppliers(params)
                .then((response) => {
                    this.invoices = response.data.data.invoices.data
                    this.last_page = response.data.data.invoices.last_page
                    this.invoicesTotalCount = response.data.data.invoicesTotalCount
                })
        }
    }
})
