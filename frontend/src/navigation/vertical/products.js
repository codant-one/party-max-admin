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
          subject: 'categorías', 
        },
        { 
          title: 'Tags', 
          icon: { icon: 'mdi-tag-multiple-outline' },
          to: 'dashboard-products-tags', 
          action: 'ver', 
          subject: 'categorías', 
        },
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
  