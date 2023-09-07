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
        title: 'Proveedores', 
        icon: { icon: 'mdi-account-tie' },
        to: 'dashboard-admin-providers', 
        action: 'ver' , 
        subject: 'roles', 
      },
      { 
        title: 'Clientes', 
        icon: { icon: 'mdi-account-star' },
        to: 'dashboard-admin-clients', 
        action: 'ver' , 
        subject: 'roles', 
      }     
    ],
  }
]
