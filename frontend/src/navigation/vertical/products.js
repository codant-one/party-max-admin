export default [
    { 
      title: 'PRODUCTOS',
      icon: { icon:'mdi-cart' },
      children:[
        { 
          title: 'Categorías', 
          icon: { icon: 'tabler-category' },
          to: 'dashboard-products-categories', 
          action: 'ver', 
          subject: 'categorías', 
        },
        { 
          title: 'Productos', 
          icon: { icon: 'tabler-confetti' },
          to: 'dashboard-products-products', 
          action: 'ver' , 
          subject: 'productos', 
        }
      ],
    }
]
  