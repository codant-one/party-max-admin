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
        <VCard title="Detalle Rol" class="role">
            <VDivider class="mt-4"/>
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
        max-width="1100"
        >
        <DialogCloseBtn @click="isSelectRolesDialog = !isSelectRolesDialog" />

        <VCard title="Permisos">
            <VDivider class="mt-4"/>
            <VCardText class="pb-0">
                <VCardTitle>
                    Administrador General  
                </VCardTitle>
                <VCardText class="pb-0">
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
                <VCardText class="pb-0">
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
                    Proveedores  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="permissions"
                            label="ver proveedores"
                            value="ver proveedores"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="crear proveedores"
                            value="crear proveedores"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="editar proveedores"
                            value="editar proveedores"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="eliminar proveedores"
                            value="eliminar proveedores"
                            :readonly="readonly"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Clientes  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="permissions"
                            label="ver clientes"
                            value="ver clientes"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="crear clientes"
                            value="crear clientes"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="editar clientes"
                            value="editar clientes"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="eliminar clientes"
                            value="eliminar clientes"
                            :readonly="readonly"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Pedidos  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="permissions"
                            label="ver pedidos"
                            value="ver pedidos"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="eliminar pedidos"
                            value="eliminar pedidos"
                            :readonly="readonly"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Envíos  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="permissions"
                            label="ver envíos"
                            value="ver envíos"
                            :readonly="readonly"
                        />
                        <VCheckbox
                            v-model="permissions"
                            label="editar envíos"
                            value="editar envíos"
                            :readonly="readonly"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Categorías
                </VCardTitle>
                    <div class="ml-5">
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
                <VCardTitle>
                    Administración  
                </VCardTitle>
                <VCardText class="pb-0">
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
                        <VLabel style="font-weight: bold;">
                            FAQ's
                        </VLabel>
                        <div class="ml-5">
                            <VLabel style="font-weight: bold;">
                                Categorías FAQ's
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="permissions"
                                    label="ver categorías-faqs"
                                    value="ver categorías-faqs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="crear categorías-faqs"
                                    value="crear categorías-faqs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="editar categorías-faqs"
                                    value="editar categorías-faqs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="eliminar categorías-faqs"
                                    value="eliminar categorías-faqs"
                                    :readonly="readonly"
                                />
                            </div>
                            <VLabel style="font-weight: bold;">
                                FAQ's
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="permissions"
                                    label="ver faqs"
                                    value="ver faqs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="crear faqs"
                                    value="crear faqs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="editar faqs"
                                    value="editar faqs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="eliminar faqs"
                                    value="eliminar faqs"
                                    :readonly="readonly"
                                />
                            </div>
                        </div>
                        <VLabel style="font-weight: bold;">
                            Blogs
                        </VLabel>
                        <div class="ml-5">
                            <VLabel style="font-weight: bold;">
                                Categorías Blogs
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="permissions"
                                    label="ver categorías-blogs"
                                    value="ver categorías-blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="crear categorías-blogs"
                                    value="crear categorías-blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="editar categorías-blogs"
                                    value="editar categorías-blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="eliminar categorías-blogs"
                                    value="eliminar categorías-blogs"
                                    :readonly="readonly"
                                />
                            </div>
                            <VLabel style="font-weight: bold;">
                                Tag Blogs
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="permissions"
                                    label="ver tag-blogs"
                                    value="ver tag-blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="crear tag-blogs"
                                    value="crear tag-blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="editar tag-blogs"
                                    value="editar tag-blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="eliminar tag-blogs"
                                    value="eliminar tag-blogs"
                                    :readonly="readonly"
                                />
                            </div>
                            <VLabel style="font-weight: bold;">
                                Blogs
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="permissions"
                                    label="ver blogs"
                                    value="ver blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="crear blogs"
                                    value="crear blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="editar blogs"
                                    value="editar blogs"
                                    :readonly="readonly"
                                />
                                <VCheckbox
                                    v-model="permissions"
                                    label="eliminar blogs"
                                    value="eliminar blogs"
                                    :readonly="readonly"
                                />
                            </div>
                        </div>
                        <VLabel style="font-weight: bold;">
                            Atributos
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver atributos"
                                value="ver atributos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear atributos"
                                value="crear atributos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar atributos"
                                value="editar atributos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar atributos"
                                value="eliminar atributos"
                                :readonly="readonly"
                            />
                        </div>
                    </div>
                </VCardText>
                <VCardTitle>
                    Utilidades  
                </VCardTitle>
                <VCardText class="pb-0">
                    <div class="ml-5">
                        <VLabel style="font-weight: bold;">
                            FAQ's
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver página-faqs"
                                value="ver página-faqs"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Blogs
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver página-blogs"
                                value="ver página-blogs"
                                :readonly="readonly"
                            />
                        </div>
                        <!-- <VLabel style="font-weight: bold;">
                            Notificaciones
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver página-notificaciones"
                                value="ver página-notificaciones"
                                :readonly="readonly"
                            />
                        </div> -->
                    </div>
                </VCardText>
                <VCardTitle>
                    Productos  
                </VCardTitle>
                <VCardText class="pb-0">
                    <div class="ml-5">
                        <VLabel style="font-weight: bold;">
                            Marcas
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver marcas"
                                value="ver marcas"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear marcas"
                                value="crear marcas"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar marcas"
                                value="editar marcas"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar marcas"
                                value="eliminar marcas"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Tag Productos
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver tag-productos"
                                value="ver tag-productos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear tag-productos"
                                value="crear tag-productos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar tag-productos"
                                value="editar tag-productos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar tag-productos"
                                value="eliminar tag-productos"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Productos
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver productos"
                                value="ver productos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear productos"
                                value="crear productos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar productos"
                                value="editar productos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar productos"
                                value="eliminar productos"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Productos Pendientes
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver productos-pendientes"
                                value="ver productos-pendientes"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="aprobar productos"
                                value="aprobar productos"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="rechazar productos"
                                value="rechazar productos"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Ordenar Productos
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver ordenar-productos"
                                value="ver ordenar-productos"
                                :readonly="readonly"
                            />
                        </div>
                    </div>
                </VCardText>

                <VCardTitle>
                    Servicios  
                </VCardTitle>
                <VCardText class="pb-0">
                    <div class="ml-5">
                        <VLabel style="font-weight: bold;">
                            Marcas
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver marcas-servicios"
                                value="ver marcas-servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear marcas-servicios"
                                value="crear marcas-servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar marcas-servicios"
                                value="editar marcas-servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar marcas-servicios"
                                value="eliminar marcas-servicios"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Tag Servicios
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver tag-servicios"
                                value="ver tag-servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear tag-servicios"
                                value="crear tag-servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar tag-servicios"
                                value="editar tag-servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar tag-servicios"
                                value="eliminar tag-servicios"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Servicios
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver servicios"
                                value="ver servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="crear servicios"
                                value="crear servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="editar servicios"
                                value="editar servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="eliminar servicios"
                                value="eliminar servicios"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Servicios Pendientes
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver servicios-pendientes"
                                value="ver servicios-pendientes"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="aprobar servicios"
                                value="aprobar servicios"
                                :readonly="readonly"
                            />
                            <VCheckbox
                                v-model="permissions"
                                label="rechazar servicios"
                                value="rechazar servicios"
                                :readonly="readonly"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Ordenar Sevicios
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="permissions"
                                label="ver ordenar-servicios"
                                value="ver ordenar-servicios"
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

<style lang="scss">
    .v-dialog .v-overlay__content > .v-card .role {
        overflow-y: hidden !important;
    }
</style>
