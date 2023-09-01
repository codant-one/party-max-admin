<script setup>

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  role: {
    type: Object,
    required: false
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'permissions'
])

const assignedPermissions =  ref([])

watchEffect(() => {
    if (props.isDrawerOpen && props.role) {

        if (!(Object.entries(props.role).length === 0) && props.role.constructor === Object) {
            assignedPermissions.value = props.role.assignedPermissions
        }
    }
})

const closeModal = function(){
    emit('update:isDrawerOpen', false)
    emit('permissions', assignedPermissions.value)
}

</script>

<template>
    <!-- DIALOGO DE ROLES CREAR -->
    <VDialog
        :model-value="props.isDrawerOpen"
        persistent
        max-width="800"
        >
        <DialogCloseBtn @click="closeModal" />

        <VCard title="Permisos">
            <VCardText>
                <VCardTitle>
                    Administrador General  
                </VCardTitle>
                <VCardText>
                    <div class="ml-5">
                        <VLabel style="font-weight: bold;">
                            Administrador
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="administrador"
                                value="administrador"
                            />
                        </div>
                    </div>
                </VCardText>
                <VCardTitle>
                    General  
                </VCardTitle>
                <VCardText>
                    <div class="ml-5">
                        <VLabel style="font-weight: bold;">
                            Panel de Control
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver dashboard"
                                value="ver dashboard"
                            />
                        </div>
                    </div>
                </VCardText>
                <VCardTitle>
                    Administracion  
                </VCardTitle>
                <VCardText>
                    <div class="ml-5">
                        <VLabel style="font-weight: bold;">
                            Usuarios
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver usuarios"
                                value="ver usuarios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear usuarios"
                                value="crear usuarios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar usuarios"
                                value="editar usuarios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar usuarios"
                                value="eliminar usuarios"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Roles
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver roles"
                                value="ver roles"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear roles"
                                value="crear roles"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar roles"
                                value="editar roles"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar roles"
                                value="eliminar roles"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Afiliados
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver afiliados"
                                value="ver afiliados"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Capturador
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver capturador-enlaces"
                                value="ver capturador-enlaces"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Mis Enlaces
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver mis-enlaces"
                                value="ver mis-enlaces"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear mis-enlaces"
                                value="crear mis-enlaces"
                            />
                        </div>
                    </div>
                </VCardText>
            </VCardText>
            <VCardText class="d-flex flex-wrap gap-3">
                <VSpacer />
                <VBtn @click="closeModal">
                    Aceptar
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
