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
                        <li class="n2">
                            <strong>Utilidades</strong>
                        </li>
                        <li class="tab n3">
                            <strong>Ver FAQ's</strong>&emsp;/&emsp;<code>Acceso de solo lectura a la página-faqs</code>
                        </li>                   
                        <li class="n2">
                            <strong>Productos</strong>
                        </li>
                        <li class="tab n3">
                            <strong>Ver Categorías</strong>&emsp;/&emsp;<code>Acceso de solo lectura al módulo de categorías</code>
                        </li>
                        <ul class="tab">
                            <li class="tab n3">
                                <strong>Crear Categorías</strong>&emsp;/&emsp;<code>Permite la creación de categorías</code>
                            </li>
                            <li class="tab n3">
                                <strong>Editar Categorías</strong>&emsp;/&emsp;<code>Permite la edición de categorías</code>
                            </li>
                            <li class="tab n3">
                                <strong>Eliminar Categorías</strong>&emsp;/&emsp;<code>Permite la eliminación de categorías</code>
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
                    </ul>
                </ol>
            </VCardText>
        </VCard>
    </VDialog>
</template>
