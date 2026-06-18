<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ total: 0, todo: 0, inProgress: 0, done: 0 }),
    },
    recent: { type: Array, default: () => [] },
});

const page = usePage();
const userName = computed(() => page.props.auth.user?.name ?? 'there');

const progress = computed(() =>
    props.stats.total ? Math.round((props.stats.done / props.stats.total) * 100) : 0
);

const cards = computed(() => [
    {
        label: 'Total Tasks',
        value: props.stats.total,
        filter: null,
        ring: 'ring-indigo-100',
        icon: 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5',
        iconWrap: 'bg-indigo-50 text-indigo-600',
    },
    {
        label: 'To Do',
        value: props.stats.todo,
        filter: 'todo',
        ring: 'ring-slate-100',
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        iconWrap: 'bg-slate-100 text-slate-600',
    },
    {
        label: 'In Progress',
        value: props.stats.inProgress,
        filter: 'in-progress',
        ring: 'ring-amber-100',
        icon: 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
        iconWrap: 'bg-amber-50 text-amber-600',
    },
    {
        label: 'Done',
        value: props.stats.done,
        filter: 'done',
        ring: 'ring-emerald-100',
        icon: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        iconWrap: 'bg-emerald-50 text-emerald-600',
    },
]);

const STATUS_LABEL = {
    'todo': 'To Do',
    'in-progress': 'In Progress',
    'done': 'Done',
};
const STATUS_BADGE = {
    'todo': 'bg-slate-100 text-slate-700',
    'in-progress': 'bg-amber-100 text-amber-700',
    'done': 'bg-emerald-100 text-emerald-700',
};

const tasksUrl = (filter) =>
    filter ? route('tasks.index', { status: filter }) : route('tasks.index');
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">

                <!-- Hero -->
                <section
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 px-8 py-12 text-white shadow-xl"
                >
                    <!-- decorative blobs -->
                    <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-violet-400/20 blur-3xl"></div>

                    <div class="relative max-w-2xl">
                        <p class="text-sm font-medium uppercase tracking-wider text-indigo-200">Welcome back</p>
                        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">
                            Hello, {{ userName }} 👋
                        </h1>
                        <p class="mt-3 text-indigo-100">
                            <template v-if="stats.total">
                                You have <span class="font-semibold text-white">{{ stats.todo + stats.inProgress }}</span>
                                task<span v-if="(stats.todo + stats.inProgress) !== 1">s</span> still open.
                                Keep the momentum going.
                            </template>
                            <template v-else>
                                You have no tasks yet — create your first one and get organized.
                            </template>
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link
                                :href="route('tasks.index')"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition-all duration-200 hover:bg-indigo-50 hover:shadow-md active:scale-95"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                                View all tasks
                            </Link>
                            <Link
                                :href="route('tasks.index')"
                                class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-5 py-2.5 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur transition-all duration-200 hover:bg-white/20 active:scale-95"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Add a task
                            </Link>
                        </div>
                    </div>
                </section>

                <!-- Stat cards -->
                <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <Link
                        v-for="card in cards"
                        :key="card.label"
                        :href="tasksUrl(card.filter)"
                        class="group rounded-2xl bg-white p-5 shadow-sm ring-1 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                        :class="card.ring"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                                :class="card.iconWrap"
                            >
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                                </svg>
                            </span>
                            <svg class="h-5 w-5 text-gray-300 transition-all duration-300 group-hover:translate-x-1 group-hover:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-gray-900">{{ card.value }}</p>
                        <p class="text-sm font-medium text-gray-500">{{ card.label }}</p>
                    </Link>
                </section>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Progress -->
                    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                        <h3 class="text-sm font-semibold text-gray-700">Completion</h3>
                        <div class="mt-6 flex items-center justify-center">
                            <div class="relative h-36 w-36">
                                <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#eef2ff" stroke-width="3.5" />
                                    <circle
                                        cx="18" cy="18" r="15.9155" fill="none"
                                        stroke="#4f46e5" stroke-width="3.5" stroke-linecap="round"
                                        :stroke-dasharray="`${progress}, 100`"
                                        class="transition-all duration-700 ease-out"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-bold text-gray-900">{{ progress }}%</span>
                                    <span class="text-xs text-gray-400">complete</span>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-center text-sm text-gray-500">
                            {{ stats.done }} of {{ stats.total }} tasks done
                        </p>
                    </section>

                    <!-- Recent tasks -->
                    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700">Recent tasks</h3>
                            <Link :href="route('tasks.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">
                                View all →
                            </Link>
                        </div>

                        <ul v-if="recent.length" class="mt-4 divide-y divide-gray-100">
                            <li
                                v-for="task in recent"
                                :key="task.id"
                                class="flex items-center justify-between gap-3 py-3 transition-colors hover:bg-gray-50/60"
                            >
                                <span
                                    class="truncate text-sm font-medium"
                                    :class="task.status === 'done' ? 'text-gray-400 line-through' : 'text-gray-800'"
                                >
                                    {{ task.description }}
                                </span>
                                <span
                                    class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="STATUS_BADGE[task.status]"
                                >
                                    {{ STATUS_LABEL[task.status] }}
                                </span>
                            </li>
                        </ul>

                        <div v-else class="mt-8 flex flex-col items-center justify-center py-8 text-center">
                            <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <p class="mt-2 text-sm text-gray-500">No tasks yet</p>
                            <Link :href="route('tasks.index')" class="mt-1 text-xs font-medium text-indigo-600 hover:text-indigo-500">Create your first task →</Link>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
