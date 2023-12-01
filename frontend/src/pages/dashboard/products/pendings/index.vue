<script setup>

</script>

<template>
  <section>
    <!--INICIO TEMPLATE-->

    <div>
    <!-- 👉 widgets -->
        <VCard class="mb-6">
        <VCardText>
            <VRow>
            <template
                v-for="(data, id) in widgetData"
                :key="id"
            >
                <VCol
                cols="12"
                sm="6"
                md="3"
                class="px-6"
                >
                <div
                    class="d-flex justify-space-between"
                    :class="$vuetify.display.xs
                    ? 'product-widget'
                    : $vuetify.display.sm
                        ? id < 2 ? 'product-widget' : ''
                        : ''"
                >
                    <div class="d-flex flex-column gap-y-1">
                    <div class="text-body-1 font-weight-medium text-capitalize">
                        {{ data.title }}
                    </div>

                    <h4 class="text-h4 my-1">
                        {{ data.value }}
                    </h4>

                    <div class="d-flex">
                        <div class="me-2 text-disabled text-no-wrap">
                        {{ data.desc }}
                        </div>

                        <VChip
                        v-if="data.change"
                        label
                        :color="data.change > 0 ? 'success' : 'error'"
                        >
                        {{ prefixWithPlus(data.change) }}%
                        </VChip>
                    </div>
                    </div>

                    <VAvatar
                    variant="tonal"
                    rounded
                    size="38"
                    >
                    <VIcon
                        :icon="data.icon"
                        size="28"
                    />
                    </VAvatar>
                </div>
                </VCol>
                <VDivider
                v-if="$vuetify.display.mdAndUp ? id !== widgetData.length - 1
                    : $vuetify.display.smAndUp ? id % 2 === 0
                    : false"
                vertical
                inset
                length="95"
                />
            </template>
            </VRow>
        </VCardText>
        </VCard>

        <!-- 👉 products -->
        <VCard
        title="Filters"
        class="mb-6"
        >
        <VCardText>
            <VRow>
            <!-- 👉 Select Status -->
            <VCol
                cols="12"
                sm="4"
            >
                <AppSelect
                v-model="selectedStatus"
                placeholder="Status"
                :items="status"
                clearable
                clear-icon="tabler-x"
                />
            </VCol>

            <!-- 👉 Select Category -->
            <VCol
                cols="12"
                sm="4"
            >
                <AppSelect
                v-model="selectedCategory"
                placeholder="Category"
                :items="categories"
                clearable
                clear-icon="tabler-x"
                />
            </VCol>

            <!-- 👉 Select Stock Status -->
            <VCol
                cols="12"
                sm="4"
            >
                <AppSelect
                v-model="selectedStock"
                placeholder="Stock"
                :items="stockStatus"
                clearable
                clear-icon="tabler-x"
                />
            </VCol>
            </VRow>
        </VCardText>

        <VDivider class="my-4" />

        <div class="d-flex flex-wrap gap-4 mx-5">
            <div class="d-flex align-center">
            <!-- 👉 Search  -->
            <AppTextField
                v-model="searchQuery"
                placeholder="Search Product"
                density="compact"
                style="inline-size: 200px;"
                class="me-3"
            />
            </div>

            <VSpacer />
            <div class="d-flex gap-4 flex-wrap align-center">
            <AppSelect
                v-model="itemsPerPage"
                :items="[5, 10, 20, 25, 50]"
            />
            <!-- 👉 Export button -->
            <VBtn
                variant="tonal"
                color="secondary"
                prepend-icon="tabler-upload"
            >
                Export
            </VBtn>

            <VBtn
                color="primary"
                prepend-icon="tabler-plus"
                @click="$router.push('/apps/ecommerce/product/add')"
            >
                Add Product
            </VBtn>
            </div>
        </div>

        <VDivider class="mt-4" />

        <!-- 👉 Datatable  -->

        <v-table class="text-no-wrap">
            <thead>
                <tr class="text-no-wrap">
                <th> #ID </th>
                <th> PRODUCT </th>
                <th> CATEGORY </th>
                <th class="text-end pe-4"> STOCK </th>
                <th class="text-end pe-4"> SKU </th>
                <th class="text-end pe-4"> PRICE </th>
                <th class="text-end pe-4"> QTY </th>
                <th class="text-end pe-4"> STATUS </th>
                <th class="text-end pe-4"> ACTION </th>
                
              </tr>
            </thead>
            <tbody>
                <tr>
                    <td>

                    </td>
                </tr>
            </tbody>
        </v-table>
        
        </VCard>
    </div>
    <!--FIN TEMPLATE-->
  </section>
</template>

<style scope>
    .align-right {
      text-align: right !important;
    }

    .justify-content-end {
        justify-content: end !important;
    }

    .search {
        width: 100%;
    }

    @media(min-width: 991px){
        .search {
            width: 30rem;
        }
    }

    .product-widget{
                    border-block-end: 1px solid rgba(var(--v-theme-on-surface), var(--v-border-opacity));
                    padding-block-end: 1rem;
                    }
</style>
