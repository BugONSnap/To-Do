<script lang="ts">
    import { onMount } from 'svelte';
    import { currentUser } from '$lib/stores';
    import * as api from '$lib/api';
    import type { Todo } from '$lib/types';

    let todos: Todo[] = [];
    let title = '';
    let description = '';
    let dueDate = '';
    let loading = false;
    let error = '';
    let isLoading = false;
    let updatingTodoId: number | null = null;
    let deletingTodoId: number | null = null;

    $: if ($currentUser) {
        loadTodos();
    }

    async function loadTodos() {
        if (!$currentUser) return;
        isLoading = true;
        try {
            todos = await api.getTodos($currentUser.id);
        } catch (err) {
            error = 'Failed to load todos';
        } finally {
            isLoading = false;
        }
    }

    async function handleSubmit() {
        if (!$currentUser) return;
        loading = true;
        error = '';

        try {
            const response = await api.createTodo(
                title,
                description,
                dueDate,
                $currentUser.id
            );

            if (response.success) {
                title = '';
                description = '';
                dueDate = '';
                await loadTodos();
            } else {
                error = response.error || 'Failed to create todo';
            }
        } catch (err) {
            error = 'Failed to create todo';
        } finally {
            loading = false;
        }
    }

    async function handleStatusUpdate(todo: Todo, newStatus: Todo['status']) {
        if (!$currentUser) return;
        updatingTodoId = todo.id;
        try {
            const response = await api.updateTodo(
                todo.id,
                todo.title,
                todo.description,
                newStatus,
                todo.due_date,
                $currentUser.id
            );

            if (response.success) {
                await loadTodos();
            } else {
                error = response.error || 'Failed to update todo';
            }
        } catch (err) {
            error = 'Failed to update todo';
        } finally {
            updatingTodoId = null;
        }
    }

    async function handleDelete(todoId: number) {
        if (!$currentUser) return;
        deletingTodoId = todoId;
        try {
            const response = await api.deleteTodo(todoId, $currentUser.id);
            if (response.success) {
                await loadTodos();
            } else {
                error = response.error || 'Failed to delete todo';
            }
        } catch (err) {
            error = 'Failed to delete todo';
        } finally {
            deletingTodoId = null;
        }
    }
</script>

<div class="container mx-auto px-4 py-8">
    {#if !$currentUser}
        <div class="text-center">
            <h2 class="text-2xl font-bold mb-4">Please log in to manage your todos</h2>
            <a href="/login" class="text-blue-500 hover:text-blue-700">Login</a>
            <span class="mx-2">or</span>
            <a href="/register" class="text-blue-500 hover:text-blue-700">Register</a>
        </div>
    {:else}
        <h1 class="text-3xl font-bold mb-8">Todo List</h1>

        {#if error}
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {error}
            </div>
        {/if}

        <form on:submit|preventDefault={handleSubmit} class="mb-8">
            <div class="grid gap-4 mb-4">
                <input
                    type="text"
                    bind:value={title}
                    placeholder="Todo title"
                    required
                    class="border p-2 rounded"
                />
                <textarea
                    bind:value={description}
                    placeholder="Description"
                    class="border p-2 rounded"
                />
                <input
                    type="date"
                    bind:value={dueDate}
                    class="border p-2 rounded"
                />
            </div>
            <button
                type="submit"
                disabled={loading}
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
            >
                {loading ? 'Adding...' : 'Add Todo'}
            </button>
        </form>

        {#if isLoading}
            <div class="text-center py-4">Loading todos...</div>
        {:else}
            <div class="grid gap-4">
                {#each todos as todo (todo.id)}
                    <div class="border p-4 rounded shadow">
                        <h3 class="font-bold">{todo.title}</h3>
                        <p class="text-gray-600">{todo.description}</p>
                        <p class="text-sm text-gray-500">Due: {new Date(todo.due_date).toLocaleDateString()}</p>
                        <div class="flex justify-between items-center mt-4">
                            <select
                                value={todo.status}
                                on:change={(e) => handleStatusUpdate(todo, e.currentTarget.value as Todo['status'])}
                                class="border rounded p-1"
                            >
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <button
                                on:click={() => handleDelete(todo.id)}
                                class="text-red-500 hover:text-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                {/each}
            </div>
        {/if}
    {/if}
</div>
