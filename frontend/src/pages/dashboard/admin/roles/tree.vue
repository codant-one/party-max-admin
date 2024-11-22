<script setup>


const isTreeDialog = ref(false)

const closeTreeDialog  = function(){
    isTreeDialog.value = false
}

</script>

<template>
    <!-- 👉 arbol de permisos rol -->
    <VDialog
        v-model="isTreeDialog"
        max-width="1200"
        >
        <!-- Dialog Activator -->
        <template #activator="{ props }">
            <VBtn
                v-if="$can('crear','roles')"
                v-bind="props"
                prepend-icon="tabler-sitemap"
                >
                Mapa de permisos
            </VBtn>
        </template>

        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeTreeDialog " />

        <!-- Dialog Content -->
        <VCard title="Mapa de Permisos">
            <VDivider class="mt-4"/>
            <VCardText>
                <ol id="permisos-lista">
                    <li class="n1">
                        <strong>Administrador</strong>&emsp;/&emsp;<code>Acceso a toda la aplicacion</code>
                    </li>
                    <li class="n1">
                        <strong>Ver Dashboard</strong>&emsp;/&emsp;<code>Permite ver el panel de control</code>
                    </li>
                    <ul class="tab">
                        <li class="n2">
                            <strong>Proveedores</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de proveedores</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Proveedores</strong>&emsp;/&emsp;<code>Permite la creación de proveedores</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Proveedores</strong>&emsp;/&emsp;<code>Permite la edición de proveedores</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Proveedores</strong>&emsp;/&emsp;<code>Permite la eliminación de proveedores</code>
                            </li>
                        </ul>
                        <li class="n2">
                            <strong>Clientes</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de clientes</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Clientes</strong>&emsp;/&emsp;<code>Permite la creación de clientes</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Clientes</strong>&emsp;/&emsp;<code>Permite la edición de clientes</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Clientes</strong>&emsp;/&emsp;<code>Permite la eliminación de clientes</code>
                            </li>
                        </ul>
                        <li class="n2">
                            <strong>Pedidos</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de pedidos</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Eliminar Pedidos</strong>&emsp;/&emsp;<code>Permite la eliminación de pedidos</code>
                            </li>
                        </ul>
                        <li class="n2">
                            <strong>Envíos</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de envíos</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Editar envíos</strong>&emsp;/&emsp;<code>Permite la actualización de estados de los envíos</code>
                            </li>
                        </ul>
                        <li class="n2">
                            <strong>Categorías</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de categorías de productos y servicios</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Categorías</strong>&emsp;/&emsp;<code>Permite la creación de categorías de productos y servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Categorías</strong>&emsp;/&emsp;<code>Permite la edición de categorías de productos y servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Categorías</strong>&emsp;/&emsp;<code>Permite la eliminación de categorías de productos y servicios</code>
                            </li>
                        </ul>
                        <li class="n2">
                            <strong>Calendario</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de calendario</code>
                        </li>
                        <li class="n2">
                            <strong>Ventas</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de ventas</code>
                        </li>
                        <li class="n2">
                            <strong>Remisiones</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de remisiones</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Editar remisiones</strong>&emsp;/&emsp;<code>Permite la actualización de las remisiones</code>
                            </li>
                        </ul>
                        <li class="n2">
                            <strong>Administración</strong>
                        </li>
                        <li class="tab n3">
                            <strong>Ver Usuarios</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de usuarios</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Usuarios</strong>&emsp;/&emsp;<code>Permite la creación de usuarios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Usuarios</strong>&emsp;/&emsp;<code>Permite la edición de usuarios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Usuarios</strong>&emsp;/&emsp;<code>Permite la eliminación de usuarios</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ver Roles</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de roles</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Roles</strong>&emsp;/&emsp;<code>Permite la creación de roles</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Roles</strong>&emsp;/&emsp;<code>Permite la edición de roles</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Roles</strong>&emsp;/&emsp;<code>Permite la eliminación de roles</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>FAQ's</strong>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Ver Categorías de FAQ's</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de categorías de FAQ's</code>
                            </li>
                            <ul class="tab">
                                <li class="tab n3">
                                    <strong>Crear Categorías de FAQ's</strong>&emsp;/&emsp;<code>Permite la creación de categorías de FAQ's</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Editar Categorías de FAQ's</strong>&emsp;/&emsp;<code>Permite la edición de categorías de FAQ's</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Eliminar Categorías de FAQ's</strong>&emsp;/&emsp;<code>Permite la eliminación de categorías de FAQ's</code>
                                </li>
                            </ul>
                            <li class="tab n3">
                                <strong>Ver FAQ's</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de FAQ's</code>
                            </li>
                            <ul class="tab">
                                <li class="tab n3">
                                    <strong>Crear FAQ's</strong>&emsp;/&emsp;<code>Permite la creación de FAQ's</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Editar FAQ's</strong>&emsp;/&emsp;<code>Permite la edición de FAQ's</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Eliminar FAQ's</strong>&emsp;/&emsp;<code>Permite la eliminación de FAQ's</code>
                                </li>
                            </ul>
                        </ul>   
                        <li class="tab n3">
                            <strong>Blogs</strong>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Ver Categorías de Blogs</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de categorías de blogs</code>
                            </li>
                            <ul class="tab">
                                <li class="tab n3">
                                    <strong>Crear Categorías de Blogs</strong>&emsp;/&emsp;<code>Permite la creación de categorías de blogs</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Editar Categorías de Blogs</strong>&emsp;/&emsp;<code>Permite la edición de categorías de blogs</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Eliminar Categorías de Blogs</strong>&emsp;/&emsp;<code>Permite la eliminación de categorías de blogs</code>
                                </li>
                            </ul>
                            <li class="tab n3">
                                <strong>Ver Tags de Blogs</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de tags de blogs</code>
                            </li>
                            <ul class="tab">
                                <li class="tab n3">
                                    <strong>Crear Tags de Blogs</strong>&emsp;/&emsp;<code>Permite la creación de tags de blogs</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Editar Tags de Blogs</strong>&emsp;/&emsp;<code>Permite la edición de tags de blogs</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Eliminar Tags de Blogs</strong>&emsp;/&emsp;<code>Permite la eliminación de tags de blogs</code>
                                </li>
                            </ul>
                            <li class="tab n3">
                                <strong>Ver Blogs</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de blogs</code>
                            </li>
                            <ul class="tab">
                                <li class="tab n3">
                                    <strong>Crear Blogs</strong>&emsp;/&emsp;<code>Permite la creación de blogs</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Editar Blogs</strong>&emsp;/&emsp;<code>Permite la edición de blogs</code>
                                </li>
                                <li class="tab n3">
                                    <strong>Eliminar Blogs</strong>&emsp;/&emsp;<code>Permite la eliminación de blogs</code>
                                </li>
                            </ul>
                        </ul> 
                        <li class="tab n3">
                            <strong>Atributos</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de atributos</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Atributos</strong>&emsp;/&emsp;<code>Permite la creación de atributos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Atributos</strong>&emsp;/&emsp;<code>Permite la edición de atributos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Atributos</strong>&emsp;/&emsp;<code>Permite la eliminación de atributos</code>
                            </li>
                        </ul>
                        <li class="n2">
                            <strong>Capacitación</strong>
                        </li>
                        <li class="tab n3">
                            <strong>Ver FAQ's</strong>&emsp;/&emsp;<code>Acceso de solo lectura a la página-faqs</code>
                        </li>   
                        <li class="tab n3">
                            <strong>Ver Blogs</strong>&emsp;/&emsp;<code>Acceso de solo lectura a la página-blogs</code>
                        </li>   
                        <!-- <li class="tab n3">
                            <strong>Ver Notificaciones</strong>&emsp;/&emsp;<code>Acceso de solo lectura a la página-notificaciones</code>
                        </li>               -->
                        <li class="n2">
                            <strong>Productos</strong>
                        </li>
                        <li class="tab n3">
                            <strong>Ver Marcas</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de marcas de productos</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Marcas</strong>&emsp;/&emsp;<code>Permite la creación de marcas de productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Marcas</strong>&emsp;/&emsp;<code>Permite la edición de marcas de productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Marcas</strong>&emsp;/&emsp;<code>Permite la eliminación de marcas de productos</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ver Tags de Productos</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de tags de productos</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Tags de Productos</strong>&emsp;/&emsp;<code>Permite la creación de tags de productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Tags de Productos</strong>&emsp;/&emsp;<code>Permite la edición de tags de productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Tags de Productos</strong>&emsp;/&emsp;<code>Permite la eliminación de tags de productos</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ver Productos</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de productos</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Productos</strong>&emsp;/&emsp;<code>Permite la creación de productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Productos</strong>&emsp;/&emsp;<code>Permite la edición de productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Productos</strong>&emsp;/&emsp;<code>Permite la eliminación de productos</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Inventario</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de inventario</code>
                        </li>
                        <li class="tab n3">
                            <strong>Stock pendientes</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de stock pendientes</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Editar Stock</strong>&emsp;/&emsp;<code>Permite la edición de stock de productos pendientes por aprobar</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ver Productos Pendientes</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de productos pendientes</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Aprobar Productos</strong>&emsp;/&emsp;<code>Permite la aprobación de productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Rechazar Productos</strong>&emsp;/&emsp;<code>Permite rechazar productos</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Productos</strong>&emsp;/&emsp;<code>Permite la eliminación de productos</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ordenar Productos</strong>&emsp;/&emsp;<code>Permite ordenar productos</code>
                        </li>
                        <li class="n2">
                            <strong>Servicios</strong>
                        </li>
                        <li class="tab n3">
                            <strong>Ver Marcas</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de marcas de servicios</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Marcas</strong>&emsp;/&emsp;<code>Permite la creación de marcas de servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Marcas</strong>&emsp;/&emsp;<code>Permite la edición de marcas de servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Marcas</strong>&emsp;/&emsp;<code>Permite la eliminación de marcas de servicios</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ver Tags de Servicios</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de tags de servicios</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Tags de Servicios</strong>&emsp;/&emsp;<code>Permite la creación de tags de servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Tags de Servicios</strong>&emsp;/&emsp;<code>Permite la edición de tags de servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Tags de Servicios</strong>&emsp;/&emsp;<code>Permite la eliminación de tags de servicios</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ver Servicios</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de servicios</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Servicios</strong>&emsp;/&emsp;<code>Permite la creación de servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Servicios</strong>&emsp;/&emsp;<code>Permite la edición de servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Servicios</strong>&emsp;/&emsp;<code>Permite la eliminación de servicios</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ver Servicios Pendientes</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de servicios pendientes</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Aprobar Servicios</strong>&emsp;/&emsp;<code>Permite la aprobación de servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Rechazar Servicios</strong>&emsp;/&emsp;<code>Permite rechazar servicios</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Servicios</strong>&emsp;/&emsp;<code>Permite la eliminación de servicios</code>
                            </li>
                        </ul>
                        <li class="tab n3">
                            <strong>Ordenar Servicios</strong>&emsp;/&emsp;<code>Permite ordenar servicios</code>
                        </li>
                    </ul>
                </ol>
            </VCardText>
        </VCard>
    </VDialog>
</template>
