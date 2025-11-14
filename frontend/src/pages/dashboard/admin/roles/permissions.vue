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
                    Categorías
                </VCardTitle>
                <div class="ml-5">
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
                </div>
                <VCardTitle>
                    Calendario
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver calendario"
                            value="ver calendario"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="eliminar calendario"
                            value="eliminar calendario"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Ventas
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver ventas"
                            value="ver ventas"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Facturación
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver facturas"
                            value="ver facturas"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="crear facturas"
                            value="crear facturas"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="editar facturas"
                            value="editar facturas"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="eliminar facturas"
                            value="eliminar facturas"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Remisiones
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver remisiones"
                            value="ver remisiones"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="editar remisiones"
                            value="editar remisiones"
                        />
                    </div>
                </div>
                <VCardTitle>
                    Cotizaciones
                </VCardTitle>
                <div class="ml-5">
                    <div class="demo-space-x ml-5">
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="ver cotizaciones"
                            value="ver cotizaciones"
                        />
                        <VCheckbox
                            v-model="assignedPermissions"
                            label="eliminar cotizaciones"
                            value="eliminar cotizaciones"
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
                            Home imágenes
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver home-imágenes"
                                value="ver home-imágenes"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear home-imágenes"
                                value="crear home-imágenes"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar home-imágenes"
                                value="editar home-imágenes"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar home-imágenes"
                                value="eliminar home-imágenes"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Banners
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver banners"
                                value="ver banners"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear banners"
                                value="crear banners"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar banners"
                                value="editar banners"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar banners"
                                value="eliminar banners"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Registro de ips
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver ips"
                                value="ver ips"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="bloquear ips"
                                value="bloquear ips"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar ips"
                                value="eliminar ips"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Cupones
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver cupones"
                                value="ver cupones"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar cupones"
                                value="eliminar cupones"
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
                        <VLabel style="font-weight: bold;">
                            Atributos
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver atributos"
                                value="ver atributos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear atributos"
                                value="crear atributos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar atributos"
                                value="editar atributos"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar atributos"
                                value="eliminar atributos"
                            />
                        </div>
                    </div>
                </VCardText>
                <VCardTitle>
                    Capacitación  
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
                        </div>
                        <VLabel style="font-weight: bold;">
                            Inventario
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver inventario"
                                value="ver inventario"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Stock
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver stock"
                                value="ver stock"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar stock"
                                value="editar stock"
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
                        <VLabel style="font-weight: bold;">
                            Ordenar Productos
                        </VLabel> 
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver ordenar-productos"
                                value="ver ordenar-productos"
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
                                v-model="assignedPermissions"
                                label="ver marcas-servicios"
                                value="ver marcas-servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear marcas-servicios"
                                value="crear marcas-servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar marcas-servicios"
                                value="editar marcas-servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar marcas-servicios"
                                value="eliminar marcas-servicios"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Tag Servicios
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver tag-servicios"
                                value="ver tag-servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear tag-servicios"
                                value="crear tag-servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar tag-servicios"
                                value="editar tag-servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar tag-servicios"
                                value="eliminar tag-servicios"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Servicios
                        </VLabel>
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver servicios"
                                value="ver servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="crear servicios"
                                value="crear servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="editar servicios"
                                value="editar servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="eliminar servicios"
                                value="eliminar servicios"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Servicios Pendientes
                        </VLabel> 
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver servicios-pendientes"
                                value="ver servicios-pendientes"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="aprobar servicios"
                                value="aprobar servicios"
                            />
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="rechazar servicios"
                                value="rechazar servicios"
                            />
                        </div>
                        <VLabel style="font-weight: bold;">
                            Ordenar Servicios
                        </VLabel> 
                        <div class="demo-space-x ml-5">
                            <VCheckbox
                                v-model="assignedPermissions"
                                label="ver ordenar-servicios"
                                value="ver ordenar-servicios"
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
