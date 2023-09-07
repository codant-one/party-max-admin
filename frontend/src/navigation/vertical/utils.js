export default [
    { 
      title: 'UTILIDADES',
      icon: { icon:'tabler-settings' },
      children:[      
        {
          title: 'FAQ',
          icon: { icon: 'tabler-help-hexagon' },
          to: 'dashboard-utils-faqs',
          action: 'ver',
          subject: 'página-faqs',
        },
        { 
          title: 'Blogs', 
          icon: { icon: 'mdi-notebook-outline' },
          to: 'dashboard-utils-blogs', 
          action: 'ver', 
          subject: 'formulario', 
        },
        {
          title: 'Notificaciones',
          icon: { icon: 'mdi-bell-badge' },
          to: 'dashboard-utils-notifications',
          action: 'ver' ,
          subject: 'horarios',
        }
      ],
    }
]
    