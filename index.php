<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">EMS Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="logout-btn">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container app-container">
        <!-- Registration Form Section -->
        <div class="card mb-4">
            <div class="card-header">
                <strong>Register New Employee</strong>
            </div>
            <div class="card-body">
                <div id="reg-alert" class="alert d-none"></div>
                <form id="register-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" id="reg-name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="reg-email" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mobile *</label>
                            <input type="text" class="form-control" id="reg-mobile" pattern="[0-9]{10,15}" title="10 to 15 digits" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Department *</label>
                            <select class="form-select" id="reg-department" required>
                                <option value="">Select...</option>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Sales">Sales</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Finance">Finance</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Designation *</label>
                            <input type="text" class="form-control" id="reg-designation" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Salary *</label>
                            <input type="number" class="form-control" id="reg-salary" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Joining Date *</label>
                            <input type="date" class="form-control" id="reg-joining" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" id="reg-status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success" id="reg-btn">Register Employee</button>
                </form>
            </div>
        </div>

        <!-- Employee Listing Section -->
        <div class="table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Employee List</h4>
                <div class="input-group" style="width: 300px;">
                    <input type="text" id="search-input" class="form-control" placeholder="Search name or email...">
                    <button class="btn btn-outline-secondary" type="button" id="search-btn">Search</button>
                </div>
            </div>
            
            <div id="list-alert" class="alert alert-danger d-none"></div>

            <div class="text-center my-4" id="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <table class="table table-bordered table-hover" id="employee-table">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Designation</th>
                    </tr>
                </thead>
                <tbody id="employee-tbody">
                    <!-- Data rendered here -->
                </tbody>
            </table>
            
            <nav>
                <ul class="pagination justify-content-end" id="pagination">
                    <!-- Pagination rendered here -->
                </ul>
            </nav>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
