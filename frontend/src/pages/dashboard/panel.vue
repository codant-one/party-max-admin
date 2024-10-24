<script setup>

import { useDashboardStores } from '@/stores/useDashboard'
import { useSuppliersStores } from '@/stores/useSuppliers'
import { formatNumber } from '@/@core/utils/formatters'

import VueApexCharts from 'vue3-apexcharts'
import Congratulations from "@/components/dashboard/Congratulations.vue";
import Statistics from "@/components/dashboard/Statistics.vue";
import Earning from "@/components/dashboard/Earning.vue";
import Products from "@/components/dashboard/Products.vue";
import Services from "@/components/dashboard/Services.vue";
import Orders from "@/components/dashboard/Orders.vue";

const dashboardStores = useDashboardStores()
const suppliersStores = useSuppliersStores()

const userDataJ = ref('')
const rol = ref(null)
const name = ref('')
const store = ref(null)
const data = ref(null)

const isRequestOngoing = ref(true)

const donutChartColors = {
  donut: {
    series1: '#FF0090',
    series2: '#ffdbee',
  },
}

const timeSpendingChartConfig = ref(null)
const timeSpendingChartSeries = ref(null)


watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  const userData = localStorage.getItem('user_data')
    
  userDataJ.value = JSON.parse(userData)
  name.value = userDataJ.value.name + " " + userDataJ.value.last_name
  rol.value = userDataJ.value.roles[0].name
    
  store.value = userDataJ.value.user_details.store_name ?? (userDataJ.value.supplier?.company_name ?? null)

  await dashboardStores.fetchData();

  data.value = dashboardStores.getData

  if(rol.value === 'Proveedor') {
    const total = Number(data.value.supplier.account.balance ?? 0)
    const retail_sales_amount = '$' + formatNumber(data.value.supplier.account.retail_sales_amount) ?? '0.00'
    const wholesale_sales_amount = '$' + formatNumber(data.value.supplier.account.wholesale_sales_amount) ?? '0.00'
    const percentage_retail = (Number(data.value.supplier.account.retail_sales_amount ?? 0) * 100)/total ?? 0
    const percentage_wholesale = (Number(data.value.supplier.account.wholesale_sales_amount ?? 0) * 100)/total  ?? 0

    console.log('data', data.value.supplier)
    console.log('percentage_retail', percentage_retail)
    timeSpendingChartConfig.value = {
      chart: {
        height: 157,
        width: 130,
        parentHeightOffset: 0,
        type: 'donut',
      },
      labels: [retail_sales_amount, wholesale_sales_amount],
      colors: [
        donutChartColors.donut.series1,
        donutChartColors.donut.series2
      ],
      stroke: { width: 0 },
      dataLabels: {
        enabled: false,
        formatter(val) {
          return `${ Number.parseFloat(val).toFixed(1) }%`
        },
      },
      legend: { show: false },
      tooltip: { 
        enabled: true,
        y: {
          formatter: function (val) {
            return `${Number.parseFloat(val).toFixed(1)}%`;  // Agregar '%' en el tooltip
          }
        }
      },
      grid: { padding: { top: 0 } },
      plotOptions: {
        pie: {
          donut: {
            size: '75%',
            labels: {
              show: true,
              value: {
                fontSize: '15px',
                color: 'rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity))',
                fontWeight: 500,
                offsetY: -15,
                formatter(val) {
                  return `${ Number.parseFloat(val).toFixed(1) }%`
                },
              },
              name: { offsetY: 20 },
              total: {
                show: true,
                fontSize: '10px',
                label: 'Total',
                color: 'rgba(var(--v-theme-on-background), var(--v-disabled-opacity))',
                formatter() {
                  return '$'+ (formatNumber(data.value.supplier.account.balance ?? '0.00'))
                },
              },
            },
          },
        },
      }
    }

    timeSpendingChartSeries.value = [percentage_retail, percentage_wholesale]

    let retail_sales = parseFloat(data.value.supplier.retail_sales ?? 0)
    let wholesale_sales = parseFloat(data.value.supplier.wholesale_sales ?? 0)
    let total_sales = retail_sales + wholesale_sales
    let commission_retail = retail_sales * (parseFloat(data.value.supplier.commission ?? 0) / 100)
    let commission_wholesale = wholesale_sales * (parseFloat(data.value.supplier.wholesale_commission ?? 0) / 100) 
        
    let total_balance = total_sales - commission_retail - commission_wholesale

    let data_ = {
        balance: total_balance,
        retail_sales_amount: retail_sales - commission_retail,
        wholesale_sales_amount: wholesale_sales - commission_wholesale,
        type_commission: 2
    }

    let response = await suppliersStores.updateBalance(data.value.supplier.id, data_)

    data.value.supplier.account.balance = response.data.data.supplierAccount.balance
    data.value.supplier.account.retail_sales_amount = response.data.data.supplierAccount.retail_sales_amount
    data.value.supplier.account.wholesale_sales_amount = response.data.data.supplierAccount.wholesale_sales_amount
  }

  isRequestOngoing.value = false
}

</script>

<template>
  <section>
    <VRow>
      <VDialog
        v-model="isRequestOngoing"
        width="300"
        persistent>
        <VCard
          color="primary"
          width="300">
          <VCardText class="pt-3">
            Cargando
            <VProgressLinear
              indeterminate
              color="white"
              class="mb-0"
             />
          </VCardText>
        </VCard>
      </VDialog>
    </VRow>

    <VRow class="py-6 px-md-6 px-2" v-if="rol === 'Proveedor' && data">
      <VCol
        cols="12"
        md="8"
        :class="$vuetify.display.mdAndUp ? 'border-e' : 'border-b'"
      >
        <div class="pe-3">
          <h3 class="text-h3 text-high-emphasis mb-1">
            Bienvenido de nuevo, <span class="font-weight-medium"> {{ name }} 👋🏻 </span>
          </h3>

          <div
            class="mb-7 text-wrap"
            style="max-inline-size: 800px;"
          >
            <span v-if="store === null">
              Tu progreso es impresionante. ¡Sigamos así y obtengamos muchas ventas!
            </span>
            <span v-else>
              El progreso de <b>`{{ store }}`</b> es impresionante. ¡Sigamos así y obtengamos muchas ventas!
            </span>
          
          </div>

          <div class="d-flex justify-space-between flex-wrap gap-4 flex-column flex-md-row">
            <div
              v-for="{ title, value, icon, color } in [
                { title: 'Comisión Detal', value: (data.supplier.commission ?? '0.00') + '%', icon: 'mdi-brightness-percent', color: 'primary' },
                { title: 'Comisión Mayorista', value: (data.supplier.wholesale_commission ?? '0.00') + '%', icon: 'mdi-sack-percent', color: 'success' },
                { title: 'Ventas Totales', value: 'COP ' + formatNumber(data.supplier.sales ?? '0.00') , icon: 'mdi-check-decagram-outline', color: 'error' },
              ]"
              :key="title"
            >
              <div class="d-flex">
                <VAvatar
                  variant="tonal"
                  :color="color"
                  rounded
                  size="54"
                  class="text-primary me-4"
                >
                  <VIcon
                    :icon="icon"
                    size="38"
                  />
                </VAvatar>
                <div>
                  <span class="text-base">{{ title }}</span>
                  <h4
                    class="text-h4 font-weight-medium"
                    :class="`text-${color}`"
                  >
                    {{ value }}
                  </h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </VCol>
      <VCol cols="12" md="4">
        <div class="d-flex justify-space-between align-center">
          <div class="d-flex flex-column ps-3">
            <h5 class="text-h5 text-high-emphasis mb-2 text-no-wrap">
              Saldo total de la Cuenta
            </h5>
            <span class="mb-7">Crédito restante 
              <span class="text-xs text-medium-emphasis">(Detal + Mayorista)</span>
            </span>
            <div class="text-h3 mb-2 text-primary">
              COP  {{ formatNumber(data.supplier.account.balance) ?? '0.00' }}
            </div>
          </div>
          <div>
            <VueApexCharts
              type="donut"
              height="150"
              width="150"
              :options="timeSpendingChartConfig"
              :series="timeSpendingChartSeries"
            />
          </div>
        </div>
        <!-- <div class="d-flex justify-space-between align-center">
          <div class="d-flex flex-column ps-3">
            <h5 class="text-h5 text-high-emphasis mb-2 text-no-wrap">
              Tiempo en Servicios
            </h5>
            <span class="mb-7">Reporte Semanal</span>
            <div class="text-h3 mb-2">
              231<span class="text-h4 text-medium-emphasis">h</span> 14<span class="text-h4 text-medium-emphasis">m</span>
            </div>
            <div>
              <VChip
                color="success"
                label
              >
                +18.4%
              </VChip>
            </div>
          </div>
          <div>
            <VueApexCharts
              type="donut"
              height="150"
              width="150"
              :options="timeSpendingChartConfig"
              :series="timeSpendingChartSeries"
            />
          </div>
        </div> -->

      </VCol>
    </VRow>

    <VRow class="px-md-6 px-2 match-height" v-if="rol === 'Proveedor' && data">
      <VCol
        cols="12"
        md="8"
        lg="8"
      >
        <Statistics class="h-100" :data="data"/>
      </VCol>
      <VCol
        cols="12"
        md="4"
        lg="84"
      >
        <Congratulations :data="data.deliveredShipping"/>
      </VCol>
    </VRow>

    <VRow class="px-md-6 px-2" v-if="rol === 'Proveedor' && data">
      <VCol
        cols="12"
        sm="6"
        lg="4"
      >
        <!-- <Earning /> -->
        <Services :services="data.services"/>
      </VCol>

      <VCol
        cols="12"
        sm="6"
        lg="4"
      >
        <Products 
          title="Productos Populares"
          subtitle="Top 6 productos más vendidos"
          isPopular="1"
          :products="data.products"/>
      </VCol>

      <VCol
        cols="12"
        sm="6"
        lg="4"
      >
        <Products 
          title="Productos Escasos"
          subtitle="Top 6 productos con menos existencias"
          isPopular="0"
          :products="data.stock"/>
        <!-- <Orders /> -->
      </VCol>
    </VRow>

  </section>
</template>

<style lang="scss" scoped>
  .card-list {
    --v-card-list-gap: 30px;
  }

  .apexcharts-text:deep(.apexcharts-datalabel-value) {
    font-size: 15px !important;
  }
</style>

<route lang="yaml">
  meta:
    action: ver
    subject: dashboard
</route>
