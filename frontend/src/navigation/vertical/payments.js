export default [
    { 
      title: 'PAGOS',
      icon: { icon:'mdi-credit-card-outline' },
      children:[
        { 
          title: 'Proveedores', 
          icon: { icon: 'mdi-account-credit-card' },
          to: 'dashboard-payments-providers', 
          action: 'ver', 
          subject: 'x', 
        },
        { 
          title: 'Mi Cuenta', 
          icon: { icon: 'mdi-credit-card-settings-outline' },
          to: 'dashboard-payments-my-account', 
          action: 'ver' , 
          subject: 'x', 
        }
      ],
    }
]
  