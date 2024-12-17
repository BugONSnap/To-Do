<script lang="ts">
    import { goto } from '$app/navigation';
    import { currentUser } from '$lib/stores';
    import * as api from '$lib/api';

    let username = '';
    let password = '';
    let loading = false;
    let error = '';

    async function handleSubmit() {
        loading = true;
        error = '';

        try {
            const response = await api.login(username, password);
            if (response.success && response.user_id && response.username) {
                currentUser.set({
                    id: response.user_id,
                    username: response.username
                });
                goto('/');
            } else {
                error = response.error || 'Login failed';
            }
        } catch (err) {
            error = 'Login failed';
        } finally {
            loading = false;
        }
    }
</script>

<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-3xl font-bold mb-8 text-center">Login</h1>

    {#if error}
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
        </div>
    {/if}

    <form on:submit|preventDefault={handleSubmit} class="space-y-4">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input
                id="username"
                type="text"
                bind:value={username}
                required
                class="mt-1 block w-full border rounded-md shadow-sm p-2"
            />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input
                id="password"
                type="password"
                bind:value={password}
                required
                class="mt-1 block w-full border rounded-md shadow-sm p-2"
            />
        </div>

        <button
            type="submit"
            disabled={loading}
            class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
        >
            {loading ? 'Logging in...' : 'Login'}
        </button>
    </form>

    <p class="mt-4 text-center">
        Don't have an account? <a href="/register" class="text-blue-500 hover:text-blue-700">Register</a>
    </p>
</div> 