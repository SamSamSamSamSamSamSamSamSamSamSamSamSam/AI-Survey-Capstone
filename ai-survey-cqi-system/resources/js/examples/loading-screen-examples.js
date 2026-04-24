/**
 * LOADING SCREEN EXAMPLES
 * 
 * Copy and paste these examples into your code as needed.
 * The loader object is globally available after resources/js/modules/loading-screen.js loads.
 */

// ============================================================
// EXAMPLE 1: Simple API Call
// ============================================================

async function loadUserProfile() {
    loader.show('Loading profile...');
    
    try {
        const response = await fetch('/api/user/profile');
        const data = await response.json();
        
        // Do something with data
        console.log(data);
        
        loader.hide(500);
    } catch (error) {
        console.error(error);
        loader.showTemporary('Error loading profile', 3000);
    }
}


// ============================================================
// EXAMPLE 2: Form Submission with Validation
// ============================================================

document.getElementById('surveyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    loader.show('Processing survey...');
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Submission failed');
        }
        
        loader.showTemporary('Survey submitted successfully!', 2000);
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = '/surveys/thank-you';
        }, 2000);
        
    } catch (error) {
        console.error(error);
        loader.showTemporary('Error submitting survey', 3000);
    }
});


// ============================================================
// EXAMPLE 3: File Upload with Progress
// ============================================================

document.getElementById('uploadBtn').addEventListener('click', async () => {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    
    if (!file) {
        loader.showTemporary('Please select a file', 2000);
        return;
    }
    
    loader.show(`Uploading ${file.name}...`);
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch('/upload', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Upload failed');
        }
        
        loader.showTemporary('File uploaded successfully!', 2000);
        fileInput.value = '';
        
    } catch (error) {
        console.error(error);
        loader.showTemporary('Upload failed. Please try again.', 3000);
    }
});


// ============================================================
// EXAMPLE 4: Bulk Operations
// ============================================================

async function deleteManyRecords(recordIds) {
    loader.show(`Deleting ${recordIds.length} records...`);
    
    try {
        const response = await fetch('/api/records/delete-bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: recordIds })
        });
        
        if (!response.ok) {
            throw new Error('Deletion failed');
        }
        
        const result = await response.json();
        loader.showTemporary(`${result.deleted} records deleted!`, 2000);
        
        // Refresh table or list
        location.reload();
        
    } catch (error) {
        console.error(error);
        loader.showTemporary('Failed to delete records', 3000);
    }
}


// ============================================================
// EXAMPLE 5: Sequential Operations
// ============================================================

async function processMultipleSteps() {
    try {
        // Step 1
        loader.show('Step 1: Validating data...');
        await sleep(2000);
        
        // Step 2
        loader.show('Step 2: Analyzing responses...');
        await sleep(2000);
        
        // Step 3
        loader.show('Step 3: Generating report...');
        await sleep(2000);
        
        // Complete
        loader.showTemporary('All steps complete!', 2000);
        
    } catch (error) {
        console.error(error);
        loader.showTemporary('Process failed', 3000);
    }
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}


// ============================================================
// EXAMPLE 6: Button Loading State
// ============================================================

document.getElementById('submitBtn').addEventListener('click', async (e) => {
    const btn = e.target;
    const originalText = btn.textContent;
    
    // Show loading state on button
    btn.classList.add('loading');
    btn.disabled = true;
    
    try {
        const response = await fetch('/api/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            btn.textContent = '✓ Submitted';
            setTimeout(() => {
                btn.textContent = originalText;
                btn.classList.remove('loading');
                btn.disabled = false;
            }, 2000);
        }
        
    } catch (error) {
        console.error(error);
        btn.textContent = '✗ Error';
        btn.classList.remove('loading');
        btn.disabled = false;
        
        setTimeout(() => {
            btn.textContent = originalText;
        }, 2000);
    }
});


// ============================================================
// EXAMPLE 7: Data Export/Download
// ============================================================

async function exportSurveyData(surveyId) {
    loader.show('Preparing export...');
    
    try {
        const response = await fetch(`/api/surveys/${surveyId}/export`);
        
        if (!response.ok) {
            throw new Error('Export failed');
        }
        
        // Get blob and create download link
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `survey_${surveyId}_export.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        
        loader.showTemporary('Export ready!', 2000);
        
    } catch (error) {
        console.error(error);
        loader.showTemporary('Export failed', 3000);
    }
}


// ============================================================
// EXAMPLE 8: Real-time Search with Debounce
// ============================================================

function setupSearchWithLoading() {
    const searchInput = document.getElementById('searchInput');
    let timeout;
    
    searchInput.addEventListener('input', (e) => {
        clearTimeout(timeout);
        
        const query = e.target.value.trim();
        if (!query) return;
        
        loader.showPageLoader();
        
        timeout = setTimeout(async () => {
            try {
                const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
                const results = await response.json();
                
                // Display results
                updateSearchResults(results);
                
                loader.hidePageLoader();
                
            } catch (error) {
                console.error(error);
                loader.hidePageLoader();
            }
        }, 500);
    });
}

function updateSearchResults(results) {
    // Update your UI with results
    console.log('Results:', results);
}


// ============================================================
// EXAMPLE 9: Confirm Action with Loading
// ============================================================

async function confirmAndProcess(action, data) {
    // Show confirmation (using Bootstrap Modal)
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    return new Promise((resolve) => {
        document.getElementById('confirmBtn').onclick = async () => {
            modal.hide();
            
            loader.show(`Processing ${action}...`);
            
            try {
                const response = await fetch('/api/action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ action, data })
                });
                
                if (response.ok) {
                    loader.showTemporary('Action completed!', 2000);
                    resolve(true);
                }
            } catch (error) {
                loader.showTemporary('Error processing action', 3000);
                resolve(false);
            }
        };
        
        modal.show();
    });
}


// ============================================================
// EXAMPLE 10: Long-Running Background Task
// ============================================================

async function startBackgroundAnalysis(surveyId) {
    loader.show('Starting analysis...');
    
    try {
        // Initiate background task
        const response = await fetch(`/api/surveys/${surveyId}/analyze`, {
            method: 'POST'
        });
        
        if (!response.ok) {
            throw new Error('Failed to start analysis');
        }
        
        const { taskId } = await response.json();
        
        loader.showTemporary('Analysis started! You\'ll be notified when complete.', 3000);
        
        // Optional: Poll for status
        pollTaskStatus(taskId);
        
    } catch (error) {
        console.error(error);
        loader.showTemporary('Failed to start analysis', 3000);
    }
}

async function pollTaskStatus(taskId) {
    const maxAttempts = 60;
    let attempts = 0;
    
    const interval = setInterval(async () => {
        attempts++;
        
        try {
            const response = await fetch(`/api/tasks/${taskId}/status`);
            const { status, progress } = await response.json();
            
            if (status === 'complete') {
                clearInterval(interval);
                loader.showTemporary('Analysis complete!', 2000);
                // Refresh or redirect
                location.reload();
            }
            
            if (attempts >= maxAttempts) {
                clearInterval(interval);
            }
        } catch (error) {
            console.error(error);
            clearInterval(interval);
        }
    }, 5000);
}


// ============================================================
// EXAMPLE 11: Disable Loading for Quick Actions
// ============================================================

// This form won't show loading screen
document.getElementById('quickForm').addEventListener('submit', (e) => {
    // The form has data-no-loading attribute, so loader won't show
    // But you can still manually control it
    console.log('Quick action submitted');
});

// HTML:
// <form id="quickForm" action="/quick-action" method="POST" data-no-loading>
//     <button type="submit">Quick Action</button>
// </form>


// ============================================================
// EXAMPLE 12: Chained Operations
// ============================================================

async function processComplexWorkflow() {
    try {
        // Step 1: Fetch data
        loader.show('Fetching data...');
        const data = await fetchData();
        
        // Step 2: Process data
        loader.show('Processing...');
        const processed = await processData(data);
        
        // Step 3: Save results
        loader.show('Saving...');
        await saveResults(processed);
        
        // Done
        loader.showTemporary('Workflow complete!', 2000);
        
    } catch (error) {
        console.error(error);
        loader.showTemporary('Workflow failed', 3000);
    }
}

async function fetchData() {
    const response = await fetch('/api/data');
    return response.json();
}

async function processData(data) {
    // Simulate processing
    return await new Promise(resolve => {
        setTimeout(() => resolve(data), 1000);
    });
}

async function saveResults(results) {
    const response = await fetch('/api/results', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(results)
    });
    return response.json();
}
