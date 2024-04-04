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
          title: 'Categorías', 
          icon: { icon: 'tabler-category' },
          to: 'dashboard-products-categories', 
          action: 'ver', 
          subject: 'categorías', 
        },
        { 
          title: 'Todos los Productos', 
          icon: { icon: 'tabler-confetti' },
          to: 'dashboard-products-products', 
          action: 'ver' , 
          subject: 'productos', 
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
          subject: 'productos-pendientes', 
        }
      ]
    }
]
  