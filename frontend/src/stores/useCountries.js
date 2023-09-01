import { defineStore } from 'pinia'
import Countries from '@/api/countries'

export const useCountriesStores = defineStore('countries', {
    state: () => ({
        countries: {},
        loading: false
    }),
    getters:{
        getCountries() {
          return this.countries
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        }, 
        fetchCountries(){
            this.setLoading(true)

            return Countries.get()
                .then((countries) => {
                    this.countries = countries.data.data
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                });
        }
    }
})
