// Student Dashboard JavaScript

// Set username on page load
window.onload = function () {
    // Check session
    checkSession();

    // Load applications
    loadApplications();

    // Setup navigation
    setupNavigation();

    // Setup file upload
    setupFileUpload('upload-area-new', 'file-input-new', 'file-list-new');
    setupFileUpload('upload-area-docs', 'file-input-docs', 'file-list-docs');

    // Setup form submission
    setupFormSubmission();

    // Load history
    loadApplicationHistory();
};

// Check user session
function checkSession() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/check_session.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                if (!data.authenticated || data.role !== 'student') {
                    window.location.href = 'html/login.html';
                } else {
                    document.getElementById('display-username').textContent = data.username;
                    sessionStorage.setItem('studentId', data.student_id);
                }
            }
        }
    };
    xhr.send();
}

// Load applications from server
function loadApplications() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/get_applications.php?v=' + new Date().getTime(), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var applications = JSON.parse(xhr.responseText);
            displayApplications(applications);
        }
    };
    xhr.send();
}

// Display applications in the list
function displayApplications(applications) {
    var listContainer = document.getElementById('applications-list');
    if (!listContainer) return;

    if (applications.length == 0) {
        listContainer.innerHTML = '<p>No applications yet. Click "New Application" to get started.</p>';
        return;
    }

    var html = '';
    for (var i = 0; i < applications.length; i++) {
        var app = applications[i];
        var statusClass = 'status-' + app.status;
        var statusText = app.status.charAt(0).toUpperCase() + app.status.slice(1).replace('-', ' ');

        html += '<div class="application-item">';
        html += '    <div class="application-info">';
        html += '        <h4>' + app.program + '</h4>';
        html += '        <p><strong>Type:</strong> ' + app.application_type + '</p>';
        html += '        <p><strong>Submitted:</strong> ' + app.submission_date + '</p>';
        html += '        <p><strong>Status:</strong> <span class="status-badge ' + statusClass + '">' + statusText + '</span></p>';
        html += '    </div>';
        html += '    <div class="application-actions">';
        html += '        <button class="btn btn-secondary" onclick="viewApplication(' + app.id + ')">View</button>';
        if (app.status == 'pending' || app.status == 'draft') {
            html += '        <button class="btn btn-primary" onclick="editApplication(' + app.id + ')">Edit</button>';
        }
        html += '    </div>';
        html += '</div>';
    }

    listContainer.innerHTML = html;
}

// Navigation between sections
function showSection(sectionId) {
    // Hide all sections
    var sections = document.querySelectorAll('.content-section');
    for (var i = 0; i < sections.length; i++) {
        sections[i].classList.remove('active');
    }

    // Remove active class from all nav links
    var links = document.querySelectorAll('.sidebar-link');
    for (var i = 0; i < links.length; i++) {
        links[i].classList.remove('active');
    }

    // Show selected section
    document.getElementById(sectionId).classList.add('active');

    // Add active class to clicked nav link
    var navLink = document.querySelector('[data-section="' + sectionId + '"]');
    if (navLink) {
        navLink.classList.add('active');
    }

    // Update page title
    var titles = {
        'my-applications': 'My Applications',
        'new-application': 'New Application',
        'track-status': 'Track Status',
        'documents': 'Documents',
        'history': 'Application History'
    };
    document.getElementById('page-title').textContent = titles[sectionId];
}

// Setup navigation listeners
function setupNavigation() {
    var links = document.querySelectorAll('.sidebar-link[data-section]');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function (e) {
            e.preventDefault();
            var section = this.getAttribute('data-section');
            showSection(section);
        });
    }
}

// Form submission
function setupFormSubmission() {
    var form = document.getElementById('new-application-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            alert('Please fill in all required fields');
            return;
        }

        // Submit via AJAX or regular form submission
        alert('Application submitted successfully!\n\nIn a real application, this would be sent to submit_application.php');

        this.reset();
        document.getElementById('file-list-new').innerHTML = '';
        showSection('my-applications');
        loadApplications();
    });
}

// Save as draft functionality
function saveDraft() {
    var form = document.getElementById('new-application-form');
    alert('Application saved as draft!\n\nIn a real application, this would be saved to the database.');
}

// File upload handling
function setupFileUpload(uploadAreaId, fileInputId, fileListId) {
    var uploadArea = document.getElementById(uploadAreaId);
    var fileInput = document.getElementById(fileInputId);

    if (!uploadArea || !fileInput) return;

    uploadArea.addEventListener('click', function () {
        fileInput.click();
    });

    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function () {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files, fileListId);
    });

    fileInput.addEventListener('change', function (e) {
        handleFiles(e.target.files, fileListId);
    });
}

function handleFiles(files, fileListId) {
    var fileList = document.getElementById(fileListId);
    if (!fileList) return;

    for (var i = 0; i < files.length; i++) {
        var file = files[i];

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File ' + file.name + ' is too large. Max size is 5MB.');
            continue;
        }

        // Validate file type
        var allowedTypes = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 'image/jpg', 'image/png'];
        if (allowedTypes.indexOf(file.type) === -1) {
            alert('File ' + file.name + ' has an invalid type. Please upload PDF, DOC, DOCX, JPG, or PNG files.');
            continue;
        }

        // Add file to list
        var fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = '<div><strong>📄 ' + file.name + '</strong><p style="font-size: 12px; margin-top: 4px;">' +
            (file.size / 1024).toFixed(2) + ' KB</p></div>' +
            '<button class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" ' +
            'onclick="this.parentElement.remove()">Remove</button>';
        fileList.appendChild(fileItem);
    }
}

// View application
function viewApplication(id) {
    alert('Viewing application #' + id + '\n\nIn a real application, this would open a detailed view.');
}

// Edit application
function editApplication(id) {
    alert('Editing application #' + id + '\n\nLoading application data into form...');
    showSection('new-application');
}

// Refresh status
function refreshStatus() {
    alert('Refreshing status...\n\nIn a real application, this would fetch the latest updates from the server.');
    loadApplications();
}

// Load application history
function loadApplicationHistory() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'php/get_application_history.php?v=' + new Date().getTime(), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var history = JSON.parse(xhr.responseText);
            displayHistory(history);
        }
    };
    xhr.send();
}

// Display history timeline
function displayHistory(history) {
    var timeline = document.querySelector('#history .timeline');
    if (!timeline) return;

    var html = '';
    for (var i = 0; i < history.length; i++) {
        var item = history[i];
        html += '<div class="timeline-item">';
        html += '    <div class="timeline-date">' + item.date + '</div>';
        html += '    <div class="timeline-content">';
        html += '        <h4 style="margin-bottom: 8px;">' + item.action + '</h4>';
        html += '        <p><strong>Program:</strong> ' + item.program + '</p>';
        html += '        <p style="margin-top: 8px;">' + item.details + '</p>';
        html += '    </div>';
        html += '</div>';
    }

    timeline.innerHTML = html;
}
