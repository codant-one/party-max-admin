export default [
    { 
      title: 'SERVICIOS',
      icon: { icon:'mdi-hand-heart-outline' },
      children:[
        { 
          title: 'Marcas', 
          icon: { icon: 'mdi-tag-faces' },
          to: 'dashboard-services-brands', 
          action: 'ver', 
          subject: 'marcas-servicios', 
        },
        { 
          title: 'Tags', 
          icon: { icon: 'mdi-tag-multiple-outline' },
          to: 'dashboard-services-tags', 
          action: 'ver', 
          subject: 'tag-servicios', 
        },
        { 
          title: 'Todos los Servicios', 
          icon: { icon: 'tabler-confetti' },
          to: 'dashboard-services-services', 
          action: 'ver' , 
          subject: 'servicios', 
        },
        { 
          title: 'Pendientes', 
          icon: { icon: 'mdi-clock-time-two-outline' },
          to: 'dashboard-services-pendings', 
          action: 'ver' , 
          subject: 'servicios-pendientes', 
        },
        { 
          title: 'Ordenar Servicios', 
          icon: { icon: 'mdi-sort' },
          to: 'dashboard-services-orders', 
          action: 'ver' , 
          subject: 'ordenar-servicios', 
        }
      ]
    }
]
  