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
        title: "FAQ's",
        icon: { icon: 'tabler-help-hexagon' },
        action: 'ver',
        subject: 'categorías-faqs',
        children: [
          { 
            title: 'Categorías', 
            to: 'dashboard-admin-faq-categories', 
            action: 'ver',
            subject: 'categorías-faqs',
          },
          { 
            title: "Lista de FAQ's", 
            to: 'dashboard-admin-faqs', 
            action: 'ver',
            subject: 'faqs',
          },
        ],
      },
      { 
        title: 'Blogs', 
        icon: { icon: 'mdi-notebook-edit-outline' },
        action: 'ver',
        subject: 'categorías-blogs',
        children: [
          { 
            title: 'Categorías', 
            to: 'dashboard-admin-blog-categories', 
            action: 'ver',
            subject: 'categorías-blogs',
          },
          { 
            title: 'Tags', 
            to: 'dashboard-admin-blog-tags', 
            action: 'ver', 
            subject: 'tag-blogs', 
          },
          { 
            title: "Lista de Blogs", 
            to: 'dashboard-admin-blogs', 
            action: 'ver',
            subject: 'blogs',
          },
        ]
      }
    ],
  }
]
