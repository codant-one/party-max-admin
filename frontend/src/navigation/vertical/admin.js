export default [
  { 
    title: 'ADMINISTRACIÓN',
    icon: { icon:'tabler-home-cog' },
    children:[
      { 
        title: 'Usuarios', 
        icon: { icon: 'mdi-account' },
        to: 'dashboard-admin-users', 
        action: 'ver', 
        subject: 'usuarios', 
      },
      { 
        title: 'Roles', 
        icon: { icon: 'mdi-account-lock-open' },
        to: 'dashboard-admin-roles', 
        action: 'ver' , 
        subject: 'roles', 
      },
      { 
        title: 'FAQs', 
        icon: { icon: 'tabler-help-hexagon' },
        to: 'dashboard-admin-faqs', 
        action: 'ver' , 
        subject: 'faqs', 
      }   
    ],
  }
]
