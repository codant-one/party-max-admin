<script setup>

import profile from '@assets/icons/icon-perfil-white.svg?inline';
import home from '@assets/icons/lineas-de-cuadricula.svg?inline';
import favorites from '@assets/icons/heart2.svg?inline';
import favorites_mobile from '@assets/icons/heart-mobile.svg?inline';
import purchases from '@assets/icons/icon-compras.svg?inline';
import coupons from '@assets/icons/ticket-discount.svg?inline';

const name = ref(null)
const usermail = ref(null)
   
const isMobile = /Mobi/i.test(navigator.userAgent)
const drawer = ref(isMobile ? false : true)

const fixedSectionRefd = ref(null)
const classFixed = ref('second-header-dashboard')

const me = async () => {
    if(localStorage.getItem('user_data')){
      const userData = localStorage.getItem('user_data')
      const userDataJ = JSON.parse(userData)

      name.value = userDataJ.name + ' ' +(userDataJ.last_name ?? '')
      usermail.value = userDataJ.email
    }
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

const handleScroll = () => {

    if (fixedSectionRefd.value && isMobile) {
      const scrollY = window.scrollY || window.pageYOffset;
    
      classFixed.value = (scrollY === 0 ) ? 'second-header-dashboard' : 'topFixedDashboard';
    }
};

me()

</script>

<template>
    <VLayout class="dashboard">
        <VNavigationDrawer 
            v-model="drawer"
            class="custom-background"
            app 
            floating
            permanent
        >
            <VList density="compact" nav>
                <!-- <router-link :to="{ name: 'dashboardHome' }" class="link-menu" exact>
                    <VListItem 
                        :class="{ 'v-list-item--active': ($route.name === 'dashboardHome') }"
                        class="items-list" title="Home" value="dashboardHome" >
                        <template v-slot:prepend>
                            <home style="width: 24px; height: 24px;"></home>
                        </template>
                    </VListItem>
                </router-link> -->
                <router-link :to="{ name: 'profile' }" class="link-menu" exact>
                    <VListItem 
                        :class="{ 'v-list-item--active': $route.name === 'profile' }"
                        class="items-list" style="margin-top: 40px;" title="Mi perfil" value="profile">
                        <template v-slot:prepend>
                            <profile class="icon-profile"></profile>
                        </template>
                    </VListItem>
                </router-link>
                <router-link :to="{ name: 'purchases' }" class="link-menu" exact>
                    <VListItem 
                        :class="{ 'v-list-item--active': $route.name === 'purchases' }"
                        class="items-list" style="margin-top: 16px;" title="Compras" value="purchases">
                        <template v-slot:prepend>
                            <purchases style="width: 24px; height: 24px;"></purchases>
                        </template>
                    </VListItem>
                </router-link>
                <router-link :to="{ name: 'coupons' }" class="link-menu" exact>
                    <VListItem 
                        :class="{ 'v-list-item--active': $route.name === 'coupons' }"
                        class="items-list" style="margin-top: 16px;" title="Cupones" value="coupons">
                        <template v-slot:prepend>
                            <coupons style="width: 24px; height: 24px;"></coupons>
                        </template>
                    </VListItem>
                </router-link>
                <router-link :to="{ name: 'favorites' }" class="link-menu" exact>
                    <VListItem 
                        :class="{ 'v-list-item--active': $route.name === 'favorites' }"
                        class="items-list" style="margin-top: 16px;" title="Mis Favoritos" value="favorites">
                        <template v-slot:prepend>
                            <favorites></favorites>
                        </template>
                    </VListItem>
                </router-link>
            </VList>
        </VNavigationDrawer>
        <VMain style="min-height: 700px; background-color: #E2F8FC;" class="pt-30">
            <div class="mt-0 p-0 mt-md-10 container-dashboard d-block d-md-none">
                <VCard class="no-shadown card-information p-2 transparent box-bottom" :class="classFixed" ref="fixedSectionRefd">
                    <VCardTitle class="p-0 d-flex align-center justify-content-center">
                        <div class="d-block text-center box-iconmob box-comp">
                            <router-link :to="{ name: 'profile' }" class="link-menumob tw-text-tertiary tw-no-underline" exact>
                                <profile class="icon-menumob"></profile>
                                <h5 class="text-menumob">Perfil</h5>
                            </router-link>
                        </div>

                        <div class="d-block text-center box-iconmob box-comp">
                            <router-link :to="{ name: 'purchases' }" class="link-menumob tw-text-tertiary tw-no-underline" exact>
                                <purchases class="icon-menumob"></purchases>
                                <h5 class="text-menumob">Compras</h5>
                            </router-link>
                        </div>

                        <div class="d-block text-center box-iconmob box-comp">
                            <router-link :to="{ name: 'coupons' }" class="link-menumob tw-text-tertiary tw-no-underline" exact>
                                <coupons class="icon-menumob"></coupons>
                                <h5 class="text-menumob">Cupones</h5>
                            </router-link>
                        </div>

                        <div class="d-block text-center box-iconmob">
                            <router-link :to="{ name: 'favorites' }" class="link-menumob tw-text-tertiary tw-no-underline" exact>
                                <favorites_mobile class="icon-menumob"></favorites_mobile>
                                <h5 class="text-menumob">Favoritos</h5>
                            </router-link>
                        </div>
                    </VCardTitle>
                </VCard>
            </div>
            <router-view />
        </VMain>
    </VLayout>

</template>

<style scoped>

    .card-information {
        padding: 24px 32px;
        margin-top: 24px;
        border-radius: 16px;
    }

    .container-dashboard {
        padding: 3% 10%;
    }

    .profile-image {
        border-radius: 40px;
        height:80px;
        width:80px;
    }

    .name-client {
        color:#0A1B33;
        font-size: 17px;
        font-style: normal;
        font-weight: 600;
        line-height: 25px; /* 147.059% */
    }

    .text-titles {
        color: #0A1B33;
        font-size: 15px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px; /* 133.333% */
    }

    .text-subtitles {
        color: var(--Grey-1, #999);
        font-size: 15px;
        font-style: normal;
        font-weight: 400;
        line-height: 18px; /* 120% */
    }
 
    .card-profile {
        padding: 16px 32px;
        margin-top: 24px;
        border-radius: 16px;
    }

    .items-profile {
        padding: 14px 0px;
    }

    .icon-right {
        width: 20px;
    }

    .container-sidebar {
        padding: 40px 5px;
    }

    .custom-background {
        background-color: #0A1B33;
        color: #FFFFFF;
    }

    .custom-background .VListItem-title {
        font-size: 12px;
        font-style: normal;
        font-weight: 400;
        line-height: 18.2px;
    }

    .text-menu {
        color: #FFFFFF;
        font-size: 12px;
        font-style: normal;
        font-weight: 400;
        line-height: normal;
        margin: 0;    
    }

    .link-menu {
        text-decoration: none;
        color: #FFFFFF;
    }

    .items-list::v-deep(.v-list-item-title) {
        text-align: left;
        margin-left: 12px;
    }

    .router-link-exact-active {
        color: #FFC549!important;
    }

    .router-link-exact-active::v-deep(path) {
        stroke: #FFC549 !important;
    }

    .v-list-item--nav {
        padding-inline: 15px !important
    }

    .v-list-item--active::v-deep(.v-list-item__overlay) {
        opacity: 0 !important;
    }

    @media (max-width: 768px) {

        .second-header-dashboard {
            top: 120px !important;
            position: fixed !important;
            width: 100%;
            z-index: 1000;
        }
            
        .topFixedDashboard {
            top: 44px !important;
            position: fixed !important;
            width: 100%;
            z-index: 1000;
        }

        .dashboard {
            margin-top: -10px;
        }

        .container-dashboard {
            padding: 0 5%;
        }

        .card-information {
            border-radius: 0px;
            margin-top: 0px;
            padding: 24px 0px;
        }

        .icon-menumob {
            stroke: #0A1B33;
        }

        .text-menumob {
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px;
        }
        
        .link-menumob::v-deep(path) {
            stroke: #0A1B33!important;
        }

        .router-link-exact-active {
            color: #FF0090!important;
        }

        .router-link-exact-active::v-deep(path) {
            stroke: #FF0090 !important;
        }

        .box-iconmob {
            width: 33%;
        }

        .box-comp {
            border-right: 1px solid #E2F8FC;
        }

        .box-bottom {
            border-bottom: 1px solid #E2F8FC;
        }

        .pt-30 {
            padding-top: 30%;
        }
    }
</style>