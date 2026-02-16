import LeadInsightsTable from './components/widgets/LeadInsightsTable.vue';

Statamic.booting(() => {
    Statamic.$components.register('lead-insights-table', LeadInsightsTable);
});
