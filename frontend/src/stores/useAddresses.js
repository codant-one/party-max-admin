import { defineStore } from 'pinia'
import Addresses from '@/api/addresses'

export const useAddressesStores = defineStore('addresses', {
    state: () => ({
        addresses: {},
        loading: false,
        last_page: 1,
        addressesTotalCount: 6
    }),
    getters:{
        getAddresses(){
            return this.addresses
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        fetchAddresses(params) {
            this.setLoading(true)
            
            return Addresses.get(params)
                .then((response) => {
                    this.addresses = response.data.data.addresses.data
                    this.last_page = response.data.data.addresses.last_page
                    this.addressesTotalCount = response.data.data.addressesTotalCount
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addAddress(data) {
            this.setLoading(true)

            return Addresses.create(data)
                .then((response) => {
                    // this.addresses.push(response.data.data.address)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateAddress(data) {
            this.setLoading(true)
            
            return Addresses.update(data)
                .then((response) => {
                    // let pos = this.addresses.findIndex((item) => item.id === response.data.data.address.id)
                    // this.addresses[pos] = response.data.data.address
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        deleteAddress(id) {
            this.setLoading(true)

            return Addresses.delete(id)
                .then((response) => {
                    // let index = this.addresses.findIndex((item) => item.id === id)
                    // this.addresses.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        }
    }
})
