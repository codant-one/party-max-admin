import Events from '@/api/events'
import Categories from '@/api/categories'

export const useEventsStore = defineStore('events', {
    state: () => ({
        events: {},
        last_page: 1,
        eventsTotalCount: 6,
        categories: {},
        loading: false,
        availableCalendars: [],
        availableServices: [],
        selectedCalendars: [],
        selectedUsers: [],
        isAdmin: false,
        idAdmin: 0,
        users: [],
        count: 0
    }),
    getters:{
        getUsersArray(){
            return this.users
        },
        getCount() {
            return this.count
        }
    },
    actions: {        
        setLoading(payload){
            this.loading = payload
        },
        fetchCategories() {

            return Categories.events() 
                .then((response) => {
                    this.availableCalendars = response.data.availableCalendars
                    this.availableServices = response.data.availableServices
                    this.selectedCalendars = response.data.selectedCalendars
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        fetchEvents() {
            this.setLoading(true)
            
            let data = { 
                calendars: this.selectedCalendars.join(','),
                users: this.selectedUsers.join(',')
            }

            return Events.get(data)
                .then((response) => {
                    this.events = response.data.data
                    this.isAdmin = response.data.isAdmin
                    this.idAdmin = response.data.idAdmin
                    return Promise.resolve(response.data.data)
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        addEvent(data) {
            this.setLoading(true)

            return Events.create(data)
                .then((response) => {
                    this.events.push(response.data.data.event)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        showEvent(id) {
            this.setLoading(true)

            return Events.show(id)
                .then((response) => {
                    if(response.data.data.success)
                    return Promise.resolve(response.data.data.event)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        updateEvent(data) {
            this.setLoading(true)

            return Events.update(data)
                .then((response) => {
                    let pos = this.events.findIndex((item) => item.id === response.data.data.event.id)
                    this.events[pos] = response.data.data.event
                    this.count = response.data.data.count
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })
         
        },
        removeEvent(id) {
            this.setLoading(true)

            return Events.delete(id)
                .then((response) => {
                    let index = this.events.findIndex((item) => item.id === id)
                    this.events.splice(index, 1)
                    return Promise.resolve(response)
                })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        changeEventStatus(params) {
            this.setLoading(true)
      
            return Events.status(params)
                .then(response => {      
                    return Promise.resolve(response)
                    })
                .catch(error => Promise.reject(error))
                .finally(() => {
                    this.setLoading(false)
                })  
        },
        fetchAllEvents() {
            this.setLoading(true)
            
            let data = { 
                calendars: this.selectedCalendars.join(','),
                users: this.selectedUsers.join(',')
            }

            return Events.events(data)
                .then((response) => {
                    this.events = response.data.data
                    this.isAdmin = response.data.isAdmin
                    this.idAdmin = response.data.idAdmin
                    return Promise.resolve(response.data.data)
                })
                .catch(error => console.log(error))
                .finally(() => {
                    this.setLoading(false)
                })
            
        },
        deleteBatch(params){
            this.setLoading(true)
      
            return Events.deleteBatch(params)
              .then(response => {
                return Promise.resolve(response)
              })
              .catch(error => console.log(error))
              .finally(() => {
                this.setLoading(false)
              })
        },
        getUsers(){
            this.setLoading(true)
      
            return Events.getUsers()
              .then(response => {
                this.users = response.data.users
              })
              .catch(error => console.log(error))
              .finally(() => {
                this.setLoading(false)
              })
        },
        getPendings(){
            this.setLoading(true)
      
            return Events.getPendings()
              .then(response => {
                this.count = response.data.count
                return Promise.resolve(response.data.count)
              })
              .catch(error => console.log(error))
              .finally(() => {
                this.setLoading(false)
              })
        }
    },
})
