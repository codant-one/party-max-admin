<script setup>

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  role: {
    type: Object,
    required: true
  },
  readonly: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'close',
  'readonly'
])

const isSelectRolesDialog = ref(false)
const id =  ref([])
const name =  ref([])
const readonly =  ref([])
const permissions =  ref([])

watchEffect(() => {
    if (props.isDrawerOpen) {

        if (!(Object.entries(props.role).length === 0) && props.role.constructor === Object) {
            permissions.value = props.role.assignedPermissions
            id.value = props.role.id
            name.value = props.role.name
            readonly.value = props.readonly
        }
    }
})

const closeRoleDetailDialog = function(){
    emit('update:isDrawerOpen', false)
    emit('close')
    emit('readonly')
}

</script>

<template>
    <section>
    <!-- DIALOGO DE VER -->
    <VDialog
        :model-value="props.isDrawerOpen"
        max-width="600"
        persistent
        >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeRoleDetailDialog" />

        <!-- Dialog Content -->
        <VCard title="Detalle Rol">
            <VCardText>
                <VRow>
                    <VCol cols="12" >
                        <VTextField
                            v-model="id"
                            label="ID"
                            readonly
                        />
                    </VCol>
                    <VCol cols="12">
                        <VTextField
                            v-model="name"
                            label="Nombre rol"
                            readonly
                        />
                    </VCol>
                    <VCol
                        cols="12"
                        class="text-center"
                    >
                        <VBtn
                            @click="isSelectRolesDialog = true"
                        >
                            Ver Permisos del Rol
                        </VBtn>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>

     <!-- DIALOGO DE ROLES -->
     <VDialog
        v-model="isSelectRolesDialog"
        persistent
        max-width="800"
        >
        <DialogCloseBtn @click="isSelectRolesDialog = !isSelectRolesDialog" />

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
                                v-model="permissions"
                                label="administrador"
                                value="administrador"
                                :readonly="readonly"
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
                                v-model="permissions"
                                label="ver dashboard"
                                value="ver dashboard"
                                :readonly="readonly"
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
                                v-model="permissions"
                                label="ver usuarios"
                                value="ver usuarios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear usuarios"
                                value="crear usuarios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar usuarios"
                                value="editar usuarios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar usuarios"
                                value="eliminar usuarios"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Roles
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver roles"
                                value="ver roles"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear roles"
                                value="crear roles"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar roles"
                                value="editar roles"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar roles"
                                value="eliminar roles"
                                :readonly="readonly"
                            />
                        </div>
                    </div>
                </VCardText>
                <VCardTitle>
                    Productos  
                </VCardTitle>
                <VCardText>
                    <div class="ml-5">
                        <VLabel style="font-weight: bold;">
                            Categorías
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver categorías"
                                value="ver categorías"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear categorías"
                                value="crear categorías"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar categorías"
                                value="editar categorías"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar categorías"
                                value="eliminar categorías"
                                :readonly="readonly"
                            />
                        </div>
                    </div>
                </VCardText>
            </VCardText>
            <VCardText class="d-flex flex-wrap gap-3">
                <VSpacer />
                <VBtn @click="isSelectRolesDialog = false">
                    Aceptar
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
    </section>
</template>
