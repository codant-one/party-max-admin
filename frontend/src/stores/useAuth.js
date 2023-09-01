import { defineStore } from 'pinia';
import Auth from '@/api/auth'

export const useAuthStores = defineStore('auth', {
    state: () => ({
        items: {}
    }),
    actions: {
        me(data) {
            return Auth.me(data.hash)
                .then((response) => {
                    return Promise.resolve(response.data.data)
                }).catch(error => {
                    return Promise.reject(error)
                })            
        },
        login(data) {
            
            return Auth.login(data)
                .then((response) => {
                    return Promise.resolve(response.data)
                }).catch(error => {
                    return Promise.reject(error)
                })
        },
        logout(){
            return Auth.logout()
                .then((response) => {
                    return Promise.resolve(response.data)
                }).catch(error => {
                    return Promise.reject(error)
                })
        }
    }
})