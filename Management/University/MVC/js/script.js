


function getQueryParam(param) {
    if (!window.location.search) return null;
    var search = window.location.search.substring(1);
    var vars = search.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == param) {
            return decodeURIComponent(pair[1]);
        }
    }
    return null;
}


window.onload = function () {
    var error = getQueryParam('error');
    var success = getQueryParam('success');
    var msgDiv = document.getElementById('message-container');

    if (msgDiv) {
        if (error) {
            msgDiv.innerHTML = error;
            msgDiv.className = 'alert alert-error';
            msgDiv.style.display = 'block';
        } else if (success) {
            msgDiv.innerHTML = success;
            msgDiv.className = 'alert alert-success';
            msgDiv.style.display = 'block';
        }
    }


    if (typeof loadApplications === 'function') {
        loadApplications();
    }


    if (typeof loadPrograms === 'function') {
        loadPrograms();
    }


    var studentBtn = document.getElementById('tab-student');
    var uniBtn = document.getElementById('tab-university');

    if (studentBtn && uniBtn) {
        studentBtn.onclick = function () {
            document.getElementById('form-student').style.display = 'block';
            document.getElementById('form-university').style.display = 'none';
            studentBtn.className = 'tab-btn active';
            uniBtn.className = 'tab-btn';
        };

        uniBtn.onclick = function () {
            document.getElementById('form-student').style.display = 'none';
            document.getElementById('form-university').style.display = 'block';
            studentBtn.className = 'tab-btn';
            uniBtn.className = 'tab-btn active';
        };
    }
};

function loadApplications() {
    var tableBody = document.getElementById('applicationsBody');
    if (!tableBody) return;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../db/applications.json?v=' + new Date().getTime(), true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var applications = JSON.parse(xhr.responseText);
            var html = '';

            for (var i = 0; i < applications.length; i++) {
                var app = applications[i];
                html += '<tr>';
                html += '<td>' + app.id + '</td>';
                html += '<td>' + app.student_name + '</td>';
                html += '<td>' + app.program + '</td>';
                html += '<td>' + app.date + '</td>';
                html += '<td>' + (app.documents ? app.documents.length : 0) + ' Files</td>';
                html += '<td>' + app.status + '</td>';
                html += '<td><button class="btn btn-outline" onclick="alert(\'View Docs for ' + app.student_name + '\')">View</button></td>';
                html += '</tr>';
            }

            tableBody.innerHTML = html;
        }
    };
    xhr.send();
}


var registerForm = document.querySelector('form[action*="register.php"]');
if (registerForm) {
    registerForm.onsubmit = function (e) {
        var password = registerForm.querySelector('input[name="password"]').value;
        var confirmPassword = registerForm.querySelector('input[name="confirm_password"]').value;

        if (password.length < 6) {
            alert('Password must be at least 6 characters long!');
            return false; // Stop submission
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return false;
        }
        return true;
    };
}
