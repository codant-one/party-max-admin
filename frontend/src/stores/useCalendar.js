import esLocale from '@fullcalendar/core/locales/es';
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'
import timeGridPlugin from '@fullcalendar/timegrid'
import dayjs from 'dayjs';
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { useEventsStore } from '@/stores/useEvents'
import { themeConfig } from '@themeConfig'
import { avatarText } from '@core/utils/formatters'
import { formatNumber } from '@/@core/utils/formatters'

export const blankEvent = {
  title: '',
  start: '',
  end: '',
  allDay: true,
  delta: 0,
  extendedProps: {
    /*
          ℹ️ We have to use undefined here because if we have blank string as value then select placeholder will be active (moved to top).
          Hence, we need to set it to undefined or null
        */
    state_id: 4,
    calendar: undefined,
    order_detail: undefined,
    description: ''
  },
}
export const useCalendar = (event, isEventHandlerSidebarActive, isLeftSidebarOpen) => {
  // 👉 themeConfig
  const { isAppRtl } = useThemeConfig()

  // 👉 Store
  const store = useEventsStore()

  // 👉 Calendar template ref
  const refCalendar = ref()

  // ℹ️ Extract event data from event API
  const extractEventDataFromEventApi = eventApi => {

    const { id, title, start, end, extendedProps: { state_id, calendar, order_detail, description }, allDay, delta } = eventApi
    
    return {
      id,
      title,
      start,
      end,
      extendedProps: {
        state_id,
        calendar,
        order_detail,
        description
      },
      allDay,
      delta
    }
  }


  // 👉 Fetch events
  const fetchEvents = (info, successCallback) => {
    // If there's no info => Don't make useless API call
    if (!info)
      return

    isRequestOngoing.value = true

    store.fetchEvents()
      .then(res => {
        successCallback(res.map(e => ({
          ...e,
    
          // Convert string representation of date to Date object
          start:  dayjs(e.start).toISOString(),
          end: dayjs(e.end).add(1, 'day').toISOString(),
        })))

        isRequestOngoing.value = false
      })
      .catch(e => {
        isRequestOngoing.value = false
        console.error('Error occurred while fetching calendar events', e)
      })
  }


  // 👉 Calendar API
  const calendarApi = ref(null)


  // 👉 Update event in calendar [UI]
  const updateEventInCalendar = (updatedEventData, propsToUpdate, extendedPropsToUpdate) => {
    const existingEvent = calendarApi.value?.getEventById(updatedEventData.id)
    if (!existingEvent) {
      console.warn('Can\'t found event in calendar to update')
      
      return
    }

    // ---Set event properties except date related
    // Docs: https://fullcalendar.io/docs/Event-setProp
    // dateRelatedProps => ['start', 'end', 'allDay']
    for (let index = 0; index < propsToUpdate.length; index++) {
      const propName = propsToUpdate[index]

      existingEvent.setProp(propName, updatedEventData[propName])
    }

    // --- Set date related props
    // ? Docs: https://fullcalendar.io/docs/Event-setDates
    existingEvent.setDates(updatedEventData.start, updatedEventData.end, { allDay: updatedEventData.allDay })

    // --- Set event's extendedProps
    // ? Docs: https://fullcalendar.io/docs/Event-setExtendedProp
    for (let index = 0; index < extendedPropsToUpdate.length; index++) {
      const propName = extendedPropsToUpdate[index]

      existingEvent.setExtendedProp(propName, updatedEventData.extendedProps[propName])
    }
  }


  // 👉 Remove event in calendar [UI]
  const removeEventInCalendar = eventId => {
    const _event = calendarApi.value?.getEventById(eventId)
    if (_event)
      _event.remove()
  }


  // 👉 refetch events
  const refetchEvents = () => {
    calendarApi.value?.refetchEvents()
  }

  watch(
    () => [
      store.selectedCalendars,
      store.selectedUsers
    ], refetchEvents
  )

  // 👉 Add event
  const addEvent = _event => {
    
    _event.start = dayjs(_event.start).format('YYYY-MM-DD')

    store.addEvent(_event)
      .then((res) => {
        advisor.value = {
          type: 'success',
          message: 'Pedido creado con éxito!',
          show: true
        }
        refetchEvents()
      })
      .catch((err) => {
        advisor.value = {
          type: 'error',
          message: err,
          show: true
        }
      })

    setTimeout(() => {
      advisor.value = {
        type: '',
        message: '',
        show: false
      }
    }, 3000)
  }


  // 👉 Update event
  const updateEvent = _event => {

    _event.start = dayjs(_event.start).format('YYYY-MM-DD')

    if(_event.delta !== 0)
      _event.end = dayjs(_event.end).subtract(1, 'day').format('YYYY-MM-DD')
    else
      _event.end = dayjs(_event.end).format('YYYY-MM-DD')

    store.updateEvent(_event)
      .then(res => {
        const propsToUpdate = ['id', 'title']
        const extendedPropsToUpdate = ['calendar', 'description']

        // updateEventInCalendar(res.data.data.event, propsToUpdate, extendedPropsToUpdate)

        advisor.value = {
          type: 'success',
          message: 'Pedido actualizado con éxito!',
          show: true
        }

        refetchEvents()
      })
      .catch((err) => {
        advisor.value = {
          type: 'error',
          message: err,
          show: true
        }
      })

      setTimeout(() => {
        advisor.value = {
          type: '',
          message: '',
          show: false
        }
      }, 3000)
  }

  // 👉 Remove event
  const removeEvent = eventId => {
    store.removeEvent(eventId)
      .then(() => {
      
        removeEventInCalendar(eventId)

        advisor.value = {
          type: 'success',
          message: 'Pedido eliminado con éxito!',
          show: true
        }
      })
      .catch((err) => {
        advisor.value = {
          type: 'error',
          message: err,
          show: true
        }
      })

      setTimeout(() => {
        advisor.value = {
          type: '',
          message: '',
          show: false
        }
      }, 3000)
  }

  const isRequestOngoing = ref(true)
  const advisor = ref({
    type: '',
    message: '',
    show: false
  })

  // 👉 Calendar options
  const calendarOptions = {
    plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin, listPlugin],
    initialView: 'dayGridMonth',
    locale: esLocale, // Establece el idioma español
    headerToolbar: {
      start: 'drawerToggler,prev,next title',
      end: 'listMonth,dayGridDay,dayGridWeek,dayGridMonth',
    },
    timeZone: 'local',
    eventOrder: ["state_id"],
    views: {
      /*
        Max number of events within a given day
        Docs: https://fullcalendar.io/docs/dayMaxEvents
      */
      listMonth: {
        dayMaxEvents: false // Configuración específica para listMonth
      },
      dayGridDay: {
        dayMaxEvents: false, // Configuración específica para dayGridDay
        dayHeaderContent: function(arg) {
          return arg.date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
        }
      },
      dayGridWeek: {
        dayMaxEvents: false // Configuración específica para dayGridWeek
      },
      dayGridMonth: {
        dayMaxEvents: 2 // Configuración específica para dayGridMonth
      }
    },
    eventTimeFormat: {
      day: 'numeric',  // Mostrar el día del mes (1-31)
      month: '2-digit', // Mostrar el mes (01-12)
      year: 'numeric' // Mostrar el año (ej. 2023)
    },
    eventContent: function(info){
      if (info.view.type === 'listMonth' || info.view.type === 'dayGridDay') {
        
        let user = info.event.extendedProps.order_detail.service.user;
        let eventText = info.event.title;
        let state_id = info.event.extendedProps.state_id
        let price_ = info.event.extendedProps.order_detail.total
        let state = ''
        let color = ''

        switch (state_id) {
          case 4:
            state = 'Pendiente'
            color = 'error'
            break;
          case 6:
            state = 'Rechazado'
            color = 'success'
            break;
          case 7:
            state = 'Entregado'
            color = 'primary'
            break;
          default:
            state = 'Pendiente'
            color = 'error'
        }

        let content = document.createElement('div');
        content.classList.add('event-content');

        let usersInfo = document.createElement('div');
        usersInfo.classList.add('users-content');
        usersInfo.classList.add('w-100');
        
        let eventImage = themeConfig.settings.urlStorage + user.avatar
        let image = document.createElement('img')
        let textName = document.createElement('span')
        let div = document.createElement('div')
        let textEvent = document.createElement('span')
        let price = document.createElement('span')
        let avatar = document.createElement('div')
        let avatar__underlay = document.createElement('span')
        let avatarName = document.createElement('span')
        let spanState = document.createElement('span')
        let divState = document.createElement('div')

        image.src = (user.avatar === null) ? '' : eventImage
        image.classList.add('event-image')

        textName.textContent = user.name + ' ' + user.last_name
        textName.classList.add('user-text')
          
        avatar__underlay.classList.add('v-avatar__underlay')
        avatarName.textContent = avatarText(user.name + ' ' + user.last_name)

        avatar.classList.add('v-avatar')
        avatar.classList.add('v-avatar--density-default')
        avatar.classList.add('v-avatar--variant-tonal')
        avatar.classList.add('event-image')
        avatar.appendChild(avatarName);
        avatar.appendChild(avatar__underlay)

        textEvent.textContent = 'Pedido #' + eventText
        textEvent.classList.add('w-50')
        price.textContent = '$' + formatNumber(price_)

        divState.classList.add('flex-grow-1')

        spanState.textContent = state
        spanState.classList.add('v-chip')
        spanState.classList.add('v-theme--light')
        spanState.classList.add('bg-'+color)
        spanState.classList.add('v-chip--density-default')
        spanState.classList.add('v-chip--size-small')
        spanState.classList.add('v-chip--variant-elevated')
        spanState.classList.add('position-vchip')
        spanState.classList.add('text-end')

        div.classList.add('user-content')

        if(user.avatar === null)
          div.appendChild(avatar)
        else
          div.appendChild(image)

        div.appendChild(textName)
        div.appendChild(textEvent)
        div.appendChild(price)
        div.appendChild(divState)
        div.appendChild(spanState)

        usersInfo.appendChild(div)

        // border-left: 5px solid rgb(var(--v-theme-error)) !important;

        content.appendChild(usersInfo)

        return { domNodes: [content] }
      }
    },

    events: fetchEvents,

    // ❗ We need this to be true because when its false and event is allDay event and end date is same as start data then Full calendar will set end to null
    // forceEventDuration: true,

    /*
        Enable dragging and resizing event
        Docs: https://fullcalendar.io/docs/editable
      */
    editable: true,

    /*
        Enable resizing event from start
        Docs: https://fullcalendar.io/docs/eventResizableFromStart
      */
    // eventResizableFromStart: true,

    /*
        Automatically scroll the scroll-containers during event drag-and-drop and date selecting
        Docs: https://fullcalendar.io/docs/dragScroll
      */
    dragScroll: true,

    /*
        Determines if day names and week names are clickable
        Docs: https://fullcalendar.io/docs/navLinks
      */
    navLinks: true,
    eventClassNames(info) {

      let calendarEvent = info.event 
      let colorName = 'error'
      let className = ''

      switch(calendarEvent._def.extendedProps.state_id) {
        case 4:
          colorName = 'error'
        break;
        case 6:
          colorName = 'success'
        break;
        case 7:
          colorName = 'primary'
        break;
      }

      if (info.view.type === 'dayGridDay')
        className = [`day-setting`]
      else
        className = [`bg-light-${colorName} text-${colorName}`]

      return className
    },
    eventClick(info) {
      // * Only grab required field otherwise it goes in infinity loop
      // ! Always grab all fields rendered by form (even if it get `undefined`) otherwise due to Vue3/Composition API you might get: "object is not extensible"
      event.value = extractEventDataFromEventApi(info.event)
      isEventHandlerSidebarActive.value = true
      info.jsEvent.preventDefault(); // don't let the browser navigate
    },

    // customButtons
    dateClick(info) {
      event.value = { ...event.value, start: dayjs(info.date).toISOString() }
      isEventHandlerSidebarActive.value = true
    },

    /*
          Handle event drop (Also include dragged event)
          Docs: https://fullcalendar.io/docs/eventDrop
          We can use `eventDragStop` but it doesn't return updated event so we have to use `eventDrop` which returns updated event
        */
    eventDrop: function(info) {
      info.event.delta = info.delta.days
      updateEvent(extractEventDataFromEventApi(info.event))
    },

    /*
          Handle event resize
          Docs: https://fullcalendar.io/docs/eventResize
        */
    eventResize({ event: resizedEvent }) {
      if (resizedEvent.start && resizedEvent.end)
        updateEvent(extractEventDataFromEventApi(resizedEvent))
    },
    customButtons: {
      drawerToggler: {
        text: 'calendarDrawerToggler',
        click() {
            isLeftSidebarOpen.value = true
        },
      },
    },
  }


  // 👉 onMounted
  onMounted(() => {
    calendarApi.value = refCalendar.value.getApi()
  })


  // 👉 Jump to date on sidebar(inline) calendar change
  const jumpToDate = currentDate => {
    calendarApi.value?.gotoDate(dayjs(currentDate).toISOString())
  }

  watch(isAppRtl, val => {
    calendarApi.value?.setOption('direction', val ? 'rtl' : 'ltr')
  }, { immediate: true })
  
  return {
    advisor,
    isRequestOngoing,
    refCalendar,
    calendarOptions,
    refetchEvents,
    fetchEvents,
    addEvent,
    updateEvent,
    removeEvent,
    jumpToDate,
  }
}
