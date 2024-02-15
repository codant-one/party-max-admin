import { defineStore } from 'pinia'
import DocumentTypes from '@/api/document-types'

export const useDocumentTypesStores = defineStore('document-types', {
    state: () => ({
        document_types: {},
        loading: false
    }),
    getters:{
        getDocumentTypes() {
          return this.document_types
        }
    },
    actions: {
        setLoading(payload){
            this.loading = payload
        }, 
        fetchDocumentTypes(){
            this.setLoading(true)

            return DocumentTypes.get()
                .then((response) => {
                    this.document_types = response.data.data
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                });
        }
    }
})
