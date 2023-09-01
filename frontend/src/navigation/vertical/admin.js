export default [
  { 
    title: 'ADMINISTRACIÓN',
    icon: { icon:'tabler-home-cog' },
    children:[
      { 
        title: 'Usuarios', 
        icon: { icon: 'tabler-users' },
        to: 'dashboard-admin-users', 
        action: 'ver', 
        subject: 'usuarios', 
      },
      { 
        title: 'Roles', 
        icon: { icon: 'tabler-user' },
        to: 'dashboard-admin-roles', 
        action: 'ver' , 
        subject: 'roles', 
      }    
    ],
  },
  
]
