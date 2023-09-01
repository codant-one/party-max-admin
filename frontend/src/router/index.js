import { canNavigate } from '@layouts/plugins/casl'
import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import { isUserLoggedIn } from './utils'
import routes from '~pages'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: to => {
        const userData = JSON.parse(localStorage.getItem('user_data') || 'null')
        if(userData){
          if(!userData.full_profile) {            
            return { name: 'complete-profile' }
          } else {            
            return { name: 'dashboard-panel' }
          }          
        }         
        
        return { name: 'login', query: to.query }
      },
    },
    {
      path: '/complete-profile',
      redirect: to => {
        const userData = JSON.parse(localStorage.getItem('user_data') || 'null')
        if(userData){
          if(!userData.full_profile) {            
            return { name: 'complete-profile' }
          } else {            
            return { name: 'dashboard-panel' }
          }          
        }         
        
        return { name: 'login', query: to.query }
      },
    },
    ...setupLayouts(routes),
  ],
})


// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards

router.beforeEach(to => {
  const isLoggedIn = isUserLoggedIn()
  
  if (canNavigate(to)) {

    const userData = JSON.parse(localStorage.getItem('user_data') || '{}') 
  
    if(isLoggedIn && !userData.full_profile && !to.meta.parar) {
      return { name: 'complete-profile' }
    }

    if (to.meta.redirectIfLoggedIn && isLoggedIn) { 
      return '/'
    }
      
  }
  else {
    if (isLoggedIn) {      
      return { name: 'not-authorized' }
    } else {      
      return { name: 'login', query: { to: to.name !== 'index' ? to.fullPath : undefined } }
    }
  }
})
export default router
