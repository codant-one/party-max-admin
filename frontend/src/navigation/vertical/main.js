export default [
    { heading: 'Módulos' },
    {
        title: 'Ventas',
        icon: { icon: 'mdi-cart-check' },
        to: 'dashboard-sales',
        action: 'ver',
        subject: 'ventas',
    },
   /* {
        title: 'Facturación',
        icon: { icon: 'tabler-file-invoice' },
        to: 'dashboard-invoices',
        action: 'ver',
        subject: 'facturas',
    },*/
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
        subject: 'pedidos', 
    },
    {
        title: 'Envíos', 
        icon: { icon: 'mdi-truck-outline' },
        to: 'dashboard-admin-shipping', 
        action: 'ver' , 
        subject: 'envíos', 
    },
    { 
        title: 'Categorías', 
        icon: { icon: 'tabler-category' },
        to: 'dashboard-categories', 
        action: 'ver', 
        subject: 'categorías', 
    },
    {
        title: 'Calendario',
        icon: { icon: 'mdi-calendar-multiselect' },
        to: 'dashboard-calendar',
        action: 'ver',
        subject: 'calendario',
    },
    {
        title: 'Remisiones',
        icon: { icon: 'mdi-cart-arrow-right' },
        to: 'dashboard-referrals',
        action: 'ver',
        subject: 'remisiones',
    },
    {
        title: 'Cotizaciones',
        icon: { icon: 'tabler-file-dollar' },
        to: 'dashboard-quotes',
        action: 'ver',
        subject: 'cotizaciones',
    }
]
  