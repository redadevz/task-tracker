<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    filter: { type: String, default: null },
});

/* ---- status config: single source of truth for UI ---- */
const STATUSES = {
    'todo': {
        label: 'To Do',
        badge: 'bg-slate-100 text-slate-700 ring-slate-600/20',
        dot: 'bg-slate-400',
        bar: 'before:bg-slate-300',
    },
    'in-progress': {
        label: 'In Progress',
        badge: 'bg-amber-100 text-amber-700 ring-amber-600/20',
        dot: 'bg-amber-500',
        bar: 'before:bg-amber-400',
    },
    'done': {
        label: 'Done',
        badge: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
        dot: 'bg-emerald-500',
        bar: 'before:bg-emerald-400',
    },
};

const FILTERS = [
    { key: null, label: 'All' },
    { key: 'todo', label: 'To Do' },
    { key: 'in-progress', label: 'In Progress' },
    { key: 'done', label: 'Done' },
];

/* ---- add task ---- */
const form = useForm({ description: '' });

const submit = () => {
    form.post(route('tasks.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('description'),
    });
};

/* ---- filtering ---- */
const setFilter = (key) => {
    router.get(route('tasks.index'), key ? { status: key } : {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

/* ---- update status ---- */
const setStatus = (task, status) => {
    if (task.status === status) return;
    router.patch(route('tasks.update', task.id), { status }, {
        preserveScroll: true,
        preserveState: true,
    });
};

/* ---- inline edit ---- */
const editingId = ref(null);
const editText = ref('');

const startEdit = (task) => {
    editingId.value = task.id;
    editText.value = task.description;
};
const cancelEdit = () => {
    editingId.value = null;
    editText.value = '';
};
const saveEdit = (task) => {
    const value = editText.value.trim();
    if (!value || value === task.description) return cancelEdit();
    router.patch(route('tasks.update', task.id), { description: value }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: cancelEdit,
    });
};

/* ---- delete ---- */
const destroy = (task) => {
    if (!confirm('Delete this task?')) return;
    router.delete(route('tasks.destroy', task.id), { preserveScroll: true });
};

const isEmpty = computed(() => props.tasks.length === 0);
</script>

<template>
    <Head title="Tasks" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Tasks
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Add task card -->
                <div
                    class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5 transition-shadow duration-300 hover:shadow-md"
                >
                    <form @submit.prevent="submit" class="flex items-start gap-3">
                        <div class="flex-1">
                            <input
                                v-model="form.description"
                                type="text"
                                placeholder="What needs to be done?"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 shadow-sm transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-200"
                            />
                            <InputError class="mt-1" :message="form.errors.description" />
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-500 hover:shadow-md active:scale-95 disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add
                        </button>
                    </form>
                </div>

                <!-- Filter pills -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="f in FILTERS"
                        :key="f.label"
                        @click="setFilter(f.key)"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200 active:scale-95"
                        :class="filter === f.key
                            ? 'bg-gray-900 text-white shadow'
                            : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'"
                    >
                        {{ f.label }}
                    </button>
                </div>

                <!-- Empty state -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                >
                    <div
                        v-if="isEmpty"
                        class="rounded-2xl border-2 border-dashed border-gray-200 bg-white/50 py-16 text-center"
                    >
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-gray-500">No tasks here yet</p>
                        <p class="text-xs text-gray-400">Add one above to get started.</p>
                    </div>
                </Transition>

                <!-- Task list -->
                <TransitionGroup
                    tag="ul"
                    class="space-y-3"
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in absolute w-full"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                    move-class="transition duration-300"
                >
                    <li
                        v-for="task in tasks"
                        :key="task.id"
                        class="group relative overflow-hidden rounded-2xl bg-white p-4 pl-6 shadow-sm ring-1 ring-gray-900/5 transition-all duration-300 hover:shadow-md before:absolute before:inset-y-0 before:left-0 before:w-1.5 before:transition-colors"
                        :class="STATUSES[task.status].bar"
                    >
                        <div class="flex items-center gap-3">
                            <!-- description / inline edit -->
                            <div class="min-w-0 flex-1">
                                <template v-if="editingId === task.id">
                                    <input
                                        v-model="editText"
                                        type="text"
                                        @keyup.enter="saveEdit(task)"
                                        @keyup.esc="cancelEdit"
                                        class="w-full rounded-lg border-gray-200 px-2 py-1 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200"
                                        autofocus
                                    />
                                </template>
                                <template v-else>
                                    <p
                                        class="truncate text-sm font-medium transition-colors"
                                        :class="task.status === 'done' ? 'text-gray-400 line-through' : 'text-gray-800'"
                                    >
                                        {{ task.description }}
                                    </p>
                                </template>
                            </div>

                            <!-- status badge -->
                            <span
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                :class="STATUSES[task.status].badge"
                            >
                                <span class="h-1.5 w-1.5 rounded-full" :class="STATUSES[task.status].dot" />
                                {{ STATUSES[task.status].label }}
                            </span>
                        </div>

                        <!-- actions -->
                        <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
                            <!-- status switcher -->
                            <div class="flex gap-1">
                                <button
                                    v-for="(cfg, key) in STATUSES"
                                    :key="key"
                                    @click="setStatus(task, key)"
                                    class="rounded-lg px-2.5 py-1 text-xs font-medium transition-all duration-150 active:scale-90"
                                    :class="task.status === key
                                        ? 'bg-gray-900 text-white'
                                        : 'text-gray-500 hover:bg-gray-100'"
                                >
                                    {{ cfg.label }}
                                </button>
                            </div>

                            <!-- edit / delete -->
                            <div class="flex items-center gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                <template v-if="editingId === task.id">
                                    <button @click="saveEdit(task)" class="rounded-lg p-1.5 text-emerald-600 hover:bg-emerald-50" title="Save">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </button>
                                    <button @click="cancelEdit" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100" title="Cancel">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                </template>
                                <template v-else>
                                    <button @click="startEdit(task)" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-indigo-600" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                    </button>
                                    <button @click="destroy(task)" class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Delete">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </li>
                </TransitionGroup>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
