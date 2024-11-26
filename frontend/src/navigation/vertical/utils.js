export default [
    { 
      title: 'CAPACITACIÓN',
      icon: { icon:'tabler-settings' },
      children:[      
        {
          title: 'FAQ',
          icon: { icon: 'mdi-frequently-asked-questions' },
          to: 'dashboard-utils-faqs',
          action: 'ver',
          subject: 'página-faqs',
        },
        { 
          title: 'Blogs', 
          icon: { icon: 'mdi-notebook-outline' },
          to: 'dashboard-utils-blogs', 
          action: 'ver', 
          subject: 'página-blogs', 
        }
        // {
        //   title: 'Notificaciones',
        //   icon: { icon: 'mdi-bell-badge' },
        //   to: 'dashboard-utils-notifications',
        //   action: 'ver' ,
        //   subject: 'página-notificaciones', 
        // }
      ],
    }
]
    