import { defineStore } from 'pinia'
import LinksGrabber from '@/api/linksGrabber'

export const useLinksGrabberStores = defineStore('linksGrabber', {
    state: () => ({
        loading: false,
    }),
    actions: {
        setLoading(payload){
            this.loading = payload
        },
        getconsultLinksGrabber(url) {
            this.setLoading(true)
            return LinksGrabber.getconsult(url)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        getconvertLinksGrabber(data) {
            this.setLoading(true)
            return LinksGrabber.getconvert(data)
                .then((response) => {
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
        }       
    }
})
