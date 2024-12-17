<script lang="ts">
    import { goto } from '$app/navigation';
    import * as api from '$lib/api';

    let username = '';
    let email = '';
    let password = '';
    let loading = false;
    let error = '';

    async function handleSubmit() {
        loading = true;
        error = '';

        try {
            const response = await api.register(username, email, password);
            if (response.success) {
                goto('/login');
            } else {
                error = response.error || 'Registration failed';
            }
        } catch (err) {
            console.error('Registration error:', err);
            error = 'Registration failed. Please try again later.';
        } finally {
            loading = false;
        }
    }
</script>

<div class="container mx-auto px-4 py-8 max-w-md">
    <h1 class="text-3xl font-bold mb-8 text-center">Register</h1>

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
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
                id="email"
                type="email"
                bind:value={email}
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
            {loading ? 'Registering...' : 'Register'}
        </button>
    </form>

    <p class="mt-4 text-center">
        Already have an account? <a href="/login" class="text-blue-500 hover:text-blue-700">Login</a>
    </p>
</div> 