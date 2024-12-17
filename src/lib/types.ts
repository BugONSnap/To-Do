export interface Todo {
    id: number;
    title: string;
    description: string;
    status: 'pending' | 'in_progress' | 'completed';
    due_date: string;
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    username: string;
}

export interface LoginResponse {
    success: boolean;
    user_id?: number;
    username?: string;
    error?: string;
}

export interface ApiResponse {
    success?: boolean;
    error?: string;
    todo_id?: number;
} 