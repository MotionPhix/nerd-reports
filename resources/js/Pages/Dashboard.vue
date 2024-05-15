<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import { IconPlus } from "@tabler/icons-vue"
import useStickyTop from "@/Composables/useStickyTop"
import { LineChart } from '@opd/g2plot-vue'
import { onMounted, reactive, ref } from "vue";
import { Line } from '@antv/g2plot';
import { twi, twj } from "tw-to-css"

const { navClasses } = useStickyTop()

const props = defineProps<{
  dashboardData: Object
}>()

const chartColor = ref(twj(`text-gray-500 dark:text-gray-200`))

console.log(chartColor.value);

const chartSeries = reactive([
  {
    name: 'Projects (Current Month)',
    data: [],
  },
  {
    name: 'Projects (Previous Month)',
    data: [],
  },
]);

const { current_month, previous_month } = props.dashboardData.chart_data;

chartSeries[0].data = Object.entries(current_month).map(([date, count]) => [new Date(date).getTime(), count]);
chartSeries[1].data = !! previous_month
? Object.entries(previous_month).map(([date, count]) => [new Date(date).getTime(), count])
: null;

console.log(chartSeries);
console.log(previous_month);

const options = {
  chart: {
    id: 'vuechart-example',

    toolbar: {
      enabled: false
    },

    zoom: {
      enabled: false
    },

    type: 'line',

    animations: {
      enabled: true,
      easing: 'linear',
      dynamicAnimation: {
        speed: 1000
      }
    },
  },

  dataLabels: {
    enabled: false
  },

  xaxis: {
    categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998],
    type: 'datetime'
  },

  stroke: {
    width: 3,
    curve: 'smooth'
  },

  title: {
    text: 'Monthly Project Comparison',
    align: 'left',
    style: {
      fontSize: "16px",
      color: chartColor.value.color,
    }
  },

  forecastDataPoints: {
    count: 2
  },

  fill: {
    type: 'gradient',
    gradient: {
      shade: 'dark',
      gradientToColors: [ '#FDD835'],
      shadeIntensity: 1,
      type: 'horizontal',
      opacityFrom: 1,
      opacityTo: 1,
      stops: [0, 100, 100, 100]
    },
  }
}

const series = [
  {
    name: 'Last month\'s projects',
    data: [30, 40, 45, 50, 49, 60, 70, 91]
  },

  {
    name: 'This month\'s projects',
    data: [20, 43, 12, 86, 41, 45, 31, 78]
  }
]

// update chart data
// me.$refs.chart.updateSeries([{
//   data: data
// }])

const config = {
  height: 350,
  autoFit: true,
  xField: 'year',
  yField: 'value',
  smooth: true,
  seriesField: 'category',
  xAxis: {
    type: 'time',
  },
  yAxis: {
    label: {
      // 数值格式化为千分位
      formatter: (v) => `${v}`.replace(/\d{1,3}(?=(\d{3})+$)/g, (s) => `${s},`),
    },
  },
  geometryOptions: [
    {
      lineStyle: {
        lineWidth: 50,
      },
    },
  ],
  meta: {
    Projects: {
      max: 15,
    },
  },
  data: [
    {
      "year": "1850",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1850",
      "value": 54,
      "category": "Solid fuel"
    },
    {
      "year": "1850",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1850",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1850",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1851",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1851",
      "value": 54,
      "category": "Solid fuel"
    },
    {
      "year": "1851",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1851",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1851",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1852",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1852",
      "value": 57,
      "category": "Solid fuel"
    },
    {
      "year": "1852",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1852",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1852",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1853",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1853",
      "value": 59,
      "category": "Solid fuel"
    },
    {
      "year": "1853",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1853",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1853",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1854",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1854",
      "value": 69,
      "category": "Solid fuel"
    },
    {
      "year": "1854",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1854",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1854",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1855",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1855",
      "value": 71,
      "category": "Solid fuel"
    },
    {
      "year": "1855",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1855",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1855",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1856",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1856",
      "value": 76,
      "category": "Solid fuel"
    },
    {
      "year": "1856",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1856",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1856",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1857",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1857",
      "value": 77,
      "category": "Solid fuel"
    },
    {
      "year": "1857",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1857",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1857",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1858",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1858",
      "value": 78,
      "category": "Solid fuel"
    },
    {
      "year": "1858",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1858",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1858",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1859",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1859",
      "value": 83,
      "category": "Solid fuel"
    },
    {
      "year": "1859",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1859",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1859",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1860",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1860",
      "value": 91,
      "category": "Solid fuel"
    },
    {
      "year": "1860",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1860",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1860",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1861",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1861",
      "value": 95,
      "category": "Solid fuel"
    },
    {
      "year": "1861",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1861",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1861",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1862",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1862",
      "value": 96,
      "category": "Solid fuel"
    },
    {
      "year": "1862",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1862",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1862",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1863",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1863",
      "value": 103,
      "category": "Solid fuel"
    },
    {
      "year": "1863",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1863",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1863",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1864",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1864",
      "value": 112,
      "category": "Solid fuel"
    },
    {
      "year": "1864",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1864",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1864",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1865",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1865",
      "value": 119,
      "category": "Solid fuel"
    },
    {
      "year": "1865",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1865",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1865",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1866",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1866",
      "value": 122,
      "category": "Solid fuel"
    },
    {
      "year": "1866",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1866",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1866",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1867",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1867",
      "value": 130,
      "category": "Solid fuel"
    },
    {
      "year": "1867",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1867",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1867",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1868",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1868",
      "value": 134,
      "category": "Solid fuel"
    },
    {
      "year": "1868",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1868",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1868",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1869",
      "value": 0,
      "category": "Liquid fuel"
    },
    {
      "year": "1869",
      "value": 142,
      "category": "Solid fuel"
    },
    {
      "year": "1869",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1869",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1869",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1870",
      "value": 1,
      "category": "Liquid fuel"
    },
    {
      "year": "1870",
      "value": 146,
      "category": "Solid fuel"
    },
    {
      "year": "1870",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1870",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1870",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1871",
      "value": 1,
      "category": "Liquid fuel"
    },
    {
      "year": "1871",
      "value": 156,
      "category": "Solid fuel"
    },
    {
      "year": "1871",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1871",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1871",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1872",
      "value": 1,
      "category": "Liquid fuel"
    },
    {
      "year": "1872",
      "value": 173,
      "category": "Solid fuel"
    },
    {
      "year": "1872",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1872",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1872",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1873",
      "value": 1,
      "category": "Liquid fuel"
    },
    {
      "year": "1873",
      "value": 183,
      "category": "Solid fuel"
    },
    {
      "year": "1873",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1873",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1873",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1874",
      "value": 1,
      "category": "Liquid fuel"
    },
    {
      "year": "1874",
      "value": 173,
      "category": "Solid fuel"
    },
    {
      "year": "1874",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1874",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1874",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1875",
      "value": 1,
      "category": "Liquid fuel"
    },
    {
      "year": "1875",
      "value": 187,
      "category": "Solid fuel"
    },
    {
      "year": "1875",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1875",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1875",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1876",
      "value": 1,
      "category": "Liquid fuel"
    },
    {
      "year": "1876",
      "value": 190,
      "category": "Solid fuel"
    },
    {
      "year": "1876",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1876",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1876",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1877",
      "value": 2,
      "category": "Liquid fuel"
    },
    {
      "year": "1877",
      "value": 192,
      "category": "Solid fuel"
    },
    {
      "year": "1877",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1877",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1877",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1878",
      "value": 2,
      "category": "Liquid fuel"
    },
    {
      "year": "1878",
      "value": 194,
      "category": "Solid fuel"
    },
    {
      "year": "1878",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1878",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1878",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1879",
      "value": 3,
      "category": "Liquid fuel"
    },
    {
      "year": "1879",
      "value": 207,
      "category": "Solid fuel"
    },
    {
      "year": "1879",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1879",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1879",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1880",
      "value": 3,
      "category": "Liquid fuel"
    },
    {
      "year": "1880",
      "value": 233,
      "category": "Solid fuel"
    },
    {
      "year": "1880",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1880",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1880",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1881",
      "value": 4,
      "category": "Liquid fuel"
    },
    {
      "year": "1881",
      "value": 239,
      "category": "Solid fuel"
    },
    {
      "year": "1881",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1881",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1881",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1882",
      "value": 4,
      "category": "Liquid fuel"
    },
    {
      "year": "1882",
      "value": 252,
      "category": "Solid fuel"
    },
    {
      "year": "1882",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1882",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1882",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1883",
      "value": 3,
      "category": "Liquid fuel"
    },
    {
      "year": "1883",
      "value": 269,
      "category": "Solid fuel"
    },
    {
      "year": "1883",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1883",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1883",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1884",
      "value": 4,
      "category": "Liquid fuel"
    },
    {
      "year": "1884",
      "value": 271,
      "category": "Solid fuel"
    },
    {
      "year": "1884",
      "value": 0,
      "category": "Gas fuel"
    },
    {
      "year": "1884",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1884",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1885",
      "value": 4,
      "category": "Liquid fuel"
    },
    {
      "year": "1885",
      "value": 273,
      "category": "Solid fuel"
    },
    {
      "year": "1885",
      "value": 1,
      "category": "Gas fuel"
    },
    {
      "year": "1885",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1885",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1886",
      "value": 5,
      "category": "Liquid fuel"
    },
    {
      "year": "1886",
      "value": 275,
      "category": "Solid fuel"
    },
    {
      "year": "1886",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1886",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1886",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1887",
      "value": 5,
      "category": "Liquid fuel"
    },
    {
      "year": "1887",
      "value": 287,
      "category": "Solid fuel"
    },
    {
      "year": "1887",
      "value": 3,
      "category": "Gas fuel"
    },
    {
      "year": "1887",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1887",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1888",
      "value": 5,
      "category": "Liquid fuel"
    },
    {
      "year": "1888",
      "value": 317,
      "category": "Solid fuel"
    },
    {
      "year": "1888",
      "value": 5,
      "category": "Gas fuel"
    },
    {
      "year": "1888",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1888",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1889",
      "value": 6,
      "category": "Liquid fuel"
    },
    {
      "year": "1889",
      "value": 318,
      "category": "Solid fuel"
    },
    {
      "year": "1889",
      "value": 3,
      "category": "Gas fuel"
    },
    {
      "year": "1889",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1889",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1890",
      "value": 8,
      "category": "Liquid fuel"
    },
    {
      "year": "1890",
      "value": 345,
      "category": "Solid fuel"
    },
    {
      "year": "1890",
      "value": 3,
      "category": "Gas fuel"
    },
    {
      "year": "1890",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1890",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1891",
      "value": 9,
      "category": "Liquid fuel"
    },
    {
      "year": "1891",
      "value": 360,
      "category": "Solid fuel"
    },
    {
      "year": "1891",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1891",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1891",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1892",
      "value": 9,
      "category": "Liquid fuel"
    },
    {
      "year": "1892",
      "value": 363,
      "category": "Solid fuel"
    },
    {
      "year": "1892",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1892",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1892",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1893",
      "value": 10,
      "category": "Liquid fuel"
    },
    {
      "year": "1893",
      "value": 358,
      "category": "Solid fuel"
    },
    {
      "year": "1893",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1893",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1893",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1894",
      "value": 9,
      "category": "Liquid fuel"
    },
    {
      "year": "1894",
      "value": 372,
      "category": "Solid fuel"
    },
    {
      "year": "1894",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1894",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1894",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1895",
      "value": 11,
      "category": "Liquid fuel"
    },
    {
      "year": "1895",
      "value": 393,
      "category": "Solid fuel"
    },
    {
      "year": "1895",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1895",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1895",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1896",
      "value": 12,
      "category": "Liquid fuel"
    },
    {
      "year": "1896",
      "value": 405,
      "category": "Solid fuel"
    },
    {
      "year": "1896",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1896",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1896",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1897",
      "value": 13,
      "category": "Liquid fuel"
    },
    {
      "year": "1897",
      "value": 425,
      "category": "Solid fuel"
    },
    {
      "year": "1897",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1897",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1897",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1898",
      "value": 13,
      "category": "Liquid fuel"
    },
    {
      "year": "1898",
      "value": 449,
      "category": "Solid fuel"
    },
    {
      "year": "1898",
      "value": 2,
      "category": "Gas fuel"
    },
    {
      "year": "1898",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1898",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1899",
      "value": 14,
      "category": "Liquid fuel"
    },
    {
      "year": "1899",
      "value": 491,
      "category": "Solid fuel"
    },
    {
      "year": "1899",
      "value": 3,
      "category": "Gas fuel"
    },
    {
      "year": "1899",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1899",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1900",
      "value": 16,
      "category": "Liquid fuel"
    },
    {
      "year": "1900",
      "value": 515,
      "category": "Solid fuel"
    },
    {
      "year": "1900",
      "value": 3,
      "category": "Gas fuel"
    },
    {
      "year": "1900",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1900",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1901",
      "value": 18,
      "category": "Liquid fuel"
    },
    {
      "year": "1901",
      "value": 531,
      "category": "Solid fuel"
    },
    {
      "year": "1901",
      "value": 4,
      "category": "Gas fuel"
    },
    {
      "year": "1901",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1901",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1902",
      "value": 19,
      "category": "Liquid fuel"
    },
    {
      "year": "1902",
      "value": 543,
      "category": "Solid fuel"
    },
    {
      "year": "1902",
      "value": 4,
      "category": "Gas fuel"
    },
    {
      "year": "1902",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1902",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1903",
      "value": 20,
      "category": "Liquid fuel"
    },
    {
      "year": "1903",
      "value": 593,
      "category": "Solid fuel"
    },
    {
      "year": "1903",
      "value": 4,
      "category": "Gas fuel"
    },
    {
      "year": "1903",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1903",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1904",
      "value": 23,
      "category": "Liquid fuel"
    },
    {
      "year": "1904",
      "value": 597,
      "category": "Solid fuel"
    },
    {
      "year": "1904",
      "value": 4,
      "category": "Gas fuel"
    },
    {
      "year": "1904",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1904",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1905",
      "value": 23,
      "category": "Liquid fuel"
    },
    {
      "year": "1905",
      "value": 636,
      "category": "Solid fuel"
    },
    {
      "year": "1905",
      "value": 5,
      "category": "Gas fuel"
    },
    {
      "year": "1905",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1905",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1906",
      "value": 23,
      "category": "Liquid fuel"
    },
    {
      "year": "1906",
      "value": 680,
      "category": "Solid fuel"
    },
    {
      "year": "1906",
      "value": 5,
      "category": "Gas fuel"
    },
    {
      "year": "1906",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1906",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1907",
      "value": 28,
      "category": "Liquid fuel"
    },
    {
      "year": "1907",
      "value": 750,
      "category": "Solid fuel"
    },
    {
      "year": "1907",
      "value": 5,
      "category": "Gas fuel"
    },
    {
      "year": "1907",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1907",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1908",
      "value": 30,
      "category": "Liquid fuel"
    },
    {
      "year": "1908",
      "value": 714,
      "category": "Solid fuel"
    },
    {
      "year": "1908",
      "value": 5,
      "category": "Gas fuel"
    },
    {
      "year": "1908",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1908",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1909",
      "value": 32,
      "category": "Liquid fuel"
    },
    {
      "year": "1909",
      "value": 747,
      "category": "Solid fuel"
    },
    {
      "year": "1909",
      "value": 6,
      "category": "Gas fuel"
    },
    {
      "year": "1909",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1909",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1910",
      "value": 34,
      "category": "Liquid fuel"
    },
    {
      "year": "1910",
      "value": 778,
      "category": "Solid fuel"
    },
    {
      "year": "1910",
      "value": 7,
      "category": "Gas fuel"
    },
    {
      "year": "1910",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1910",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1911",
      "value": 36,
      "category": "Liquid fuel"
    },
    {
      "year": "1911",
      "value": 792,
      "category": "Solid fuel"
    },
    {
      "year": "1911",
      "value": 7,
      "category": "Gas fuel"
    },
    {
      "year": "1911",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1911",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1912",
      "value": 37,
      "category": "Liquid fuel"
    },
    {
      "year": "1912",
      "value": 834,
      "category": "Solid fuel"
    },
    {
      "year": "1912",
      "value": 8,
      "category": "Gas fuel"
    },
    {
      "year": "1912",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1912",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1913",
      "value": 41,
      "category": "Liquid fuel"
    },
    {
      "year": "1913",
      "value": 895,
      "category": "Solid fuel"
    },
    {
      "year": "1913",
      "value": 8,
      "category": "Gas fuel"
    },
    {
      "year": "1913",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1913",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1914",
      "value": 42,
      "category": "Liquid fuel"
    },
    {
      "year": "1914",
      "value": 800,
      "category": "Solid fuel"
    },
    {
      "year": "1914",
      "value": 8,
      "category": "Gas fuel"
    },
    {
      "year": "1914",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1914",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1915",
      "value": 45,
      "category": "Liquid fuel"
    },
    {
      "year": "1915",
      "value": 784,
      "category": "Solid fuel"
    },
    {
      "year": "1915",
      "value": 9,
      "category": "Gas fuel"
    },
    {
      "year": "1915",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1915",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1916",
      "value": 48,
      "category": "Liquid fuel"
    },
    {
      "year": "1916",
      "value": 842,
      "category": "Solid fuel"
    },
    {
      "year": "1916",
      "value": 10,
      "category": "Gas fuel"
    },
    {
      "year": "1916",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1916",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1917",
      "value": 54,
      "category": "Liquid fuel"
    },
    {
      "year": "1917",
      "value": 891,
      "category": "Solid fuel"
    },
    {
      "year": "1917",
      "value": 11,
      "category": "Gas fuel"
    },
    {
      "year": "1917",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1917",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1918",
      "value": 53,
      "category": "Liquid fuel"
    },
    {
      "year": "1918",
      "value": 873,
      "category": "Solid fuel"
    },
    {
      "year": "1918",
      "value": 10,
      "category": "Gas fuel"
    },
    {
      "year": "1918",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1918",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1919",
      "value": 61,
      "category": "Liquid fuel"
    },
    {
      "year": "1919",
      "value": 735,
      "category": "Solid fuel"
    },
    {
      "year": "1919",
      "value": 10,
      "category": "Gas fuel"
    },
    {
      "year": "1919",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1919",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1920",
      "value": 78,
      "category": "Liquid fuel"
    },
    {
      "year": "1920",
      "value": 843,
      "category": "Solid fuel"
    },
    {
      "year": "1920",
      "value": 11,
      "category": "Gas fuel"
    },
    {
      "year": "1920",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1920",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1921",
      "value": 84,
      "category": "Liquid fuel"
    },
    {
      "year": "1921",
      "value": 709,
      "category": "Solid fuel"
    },
    {
      "year": "1921",
      "value": 10,
      "category": "Gas fuel"
    },
    {
      "year": "1921",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1921",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1922",
      "value": 94,
      "category": "Liquid fuel"
    },
    {
      "year": "1922",
      "value": 740,
      "category": "Solid fuel"
    },
    {
      "year": "1922",
      "value": 11,
      "category": "Gas fuel"
    },
    {
      "year": "1922",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1922",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1923",
      "value": 111,
      "category": "Liquid fuel"
    },
    {
      "year": "1923",
      "value": 845,
      "category": "Solid fuel"
    },
    {
      "year": "1923",
      "value": 14,
      "category": "Gas fuel"
    },
    {
      "year": "1923",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1923",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1924",
      "value": 110,
      "category": "Liquid fuel"
    },
    {
      "year": "1924",
      "value": 836,
      "category": "Solid fuel"
    },
    {
      "year": "1924",
      "value": 16,
      "category": "Gas fuel"
    },
    {
      "year": "1924",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1924",
      "value": 0,
      "category": "Gas flarinl"
    },
    {
      "year": "1925",
      "value": 116,
      "category": "Liquid fuel"
    },
    {
      "year": "1925",
      "value": 842,
      "category": "Solid fuel"
    },
    {
      "year": "1925",
      "value": 17,
      "category": "Gas fuel"
    },
    {
      "year": "1925",
      "value": 0,
      "category": "Cement production"
    },
    {
      "year": "1925",
      "value": 0,
      "category": "Gas flarinl"
    },
  ],
}

onMounted(() => {

fetch('https://gw.alipayobjects.com/os/bmw-prod/55424a73-7cb8-4f79-b60d-3ab627ac5698.json')
  .then((res) => res.json())
  .then((data) => {
    const line = new Line('container', {
      data,
      xField: 'year',
      yField: 'value',
      seriesField: 'category',
      xAxis: {
        type: 'time',
      },
      yAxis: {
        label: {
          // 数值格式化为千分位
          formatter: (v) => `${v}`.replace(/\d{1,3}(?=(\d{3})+$)/g, (s) => `${s},`),
        },
      },
    });

    line.render();
  });

  fetch('https://gw.alipayobjects.com/os/bmw-prod/e00d52f4-2fa6-47ee-a0d7-105dd95bde20.json')
  .then((res) => res.json())
  .then((data) => {
    const linePlot = new Line('pop-container', {
      data,
      xField: 'year',
      yField: 'gdp',
      seriesField: 'name',
      yAxis: {
        label: {
          formatter: (v) => `${(v / 10e8).toFixed(1)} B`,
        },
      },
      legend: {
        position: 'top',
      },
      smooth: true,
      // @TODO 后续会换一种动画方式
      animation: {
        appear: {
          animation: 'path-in',
          duration: 5000,
        },
      },
    });

    linePlot.render();
  });

})

defineOptions({
  layout: AuthenticatedLayout
})
</script>

<template>
  <Head title="Dashboard" />

  <nav
    class="flex items-center h-16 max-w-3xl gap-6 px-8 mx-auto dark:text-white dark:border-gray-700"
    :class="navClasses">
    <h2
      class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white">

      <span>Dashboard</span>

    </h2>

    <span class="flex-1"></span>

    <Link
      as="button"
      :href="route('projects.create')"
      class="inline-flex items-center gap-2 px-3 py-2 ml-6 font-semibold transition duration-300 rounded-md dark:text-slate-300 bg-slate-100 dark:bg-slate-800 dark:hover:text-slate-900 dark:hover:bg-slate-500 hover:bg-gray-200">
      <IconPlus stroke="2.5" class="w-4 h-4" />
      <span>Create</span>
    </Link>
  </nav>

  <section class="max-w-3xl px-8 py-12 mx-auto">

    <!-- Card Section -->
    <div class="max-w-[85rem] py-10 lg:py-14 mx-auto">
      <!-- Grid -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800 border-neutral-300">
          <div class="p-4 md:p-5 flex gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-200 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>

            <div class="grow">

              <div class="flex items-center gap-x-2">

                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                  Total <br> contacts
                </p>

                <div v-tooltip="'The number of contacts (not companies)'">

                  <svg class="flex-shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                       xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <path d="M12 17h.01" />
                  </svg>

                </div>

              </div>

              <div class="mt-1 flex items-center gap-x-2">

                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                  {{ props.dashboardData.totalContacts }}
                </h3>

              </div>

            </div>

          </div>

        </div>
        <!-- End Card -->

        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800 border-neutral-300">
          <div class="p-4 md:p-5 flex gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-200 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 22h14" />
                <path d="M5 2h14" />
                <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
                <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
              </svg>
            </div>

            <div class="grow">
              <div class="flex items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                  Total <br> Projects
                </p>
              </div>

              <div class="mt-1 flex items-center gap-x-2">
                <h3 class="text-xl font-medium text-gray-800 dark:text-neutral-200">
                  {{ props.dashboardData.totalProjects }}
                </h3>
              </div>

            </div>
          </div>
        </div>
        <!-- End Card -->

        <!-- Card -->
        <div
          class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800 border-neutral-300">
          <div class="p-4 md:p-5 flex gap-x-4">
            <div
              class="flex-shrink-0 flex justify-center items-center size-[46px] bg-gray-200 rounded-lg dark:bg-neutral-800">
              <svg class="flex-shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                   xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" />
                <path d="m12 12 4 10 1.7-4.3L22 16Z" />
              </svg>
            </div>

            <div class="grow">
              <div class="flex items-center gap-x-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                  Total <br> Tasks
                </p>
              </div>
              <div class="mt-1 flex items-center gap-x-2">

                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                  {{ props.dashboardData.totalTasks }}
                </h3>

              </div>
            </div>
          </div>
        </div>
        <!-- End Card -->

      </div>
      <!-- End Grid -->
    </div>
    <!-- End Card Section -->

    <!-- Charts Section -->


    <apexchart
      width="100%"
      type="line"
      :options="options"
      :series="series">
    </apexchart>

    <line-chart v-bind="config" />

    <div id="container" />

    <div id="pop-container" />


    <!-- End Charts Section -->

  </section>

</template>
