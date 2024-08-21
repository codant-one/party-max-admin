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
          subject: 'marcas', 
        },
        { 
          title: 'Tags', 
          icon: { icon: 'mdi-tag-multiple-outline' },
          to: 'dashboard-services-tags', 
          action: 'ver', 
          subject: 'tag-productos', 
        },
        { 
          title: 'Todos los Servicios', 
          icon: { icon: 'tabler-confetti' },
          to: 'dashboard-services-services', 
          action: 'ver' , 
          subject: 'productos', 
        },
        { 
          title: 'Pendientes', 
          icon: { icon: 'mdi-clock-time-two-outline' },
          to: 'dashboard-services-pendings', 
          action: 'ver' , 
          subject: 'productos-pendientes', 
        },
        { 
          title: 'Ordenar Servicios', 
          icon: { icon: 'mdi-sort' },
          to: 'dashboard-services-orders', 
          action: 'ver' , 
          subject: 'ordenar-productos', 
        }
      ]
    }
]
  