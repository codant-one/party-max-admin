<script setup>
import { avatarText } from '@/@core/utils/formatters'
import { initialAbility } from '@/plugins/casl/ability'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import { useAuthStores } from '@/stores/useAuth'

const authStores = useAuthStores()
const router = useRouter()
const ability = useAppAbility()

const userData = field =>{
  let values = JSON.parse(localStorage.getItem('user_data') || 'null')

  if(values && field === 'avatar') {
    return values.avatar
  }
  if(values && field === 'name') {
    return values.name
  }

  return false
}

const logout = () => {

  authStores.logout()
    .then(response => {
      // Remove "user_data" from localStorage
      localStorage.removeItem('user_data')

      // Remove "accessToken" from localStorage
      localStorage.removeItem('accessToken')
      
      // Remove "userAbilities" from localStorage
      localStorage.removeItem('userAbilities')

      // Reset ability to initial ability
      ability.update(initialAbility)
      router.push('/login')

  })

}
</script>

<template>
  <VBadge
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    bordered
    color="success"
  >
    <VAvatar
      class="cursor-pointer"
      :color="userData('avatar') ? 'default' : 'primary'"
      :variant="userData('avatar') ? 'outlined' : 'tonal'"
    >
      <VImg
        v-if="userData('avatar')"
        style="border-radius: 50%;"
        :src="userData('avatar')"
      />
      <span
        v-else
        class="font-weight-semibold"
      >
        {{ avatarText(userData('name')) }}
      </span>

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="14px"
      >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    :color="userData('avatar') ? 'default' : 'primary'" 
                    :variant="userData('avatar') ? 'outlined' : 'tonal'"
                  >
                    <VImg
                      v-if="userData('avatar')"
                      style="border-radius: 50%;"
                      :src="userData('avatar')"
                    />
                    <span
                      v-else
                      class="font-weight-semibold"
                    >
                      {{ avatarText(userData('name')) }}
                    </span>
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ userData('name') }}
            </VListItemTitle>
            <VListItemSubtitle />
          </VListItem>

          <VDivider class="my-2" />

          
          <!--  👉 Profile -->
          <VListItem :to="{ name: 'dashboard-profile' }">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-user"
                size="22"
              />
            </template>

            <VListItemTitle>Perfil</VListItemTitle>
          </VListItem>
 
          <!-- 👉 Logout -->
          <VListItem
            link
            @click="logout"
          >
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-logout"
                size="22"
              />
            </template>

            <VListItemTitle>Salir</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
