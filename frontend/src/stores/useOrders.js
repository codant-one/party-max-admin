import { defineStore } from 'pinia'
import Orders from '@/api/orders'

export const useOrdersStores = defineStore('orders', {
    state: () => ({
        orders: {},
        loading: false,
        last_page: 1,
        ordersTotalCount: 6
    }),
    getters:{
        getOrders(){
            return this.orders
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchOrders(params) {
            this.setLoading(true)
            
            return Orders.get(params)
                .then((response) => {
                    this.orders = response.data.data.orders.data
                    this.last_page = response.data.data.orders.last_page
                    this.ordersTotalCount = response.data.data.ordersTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addOrder(data) {
            this.setLoading(true)

            return Orders.create(data)
                .then((response) => {
                    this.orders.push(response.data.data.order)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showOrder(id) {
            this.setLoading(true)

            return Orders.show(id)
                .then((response) => {
                    if(response.data.success)
                        return Promise.resolve(response.data.data.order)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateOrder(data) {
            this.setLoading(true)
            
            return Orders.update(data)
                .then((response) => {
                    let pos = this.orders.findIndex((item) => item.id === response.data.data.order.id)
                    this.orders[pos] = response.data.data.order
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteOrder(id) {
            this.setLoading(true)

            return Orders.delete(id)
                .then((response) => {
                    let index = this.orders.findIndex((item) => item.id === id)
                    this.orders.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
