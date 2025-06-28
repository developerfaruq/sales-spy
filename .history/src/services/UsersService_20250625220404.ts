class UsersService {
    static async getUsers(page: number, pageSize: number) {
        // Replace with your actual API endpoint
        const response = await fetch(`/api/users?page=${page}&pageSize=${pageSize}`);
        const result = await response.json();
        return {
            data: result.users,
            total: result.totalCount,
        };
    }
    // ...existing code...
}

export default UsersService;