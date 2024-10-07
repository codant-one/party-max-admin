export default [
    { 
      title: 'PRODUCTOS',
      icon: { icon:'mdi-cart' },
      children:[
        { 
          title: 'Marcas', 
          icon: { icon: 'mdi-tag-faces' },
          to: 'dashboard-products-brands', 
          action: 'ver', 
          subject: 'marcas', 
        },
        { 
          title: 'Tags', 
          icon: { icon: 'mdi-tag-multiple-outline' },
          to: 'dashboard-products-tags', 
          action: 'ver', 
          subject: 'tag-productos', 
        },
        { 
          title: 'Todos los Productos', 
          icon: { icon: 'tabler-confetti' },
          to: 'dashboard-products-products', 
          action: 'ver' , 
          subject: 'productos', 
        },
        { 
          title: 'Mis Productos', 
          icon: { icon: 'mdi-cart-heart' },
          to: 'dashboard-products-products-my-products', 
          subject: 'administrador', 
        },
        { 
          title: 'Inventario', 
          icon: { icon: 'mdi-cart-arrow-down' },
          to: 'dashboard-products-products-inventory', 
          action: 'ver' , 
          subject: 'inventario',  
        },
        { 
          title: 'Pendientes', 
          icon: { icon: 'mdi-clock-time-two-outline' },
          to: 'dashboard-products-pendings', 
          action: 'ver' , 
          subject: 'productos-pendientes', 
        },
        { 
          title: 'Ordenar Productos', 
          icon: { icon: 'mdi-sort' },
          to: 'dashboard-products-orders', 
          action: 'ver' , 
          subject: 'ordenar-productos', 
        }
      ]
    }
]
  