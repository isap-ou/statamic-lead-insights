<template>
    <!-- No-config message state -->
    <div v-if="message" class="card p-4">
        <p class="text-gray text-sm">{{ message }}</p>
    </div>

    <!-- Normal table widget state -->
    <Widget v-else :title="title">
        <template #actions>
            <a v-if="exportUrl" :href="exportUrl" class="text-blue-500 hover:text-blue-700 text-sm">
                {{ labels.exportCsv }}
            </a>
            <span class="text-gray-600 text-sm">{{ labels.lastNDays }}</span>
        </template>

        <Listing
            class="lead-insights-table"
            :items="listingItems"
            :columns="listingColumns"
            :loading="loading"
            :allow-search="false"
            :allow-presets="false"
            :allow-customizing-columns="false"
            sort-column="count"
            sort-direction="desc"
        />
    </Widget>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Widget, Listing } from '@statamic/cms/ui';

const props = defineProps({
    title: { type: String, required: true },
    dataUrl: { type: String, default: null },
    days: { type: Number, default: 30 },
    exportUrl: { type: String, default: null },
    labels: { type: Object, default: () => ({}) },
    message: { type: String, default: null },
});

const { $axios } = Statamic.$app.config.globalProperties;

const rows = ref([]);
const total = ref(0);
const loading = ref(!!props.dataUrl);

/**
 * Fetch widget data from the backend API.
 */
async function fetchData() {
    if (!props.dataUrl) return;

    loading.value = true;
    try {
        const { data } = await $axios.get(props.dataUrl);
        rows.value = data.rows;
        total.value = data.total;
    } catch (error) {
        console.error('Lead Insights: failed to load widget data', error);
    } finally {
        loading.value = false;
    }
}

onMounted(fetchData);

/**
 * Column definitions for the Listing component.
 */
const listingColumns = computed(() => [
    { field: 'label', label: props.labels.label, sortable: true, visible: true },
    { field: 'count', label: props.labels.leads, sortable: true, visible: true },
    { field: 'share', label: props.labels.share, sortable: true, visible: true },
]);

/**
 * Transform rows into listing items with precomputed share and unique IDs.
 */
const listingItems = computed(() =>
    rows.value.map((row, index) => ({
        id: index,
        label: row.label,
        count: row.count,
        share: total.value > 0 ? `${Math.round((row.count / total.value) * 1000) / 10}%` : '0%',
    })),
);
</script>

<style>
.lead-insights-table [data-ui-panel] {
    margin-bottom: 0 !important;
}
</style>
