export default [
    { heading: 'Módulos' },
    {
        title: 'Proveedores', 
        icon: { icon: 'mdi-account-tie' },
        to: 'dashboard-admin-suppliers', 
        action: 'ver' , 
        subject: 'proveedores'
    },
    {
        title: 'Clientes', 
        icon: { icon: 'mdi-account-star' },
        to: 'dashboard-admin-clients', 
        action: 'ver' , 
        subject: 'clientes', 
    },
    {
        title: 'Pedidos', 
        icon: { icon: 'tabler-clipboard-list' },
        to: 'dashboard-admin-orders', 
        action: 'ver' , 
        subject: 'ordenes', 
    }
]
  