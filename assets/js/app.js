$(document).ready(function () {
    const API_URL = 'api/';
    let currentPage = 1;

    if (window.location.pathname.includes('index.php') || window.location.pathname.endsWith('/')) {
        const token = sessionStorage.getItem('auth_token');
        if (!token) {
            window.location.href = 'login.php';
        } else {
            loadEmployees();
        }
    }

    function escapeHTML(str) {
        return $('<div>').text(str).html();
    }

    $('#login-form').on('submit', function (e) {
        e.preventDefault();
        const email = $('#email').val();
        const password = $('#password').val();
        const $btn = $('#login-btn');
        const $alert = $('#login-alert');

        $btn.prop('disabled', true).text('Authenticating...');
        $alert.addClass('d-none');

        $.ajax({
            url: API_URL + 'login.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email, password }),
            success: function (res) {
                if (res.status && res.token) {
                    sessionStorage.setItem('auth_token', res.token);
                    window.location.href = 'index.php';
                }
            },
            error: function (xhr) {
                let msg = 'An unexpected error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $alert.removeClass('d-none').text(msg);
                $btn.prop('disabled', false).text('Login');
            }
        });
    });


    $('#logout-btn').on('click', function (e) {
        e.preventDefault();
        sessionStorage.removeItem('auth_token');
        window.location.href = 'login.php';
    });


    $('#register-form').on('submit', function (e) {
        e.preventDefault();
        const token = sessionStorage.getItem('auth_token');
        const $alert = $('#reg-alert');
        const $btn = $('#reg-btn');

        const data = {
            name: $('#reg-name').val(),
            email: $('#reg-email').val(),
            mobile: $('#reg-mobile').val(),
            department: $('#reg-department').val(),
            designation: $('#reg-designation').val(),
            salary: $('#reg-salary').val(),
            joining_date: $('#reg-joining').val(),
            status: $('#reg-status').val()
        };

        $btn.prop('disabled', true).text('Registering...');
        $alert.addClass('d-none').removeClass('alert-success alert-danger');

        $.ajax({
            url: API_URL + 'employees.php',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'Authorization': 'Bearer ' + token },
            data: JSON.stringify(data),
            success: function (res) {
                if (res.status) {
                    $alert.addClass('alert-success').removeClass('d-none').text(res.message);
                    $('#register-form')[0].reset();
                    currentPage = 1;
                    loadEmployees();
                }
            },
            error: function (xhr) {
                handleApiError(xhr, $alert);
            },
            complete: function () {
                $btn.prop('disabled', false).text('Register Employee');
            }
        });
    });


    $('#search-btn').on('click', function () {
        currentPage = 1;
        loadEmployees();
    });

    $('#search-input').on('keypress', function (e) {
        if (e.which == 13) {
            currentPage = 1;
            loadEmployees();
        }
    });


    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && page !== currentPage) {
            currentPage = page;
            loadEmployees();
        }
    });

    function loadEmployees() {
        const token = sessionStorage.getItem('auth_token');
        const search = $('#search-input').val();
        const $alert = $('#list-alert');
        const $tbody = $('#employee-tbody');
        const $spinner = $('#loading-spinner');
        const $pagination = $('#pagination');

        $alert.addClass('d-none');
        $tbody.empty();
        $pagination.empty();
        $spinner.show();

        $.ajax({
            url: API_URL + `employees.php?page=${currentPage}&limit=5&search=${encodeURIComponent(search)}`,
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function (res) {
                $spinner.hide();
                if (res.status && res.data) {
                    if (res.data.length === 0) {
                        $tbody.html('<tr><td colspan="5" class="text-center">No employees found.</td></tr>');
                    } else {
                        let html = '';
                        res.data.forEach(emp => {
                            html += `<tr>
                                <td>${escapeHTML(emp.id)}</td>
                                <td>${escapeHTML(emp.name)}</td>
                                <td>${escapeHTML(emp.email)}</td>
                                <td>${escapeHTML(emp.department)}</td>
                                <td>${escapeHTML(emp.designation)}</td>
                            </tr>`;
                        });
                        $tbody.html(html);
                        renderPagination(res.pagination.total_pages, res.pagination.page);
                    }
                }
            },
            error: function (xhr) {
                $spinner.hide();
                handleApiError(xhr, $alert, true);
            }
        });
    }

    function renderPagination(totalPages, current) {
        if (totalPages <= 1) return;
        let html = '';
        

        const prevClass = current === 1 ? 'disabled' : '';
        html += `<li class="page-item ${prevClass}"><a class="page-link" href="#" data-page="${current - 1}">Previous</a></li>`;
        

        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === current ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        

        const nextClass = current === totalPages ? 'disabled' : '';
        html += `<li class="page-item ${nextClass}"><a class="page-link" href="#" data-page="${current + 1}">Next</a></li>`;
        
        $('#pagination').html(html);
    }

    function handleApiError(xhr, $alertElement, isListing = false) {
        if (xhr.status === 401) {
            sessionStorage.removeItem('auth_token');
            alert('Your session has expired or is invalid. Please log in again.');
            window.location.href = 'login.php';
            return;
        }

        let msg = 'An unexpected error occurred.';
        if (xhr.responseJSON) {
            msg = xhr.responseJSON.message || msg;
            if (xhr.responseJSON.errors && Array.isArray(xhr.responseJSON.errors)) {
                msg += '<ul>';
                xhr.responseJSON.errors.forEach(err => {
                    msg += `<li>${escapeHTML(err)}</li>`;
                });
                msg += '</ul>';
            }
        }
        
        if (isListing && $alertElement.length) {
             $alertElement.removeClass('d-none').html(msg);
             $('#employee-tbody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load data.</td></tr>');
        } else if ($alertElement.length) {
            $alertElement.addClass('alert-danger').removeClass('d-none').html(msg);
        } else {
            alert(msg);
        }
    }
});
