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
        max-width="1100"
        >
        <DialogCloseBtn @click="closeModal" />

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
                <VCardText class="pb-0">
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
                    Proveedores  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver proveedores"
                            value="ver proveedores"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="crear proveedores"
                            value="crear proveedores"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="editar proveedores"
                            value="editar proveedores"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="eliminar proveedores"
                            value="eliminar proveedores"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Clientes  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver clientes"
                            value="ver clientes"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="crear clientes"
                            value="crear clientes"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="editar clientes"
                            value="editar clientes"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="eliminar clientes"
                            value="eliminar clientes"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Pedidos  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver pedidos"
                            value="ver pedidos"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="eliminar pedidos"
                            value="eliminar pedidos"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Envíos  
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver envíos"
                            value="ver envíos"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="editar envíos"
                            value="editar envíos"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Administracion  
                </VCardTitle>
                <VCardText class="pb-0">
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
                            FAQ's
                        </VLabel>
                        <div class="ml-5">
                            <VLabel style="font-weight: bold;">
                                Categorías FAQ's
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="ver categorías-faqs"
                                    value="ver categorías-faqs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="crear categorías-faqs"
                                    value="crear categorías-faqs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="editar categorías-faqs"
                                    value="editar categorías-faqs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="eliminar categorías-faqs"
                                    value="eliminar categorías-faqs"
                                />
                            </div>
                            <VLabel style="font-weight: bold;">
                                FAQ's
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="ver faqs"
                                    value="ver faqs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="crear faqs"
                                    value="crear faqs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="editar faqs"
                                    value="editar faqs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="eliminar faqs"
                                    value="eliminar faqs"
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
                                    v-model="assignedPermissions"
                                    label="ver categorías-blogs"
                                    value="ver categorías-blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="crear categorías-blogs"
                                    value="crear categorías-blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="editar categorías-blogs"
                                    value="editar categorías-blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="eliminar categorías-blogs"
                                    value="eliminar categorías-blogs"
                                />
                            </div>
                            <VLabel style="font-weight: bold;">
                                Tag Blogs
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="ver tag-blogs"
                                    value="ver tag-blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="crear tag-blogs"
                                    value="crear tag-blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="editar tag-blogs"
                                    value="editar tag-blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="eliminar tag-blogs"
                                    value="eliminar tag-blogs"
                                />
                            </div>
                            <VLabel style="font-weight: bold;">
                                Blogs
                            </VLabel>
                            <div class="demo-space-x ml-5">
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="ver blogs"
                                    value="ver blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="crear blogs"
                                    value="crear blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="editar blogs"
                                    value="editar blogs"
                                />
                                <VCheckbox
                                    v-model="assignedPermissions"
                                    label="eliminar blogs"
                                    value="eliminar blogs"
                                />
                            </div>
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
                                v-model="assignedPermissions"
                                label="ver página-faqs"
                                value="ver página-faqs"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Blogs
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver página-blogs"
                                value="ver página-blogs"
                            />
                        </div>
                        <!-- <VLabel style="font-weight: bold;">
                            Notificaciones
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver página-notificaciones"
                                value="ver página-notificaciones"
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
                                v-model="assignedPermissions"
                                label="ver marcas"
                                value="ver marcas"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear marcas"
                                value="crear marcas"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar marcas"
                                value="editar marcas"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar marcas"
                                value="eliminar marcas"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Tag Productos
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver tag-productos"
                                value="ver tag-productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear tag-productos"
                                value="crear tag-productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar tag-productos"
                                value="editar tag-productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar tag-productos"
                                value="eliminar tag-productos"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Categorías
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver categorías"
                                value="ver categorías"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear categorías"
                                value="crear categorías"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar categorías"
                                value="editar categorías"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar categorías"
                                value="eliminar categorías"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Productos
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver productos"
                                value="ver productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear productos"
                                value="crear productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar productos"
                                value="editar productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar productos"
                                value="eliminar productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver ordenar-productos"
                                value="ver ordenar-productos"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Productos Pendientes
                        </VLabel> 
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver productos-pendientes"
                                value="ver productos-pendientes"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="aprobar productos"
                                value="aprobar productos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="rechazar productos"
                                value="rechazar productos"
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
