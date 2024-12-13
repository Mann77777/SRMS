<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/service-management.css') }}" rel="stylesheet">
    <title>Admin - Service Management</title>
</head>
<body>
    @include('layouts.admin-navbar')
    @include('layouts.admin-sidebar')
    
    <div class="service-content">
        <div class="service-header">
            <h1>Service Management</h1>
        </div>

        <div class="service-btn" id="button-container">
            <button type="button" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Service
            </button>
        </div>

        <div class="row service-list">
            <!-- Services will be loaded here dynamically -->
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Service</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addServiceForm">
                        <div class="form-group">
                            <label>Service Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                            <small class="form-text text-muted">Upload an image for the service (recommended size: 800x600px)</small>
                        </div>
                        <div class="form-group">
                            <label>Service Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewService()">Save Service</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Service</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editServiceForm">
                        <input type="hidden" id="editServiceId">
                        <div class="form-group">
                            <label>Service Image</label>
                            <input type="file" class="form-control" id="editServiceImage" accept="image/*">
                            <small class="form-text text-muted">Upload a new image or leave empty to keep the current one</small>
                        </div>
                        <div class="form-group">
                            <label>Service Name</label>
                            <input type="text" class="form-control" id="editServiceName" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" id="editServiceDescription" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveEditedService()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/service-management.js') }}"></script>
</body>
</html>