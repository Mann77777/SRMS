document.addEventListener('DOMContentLoaded', function () {
    const addOptionBtn = document.getElementById('addOptionBtn');
    const dynamicOptions = document.getElementById('dynamicOptions');
    const saveRequestFormBtn = document.getElementById('saveRequestFormBtn');
    const requestFormTableBody = document.getElementById('requestFormTableBody');
    const userTypeFilter = document.getElementById('userTypeFilter');

    // Field types for dynamic form creation
    const fieldTypes = ['Text', 'Number', 'Email', 'Date', 'Time', 'Dropdown', 'Checkbox'];

    // Add dynamic option with nested fields
    addOptionBtn.addEventListener('click', function () {
        const optionRow = document.createElement('div');
        optionRow.classList.add('option-group', 'mb-3', 'p-3', 'border');
        
        // Option Name Input
        const optionNameInput = document.createElement('input');
        optionNameInput.type = 'text';
        optionNameInput.classList.add('form-control', 'mb-2');
        optionNameInput.placeholder = 'Option Name';
        
        // Option Fields Container
        const optionFieldsContainer = document.createElement('div');
        optionFieldsContainer.classList.add('option-fields');
        
        // Add Field Button
        const addFieldBtn = document.createElement('button');
        addFieldBtn.type = 'button';
        addFieldBtn.textContent = 'Add Field';
        addFieldBtn.classList.add('btn', 'btn-secondary', 'mb-2');
        addFieldBtn.addEventListener('click', function() {
            const fieldRow = createFieldRow();
            optionFieldsContainer.appendChild(fieldRow);
        });
        
        // Remove Option Button
        const removeOptionBtn = document.createElement('button');
        removeOptionBtn.type = 'button';
        removeOptionBtn.textContent = 'Remove Option';
        removeOptionBtn.classList.add('btn', 'btn-danger', 'ml-2');
        removeOptionBtn.addEventListener('click', function() {
            optionRow.remove();
        });

        optionRow.appendChild(optionNameInput);
        optionRow.appendChild(optionFieldsContainer);
        optionRow.appendChild(addFieldBtn);
        optionRow.appendChild(removeOptionBtn);

        dynamicOptions.appendChild(optionRow);
    });

    // Create field row function
    function createFieldRow() {
        const fieldRow = document.createElement('div');
        fieldRow.classList.add('field-row', 'form-row', 'mb-2');
        
        // Field Name Input
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.classList.add('form-control', 'col-md-4', 'mr-2');
        nameInput.placeholder = 'Field Name';
        
        // Field Type Dropdown
        const typeSelect = document.createElement('select');
        typeSelect.classList.add('form-control', 'col-md-4', 'mr-2');
        fieldTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = type.toLowerCase();
            option.textContent = type;
            typeSelect.appendChild(option);
        });
        
        // Remove Field Button
        const removeFieldBtn = document.createElement('button');
        removeFieldBtn.type = 'button';
        removeFieldBtn.textContent = 'Remove';
        removeFieldBtn.classList.add('btn', 'btn-danger', 'col-md-2');
        removeFieldBtn.addEventListener('click', function() {
            fieldRow.remove();
        });
        
        fieldRow.appendChild(nameInput);
        fieldRow.appendChild(typeSelect);
        fieldRow.appendChild(removeFieldBtn);
        
        return fieldRow;
    }

    function addOptionRow() {
        const optionRow = document.createElement('div');
        optionRow.classList.add('option-row', 'mb-3', 'border', 'p-3');
        
        optionRow.innerHTML = `
            <div class="form-group">
                <label>Option Name</label>
                <input type="text" class="form-control" placeholder="Enter option name">
                <button type="button" class="btn btn-sm btn-success add-field-btn mt-2">Add Field</button>
                <button type="button" class="btn btn-sm btn-danger remove-option-btn ml-2 mt-2">Remove Option</button>
            </div>
            <div class="fields-container"></div>
        `;
    
        // Add event listener for adding fields
        const addFieldBtn = optionRow.querySelector('.add-field-btn');
        const fieldsContainer = optionRow.querySelector('.fields-container');
        
        addFieldBtn.addEventListener('click', () => {
            const fieldRow = addFieldToOption(optionRow);
            fieldsContainer.appendChild(fieldRow);
        });
    
        // Add event listener for removing option
        const removeOptionBtn = optionRow.querySelector('.remove-option-btn');
        removeOptionBtn.addEventListener('click', () => {
            dynamicOptions.removeChild(optionRow);
        });
    
        dynamicOptions.appendChild(optionRow);
        return optionRow;
    }
    
    function addFieldToOption(optionRow) {
        const fieldRow = document.createElement('div');
        fieldRow.classList.add('field-row', 'mb-2', 'd-flex', 'align-items-center');
        
        fieldRow.innerHTML = `
            <input type="text" class="form-control mr-2" placeholder="Field Name">
            <select class="form-control mr-2" style="max-width: 150px;">
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="email">Email</option>
                <option value="date">Date</option>
                <option value="time">Time</option>
                <option value="dropdown">Dropdown</option>
            </select>
            <button type="button" class="btn btn-sm btn-danger remove-field-btn">Remove</button>
        `;
    
        // Add event listener for removing field
        const removeFieldBtn = fieldRow.querySelector('.remove-field-btn');
        removeFieldBtn.addEventListener('click', () => {
            optionRow.querySelector('.fields-container').removeChild(fieldRow);
        });
    
        return fieldRow;
    }

    function editRequestForm(formId) {
        // Find the form in the existing forms
        fetch('/get-request-forms')
        .then(response => response.json())
        .then(forms => {
            const formToEdit = forms.find(form => form.id === formId);
            
            if (formToEdit) {
                // Populate modal with existing form data
                document.getElementById('formName').value = formToEdit.name;
                document.getElementById('formDescription').value = formToEdit.description;
                document.getElementById('userType').value = formToEdit.userType;
    
                // Clear existing dynamic options
                dynamicOptions.innerHTML = '';
    
                // Recreate options and fields
                formToEdit.options.forEach(option => {
                    const optionRow = addOptionRow();
                    
                    // Set option name
                    const optionNameInput = optionRow.querySelector('input[type="text"]');
                    if (optionNameInput) optionNameInput.value = option.optionName;
    
                    // Get fields container
                    const fieldsContainer = optionRow.querySelector('.fields-container');
    
                    // Add fields for this option
                    option.fields.forEach(field => {
                        const fieldRow = addFieldToOption(optionRow);
                        
                        const nameInput = fieldRow.querySelector('input[type="text"]');
                        const typeSelect = fieldRow.querySelector('select');
                        
                        if (nameInput) nameInput.value = field.name;
                        if (typeSelect) typeSelect.value = field.type;
    
                        // Append field to container
                        fieldsContainer.appendChild(fieldRow);
                    });
                });
    
                // Change modal title and button
                document.getElementById('addReqFormModalLabel').textContent = 'Edit Request Form';
                saveRequestFormBtn.textContent = 'Update Form';
    
                // Store the ID for update
                saveRequestFormBtn.dataset.editId = formId;
    
                // Show modal
                $('#addReqFormModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error fetching form for edit:', error);
            alert('Failed to retrieve form for editing');
        });
    }
    
function deleteRequestForm(formId) {
    if (confirm('Are you sure you want to delete this request form?')) {
        fetch('/delete-request-form', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: formId })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Delete Response:', data);
            renderRequestForms(); // Refresh the table
            alert('Request form deleted successfully');
        })
        .catch(error => {
            console.error('Delete Error:', error);
            alert('Failed to delete request form');
        });
    }
}
    

    // Save Request Form
    function renderRequestForms() {
        fetch('/get-request-forms')
        .then(response => response.json())
        .then(forms => {
            const requestFormTableBody = document.getElementById('requestFormTableBody');
            requestFormTableBody.innerHTML = ''; // Clear existing rows
    
            forms.forEach(form => {
                // Create a detailed options display
                const optionsDisplay = form.options && form.options.length > 0 
                    ? form.options.map(option => {
                        // Create a list of fields for each option
                        const fieldsList = option.fields && option.fields.length > 0
                            ? option.fields.map(field => 
                                `${field.name} (${field.type})`
                            ).join('<br>')
                            : 'No fields';
                        
                        // Return option name with its fields
                        return `<strong>${option.optionName}:</strong><br>${fieldsList}`;
                    }).join('<hr>')
                    : 'No options defined';
    
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${form.name}</td>
                    <td>${form.description || 'N/A'}</td>
                    <td>${form.userType === 'student' ? 'Student' : 'Faculty & Staff'}</td>
                    <td>${optionsDisplay}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-form" data-id="${form.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-form" data-id="${form.id}">Delete</button>
                    </td>
                `;
                requestFormTableBody.appendChild(row);
            });
    
            // Add event listeners for edit and delete buttons
            document.querySelectorAll('.edit-form').forEach(btn => {
                btn.addEventListener('click', function() {
                    const formId = parseInt(this.dataset.id);
                    editRequestForm(formId);
                });
            });
    
            document.querySelectorAll('.delete-form').forEach(btn => {
                btn.addEventListener('click', function() {
                    const formId = parseInt(this.dataset.id);
                    deleteRequestForm(formId);
                });
            });
        })
        .catch(error => {
            console.error('Error fetching request forms:', error);
        });
    }
    
    // Render Request Forms Table
    saveRequestFormBtn.addEventListener('click', function () {
        const formName = document.getElementById('formName').value;
        const formDescription = document.getElementById('formDescription').value;
        const userType = document.getElementById('userType').value;
    
        const options = Array.from(dynamicOptions.children).map((optionRow) => {
            const optionNameInput = optionRow.querySelector('input[type="text"]');
            const optionName = optionNameInput ? optionNameInput.value : 'Unnamed Option';
    
            const fields = Array.from(optionRow.querySelectorAll('.field-row')).map((fieldRow) => {
                const nameInput = fieldRow.querySelector('input[type="text"]');
                const typeSelect = fieldRow.querySelector('select');
                
                return {
                    name: nameInput ? nameInput.value : 'Unnamed Field',
                    type: typeSelect ? typeSelect.value : 'text'
                };
            });
    
            return { 
                optionName, 
                fields 
            };
        });
    
        const requestForm = {
            name: formName,
            description: formDescription,
            userType,
            options: options
        };
    
        // Check if we're updating an existing form
        if (this.dataset.editId) {
            requestForm.id = parseInt(this.dataset.editId);
            
            fetch('/update-request-form', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestForm)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Update Response:', data);
                renderRequestForms();
                
                // Reset form and modal
                document.getElementById('addRequestFormForm').reset();
                dynamicOptions.innerHTML = '';
                $('#addReqFormModal').modal('hide');
                
                // Remove edit mode
                delete saveRequestFormBtn.dataset.editId;
                document.getElementById('addReqFormModalLabel').textContent = 'Add Request Form';
                saveRequestFormBtn.textContent = 'Save Form';
            })
            .catch(error => {
                console.error('Update Error:', error);
                alert('Failed to update request form');
            });
        } else {
            // Existing save logic
            requestForm.id = Date.now();
            
            fetch('/save-request-form', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestForm)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Save Response:', data);
                renderRequestForms();
                
                document.getElementById('addRequestFormForm').reset();
                dynamicOptions.innerHTML = '';
                $('#addReqFormModal').modal('hide');
            })
            .catch(error => {
                console.error('Save Error:', error);
                alert('Failed to save request form');
            });
        }
    });

    // Event listeners
    userTypeFilter.addEventListener('change', renderRequestForms);

    // Initial render
    renderRequestForms();
});