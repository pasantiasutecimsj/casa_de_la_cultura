<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    startField: {
        type: String,
        default: 'fecha',
    },
    endField: {
        type: String,
        default: 'fecha_fin',
    },
    statusField: {
        type: String,
        default: null,
    },
    approvedValues: {
        type: Array,
        default: () => ['Aprobado', 'approved'],
    },
    inReviewValues: {
        type: Array,
        default: () => ['En proceso', 'in_review', 'pending'],
    },
    detailTitle: {
        type: String,
        default: 'Elementos del',
    },
    legendLabelHasItems: {
        type: String,
        default: 'Hay elementos',
    },
    legendLabelInReview: {
        type: String,
        default: 'En revisión',
    },
    legendLabelApproved: {
        type: String,
        default: 'Aprobado',
    },
});

const weekdays = ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'];
const today = new Date();
const currentYear = ref(today.getFullYear());
const currentMonth = ref(today.getMonth());
const selectedDate = ref(null);

const dateKey = (date) => [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, '0'),
    String(date.getDate()).padStart(2, '0'),
].join('-');

const parseDate = (value) => {
    if (!value) {
        return null;
    }

    const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
    const date = match
        ? new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]))
        : new Date(value);

    return Number.isNaN(date.getTime()) ? null : date;
};

const itemsByDate = computed(() => {
    const grouped = {};

    props.items.forEach((item) => {
        const start = parseDate(item[props.startField]);
        const parsedEnd = parseDate(item[props.endField]);

        if (!start) {
            return;
        }

        const end = parsedEnd && parsedEnd >= start ? parsedEnd : start;
        const cursor = new Date(start);

        while (cursor <= end) {
            const key = dateKey(cursor);
            (grouped[key] ??= []).push(item);
            cursor.setDate(cursor.getDate() + 1);
        }
    });

    return grouped;
});

const monthLabel = computed(() => {
    const date = new Date(currentYear.value, currentMonth.value, 1);
    const label = new Intl.DateTimeFormat('es-ES', {
        month: 'long',
        year: 'numeric',
    }).format(date);

    return label.charAt(0).toUpperCase() + label.slice(1);
});

const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;
    const firstWeekday = (new Date(year, month, 1).getDay() + 6) % 7;
    const firstDate = new Date(year, month, 1 - firstWeekday);

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(firstDate);
        date.setDate(firstDate.getDate() + index);

        return {
            day: date.getDate(),
            key: dateKey(date),
            isCurrentMonth: date.getMonth() === month,
        };
    });
});

const approvedStatuses = computed(() => new Set(
    props.approvedValues.map((value) => String(value).toLocaleLowerCase()),
));
const reviewStatuses = computed(() => new Set(
    props.inReviewValues.map((value) => String(value).toLocaleLowerCase()),
));
const selectedItems = computed(() => itemsByDate.value[selectedDate.value] ?? []);
const selectedDateLabel = computed(() => {
    const date = parseDate(selectedDate.value);

    return date?.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
});

const changeMonth = (offset) => {
    const date = new Date(currentYear.value, currentMonth.value + offset, 1);
    currentYear.value = date.getFullYear();
    currentMonth.value = date.getMonth();
    selectedDate.value = null;
};

const hasStatus = (item, statuses) => {
    if (!props.statusField || item[props.statusField] == null) {
        return false;
    }

    return statuses.has(String(item[props.statusField]).toLocaleLowerCase());
};

const selectDate = (key) => {
    selectedDate.value = itemsByDate.value[key]?.length ? key : null;
};
</script>

<template>
    <section class="rounded-3xl bg-gradient-to-r from-brand to-brand-dark p-4 text-white shadow-xl sm:p-6">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-2xl transition hover:bg-white/20"
                    aria-label="Mes anterior"
                    @click="changeMonth(-1)"
                >
                    ‹
                </button>
                <h2 class="min-w-48 text-center text-xl font-semibold sm:text-2xl">{{ monthLabel }}</h2>
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-2xl transition hover:bg-white/20"
                    aria-label="Mes siguiente"
                    @click="changeMonth(1)"
                >
                    ›
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-sm ring-2 ring-red-400" />Fecha actual</span>
                <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-sm ring-2 ring-yellow-400" />{{ legendLabelHasItems }}</span>
                <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-blue-400" />{{ legendLabelInReview }}</span>
                <span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-green-400" />{{ legendLabelApproved }}</span>
            </div>
        </div>

        <div class="mb-2 grid grid-cols-7 text-center text-xs font-semibold text-white/80">
            <div v-for="weekday in weekdays" :key="weekday">{{ weekday }}</div>
        </div>

        <div class="grid grid-cols-7 gap-1 sm:gap-2">
            <button
                v-for="cell in calendarDays"
                :key="cell.key"
                type="button"
                class="relative flex min-h-16 flex-col rounded-xl border bg-white/90 text-left text-sm text-gray-800 shadow-sm transition hover:bg-white sm:min-h-20 sm:rounded-2xl"
                :class="[
                    cell.isCurrentMonth ? '' : 'opacity-40',
                    cell.key === dateKey(today)
                        ? 'border-2 border-red-400'
                        : itemsByDate[cell.key]?.length
                            ? 'border-2 border-yellow-400'
                            : 'border-transparent',
                    selectedDate === cell.key ? 'ring-2 ring-white/75' : '',
                ]"
                @click="selectDate(cell.key)"
            >
                <span class="px-2 pt-1 text-xs font-semibold text-gray-600">{{ cell.day }}</span>
                <span v-if="itemsByDate[cell.key]?.length" class="mt-auto flex items-center gap-1 px-2 pb-2">
                    <i
                        v-if="itemsByDate[cell.key].some((item) => hasStatus(item, reviewStatuses))"
                        class="h-2 w-2 rounded-full bg-blue-500"
                    />
                    <i
                        v-if="itemsByDate[cell.key].some((item) => hasStatus(item, approvedStatuses))"
                        class="h-2 w-2 rounded-full bg-green-500"
                    />
                    <span class="hidden text-xs sm:inline">{{ legendLabelHasItems }}</span>
                </span>
            </button>
        </div>

        <div v-if="selectedItems.length" class="mt-6 rounded-2xl bg-white p-4 text-gray-800 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase text-gray-500">{{ detailTitle }}</p>
                    <p class="text-lg font-semibold">{{ selectedDateLabel }}</p>
                </div>
                <p class="text-xs text-gray-500">{{ selectedItems.length }} elemento(s)</p>
            </div>

            <div class="space-y-2">
                <template v-for="(item, index) in selectedItems" :key="item.id ?? item.uuid ?? index">
                    <slot name="item" :item="item">
                        <pre class="whitespace-pre-wrap break-all rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-700">{{ item }}</pre>
                    </slot>
                </template>
            </div>
        </div>
    </section>
</template>
