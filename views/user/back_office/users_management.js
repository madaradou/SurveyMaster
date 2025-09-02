// Function to handle user banning
async function banUser(userId) {
    if (!confirm('Are you sure you want to ban this user?')) return;

    try {
        const response = await fetch('ban_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        });

        const data = await response.json();
        if (data.success) {
            // Update the UI
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const statusCell = row.querySelector('td:nth-child(5)');
            const actionCell = row.querySelector('td:last-child');
            
            statusCell.textContent = 'Banned';
            const banButton = actionCell.querySelector('.btn-ban');
            banButton.textContent = 'Unban';
            banButton.classList.replace('btn-ban', 'btn-unban');
            banButton.onclick = () => unbanUser(userId);

            alert('User has been banned successfully');
        } else {
            alert('Failed to ban user: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while banning the user');
    }
}

// Function to handle user unbanning
async function unbanUser(userId) {
    if (!confirm('Are you sure you want to unban this user?')) return;

    try {
        const response = await fetch('unban_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        });

        const data = await response.json();
        if (data.success) {
            // Update the UI
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const statusCell = row.querySelector('td:nth-child(5)');
            const actionCell = row.querySelector('td:last-child');
            
            statusCell.textContent = 'Active';
            const unbanButton = actionCell.querySelector('.btn-unban');
            unbanButton.textContent = 'Ban';
            unbanButton.classList.replace('btn-unban', 'btn-ban');
            unbanButton.onclick = () => banUser(userId);

            alert('User has been unbanned successfully');
        } else {
            alert('Failed to unban user: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while unbanning the user');
    }
}

// Function to handle role changes
async function setRole(userId, role) {
    const action = role === 'agent' ? 'make this user an agent' : 'remove agent privileges from this user';
    if (!confirm(`Are you sure you want to ${action}?`)) return;

    const endpoint = role === 'agent' ? 'set_role_to_admin.php' : 'set_role_to_user.php';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        });

        const data = await response.json();
        if (data.success) {
            // Update the UI
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const roleCell = row.querySelector('td:nth-child(4)');
            const actionCell = row.querySelector('td:last-child');

            roleCell.textContent = role;
            const roleButton = actionCell.querySelector('.btn-role');
            roleButton.textContent = role === 'agent' ? 'Make Client' : 'Make Agent';
            roleButton.onclick = () => setRole(userId, role === 'agent' ? 'client' : 'agent');

            alert(`User role has been updated to ${role} successfully`);
        } else {
            alert('Failed to update user role: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating user role');
    }
}

// Function to handle user deletion
async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone!')) return;

    try {
        const response = await fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        });

        const data = await response.json();
        if (data.success) {
            // Remove the row from the table
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            row.remove();
            alert('User has been deleted successfully');
        } else {
            alert('Failed to delete user: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the user');
    }
}