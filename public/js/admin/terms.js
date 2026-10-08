(function () {
    window.loadTermsData = async function() {
        try {
            const res = await window.apiFetch('/api/admin/terms-conditions');
            if (!res || !res.ok) return;
            const json = await res.json();
            if (json.status && json.data) {
                const titleInput = document.getElementById('terms-title');
                if (titleInput) titleInput.value = json.data.title || 'Terms & Conditions';
                const contentInput = document.getElementById('terms-content');
                if (contentInput) contentInput.value = json.data.content || '';
            }
        } catch (err) {
            console.error('Failed to load terms data:', err);
        }
    };

    window.submitTermsForm = async function(e) {
        if (e) e.preventDefault();
        
        const successDiv = document.getElementById('terms-success');
        const errorDiv = document.getElementById('terms-error');
        if (successDiv) successDiv.style.display = 'none';
        if (errorDiv) errorDiv.style.display = 'none';

        const title = document.getElementById('terms-title')?.value;
        const content = document.getElementById('terms-content')?.value;

        try {
            const res = await window.apiFetch('/api/admin/terms-conditions', {
                method: 'POST',
                body: JSON.stringify({ title, content })
            });
            if (!res) return;
            const json = await res.json();
            if (json.status) {
                if (successDiv) successDiv.style.display = 'block';
                setTimeout(() => { if (successDiv) successDiv.style.display = 'none'; }, 3000);
            } else {
                if (errorDiv) {
                    errorDiv.textContent = json.message || 'Failed to update Terms & Conditions';
                    errorDiv.style.display = 'block';
                }
            }
        } catch (err) {
            console.error('Failed to save terms data:', err);
            if (errorDiv) {
                errorDiv.textContent = 'Connection error. Please try again.';
                errorDiv.style.display = 'block';
            }
        }
    };
})();
