export default [
    { 
      title: 'REPORTES',
      icon: { icon:'mdi-finance' },
      children:[
        { 
          title: 'Reporte 1', 
          icon: { icon: 'mdi-poll' },
          to: 'dashboard-reports-report-1', 
          action: 'ver', 
          subject: 'usuarios', 
        },
        { 
          title: 'Reporte 2', 
          icon: { icon: 'mdi-poll' },
          to: 'dashboard-reports-report-2', 
          action: 'ver' , 
          subject: 'roles', 
        },
        { 
            title: 'Reporte 3', 
            icon: { icon: 'mdi-poll' },
            to: 'dashboard-reports-report-3', 
            action: 'ver' , 
            subject: 'roles', 
          }
      ],
    }
]
  