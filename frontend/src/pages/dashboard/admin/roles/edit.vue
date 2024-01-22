<script setup>

import permissions from './permissions.vue'

import { requiredValidator } from '@/@core/utils/validators'
import { useRolesStores } from '@/stores/useRoles'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  role: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'close',
  'alert',
  'data',
  'readonly'
])

const rolesStores = useRolesStores()

const refFormEdit = ref()

const isRolSelectedDialog = ref(false)
const id = ref('')
const name = ref('')
const assignedPermissions = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

watchEffect(fetchData)

async function fetchData() {
    if (props.isDrawerOpen) {

        if (!(Object.entries(props.role).length === 0) && props.role.constructor === Object) {
            id.value = props.role.id
            name.value = props.role.name
            assignedPermissions.value = props.role.assignedPermissions
      }
  }
}

const closeRoleEditDialog = function(){
    emit('update:isDrawerOpen', false)
    emit('close')
    emit('readonly')

    nextTick(() => {
        refFormEdit.value?.reset()
        refFormEdit.value?.resetValidation()
        name.value = ''
        assignedPermissions.value = []
    })
}

const getPermissions = function(permissions){
    assignedPermissions.value = permissions
}

const onSubmitEdit = () =>{
  refFormEdit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {

        let data = {
            name: name.value,
            permissions: assignedPermissions.value
        }

        rolesStores.updateRole(data, id.value)
            .then(response => {
                closeRoleEditDialog()

                window.scrollTo(0, 0)
                
                advisor.value.show = true
                advisor.value.type = 'success'
                advisor.value.message = 'Rol actualizado!'

                emit('alert', advisor)
                emit('data')

                nextTick(() => {
                    refFormEdit.value?.reset()
                    refFormEdit.value?.resetValidation()
                    name.value = ''
                    assignedPermissions.value = []
                })

                setTimeout(() => {
                    advisor.value.show = false
                    advisor.value.type = ''
                    advisor.value.message = ''
                    emit('alert', advisor)
                }, 5000)

            }).catch(error => {
                closeRoleEditDialog()
                window.scrollTo(0, 0)
                
                advisor.value.show = true
                advisor.value.type = 'error'

                if (error.feedback === 'params_validation_failed') {
                    if(error.message.hasOwnProperty('name'))
                        advisor.value.message = error.message.name[0]
                    else if(error.message.hasOwnProperty('permissions'))
                        advisor.value.message = error.message.permissions[0]
                } else {
                    advisor.value.message = 'Se ha producido un error...! (Server Error)'
                }

                emit('alert', advisor)
                emit('data')

                setTimeout(() => {
                    advisor.value.show = false
                    advisor.value.type = ''
                    advisor.value.message = ''
                    emit('alert', advisor)
                }, 5000)  
            })
    }
     
  })
}

</script>

<template>
    <!-- DIALOGO DE EDITAR -->
    <VDialog
        :model-value="props.isDrawerOpen"
        max-width="600"
        persistent
        >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeRoleEditDialog" />

        <!-- Dialog Content -->
        <VCard title="Editar Rol">
            <VDivider class="mt-4"/>
            <VForm
                ref="refFormEdit"
                @submit.prevent="onSubmitEdit"
            >
                <VCardText>
                    <VRow>
                        <VCol cols="12">
                            <VTextField
                                v-model="id"
                                label="ID rol"
                                readonly
                            />
                        </VCol>
                        <VCol cols="12">
                            <VTextField
                                v-model="name"
                                label="Nombre rol"
                                :rules="[requiredValidator]"
                            />
                        </VCol>
                        <VCol
                            cols="12"
                            class="text-center"
                        >
                            <VBtn @click="isRolSelectedDialog = true">
                            Editar Permisos del Rol
                            </VBtn>
                        </VCol>
                    </VRow>
                    <VCardText class="d-flex justify-end gap-3 flex-wrap">
                        <VBtn
                            color="secondary"
                            variant="tonal"
                            @click="closeRoleEditDialog"
                        >
                            Cancelar
                        </VBtn>
                        <VBtn
                            
                            type="submit"
                        >
                            Editar
                        </VBtn>
                    </VCardText>
                </VCardText>
            </VForm>
        </VCard>
    </VDialog>

    <permissions 
        v-model:isDrawerOpen="isRolSelectedDialog"
        :role="role"
        @permissions="getPermissions"
    />
</template>
<route lang="yaml">
    meta:
      action: editar
      subject: roles
  </route>
