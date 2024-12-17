import type { Todo, LoginResponse, ApiResponse } from './types.ts';

const BASE_URL = 'http://localhost/To-Do/api';

export async function login(username: string, password: string): Promise<LoginResponse> {
    const response = await fetch(`${BASE_URL}/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    return response.json();
}

export async function register(username: string, email: string, password: string): Promise<LoginResponse> {
    try {
        const response = await fetch(`${BASE_URL}/auth/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, email, password })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Registration error:', error);
        throw error;
    }
}

export async function getTodos(userId: number): Promise<Todo[]> {
    try {
        const response = await fetch(`${BASE_URL}/todos?user_id=${userId}`);
        if (!response.ok) {
            throw new Error('Failed to fetch todos');
        }
        return response.json();
    } catch (error) {
        console.error('Error fetching todos:', error);
        throw error;
    }
}

export async function createTodo(
    title: string, 
    description: string, 
    due_date: string, 
    user_id: number
): Promise<ApiResponse> {
    try {
        const response = await fetch(`${BASE_URL}/todos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, description, due_date, user_id })
        });
        if (!response.ok) {
            throw new Error('Failed to create todo');
        }
        return response.json();
    } catch (error) {
        console.error('Error creating todo:', error);
        throw error;
    }
}

export async function updateTodo(
    todo_id: number,
    title: string,
    description: string,
    status: Todo['status'],
    due_date: string,
    user_id: number
): Promise<ApiResponse> {
    try {
        const response = await fetch(`${BASE_URL}/todos/${todo_id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, description, status, due_date, user_id })
        });
        if (!response.ok) {
            throw new Error('Failed to update todo');
        }
        return response.json();
    } catch (error) {
        console.error('Error updating todo:', error);
        throw error;
    }
}

export async function deleteTodo(todo_id: number, user_id: number): Promise<ApiResponse> {
    const response = await fetch(`${BASE_URL}/todos/${todo_id}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id })
    });
    return response.json();
}
