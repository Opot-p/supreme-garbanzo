// API Helper Functions
const API = {
    async request(action, data = {}, method = 'POST') {
        const formData = new FormData();
        formData.append('action', action);
        
        for (const key in data) {
            if (typeof data[key] === 'object') {
                formData.append(key, JSON.stringify(data[key]));
            } else {
                formData.append(key, data[key]);
            }
        }

        try {
            const response = await fetch('../php/api.php', {
                method: method,
                body: formData
            });
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Ошибка соединения с сервером' };
        }
    },

    async get(action, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `../php/api.php?action=${action}${queryString ? '&' + queryString : ''}`;
        
        try {
            const response = await fetch(url);
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Ошибка соединения с сервером' };
        }
    }
};

// UI Helper Functions
const UI = {
    showAlert(message, type = 'error') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container');
        const main = document.querySelector('main');
        if (main && main.firstChild) {
            main.insertBefore(alertDiv, main.firstChild);
        } else if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }
        
        setTimeout(() => alertDiv.remove(), 5000);
    },

    showLoading(element) {
        element.disabled = true;
        element.originalText = element.textContent;
        element.textContent = 'Загрузка...';
    },

    hideLoading(element) {
        element.disabled = false;
        if (element.originalText) {
            element.textContent = element.originalText;
        }
    }
};

// Form Handlers
document.addEventListener('DOMContentLoaded', function() {
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('login', {
                username: formData.get('username'),
                password: formData.get('password')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('register', {
                username: formData.get('username'),
                email: formData.get('email'),
                password: formData.get('password')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Create Teacher Form
    const createTeacherForm = document.getElementById('createTeacherForm');
    if (createTeacherForm) {
        createTeacherForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('create_teacher', {
                user_id: formData.get('user_id'),
                full_name: formData.get('full_name'),
                specialization: formData.get('specialization')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Create Course Form
    const createCourseForm = document.getElementById('createCourseForm');
    if (createCourseForm) {
        createCourseForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('create_course', {
                title: formData.get('title'),
                description: formData.get('description')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Create Lesson Form
    const createLessonForm = document.getElementById('createLessonForm');
    if (createLessonForm) {
        createLessonForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('create_lesson', {
                course_id: formData.get('course_id'),
                title: formData.get('title'),
                content: formData.get('content'),
                type: formData.get('type'),
                attachment: formData.get('attachment')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Enroll Student Form
    const enrollStudentForm = document.getElementById('enrollStudentForm');
    if (enrollStudentForm) {
        enrollStudentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('enroll_student', {
                course_id: formData.get('course_id'),
                student_id: formData.get('student_id')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Create Assignment Form
    const createAssignmentForm = document.getElementById('createAssignmentForm');
    if (createAssignmentForm) {
        createAssignmentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('create_assignment', {
                lesson_id: formData.get('lesson_id'),
                type: formData.get('type'),
                title: formData.get('title'),
                description: formData.get('description'),
                due_date: formData.get('due_date')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Submit Assignment Form
    const submitAssignmentForm = document.getElementById('submitAssignmentForm');
    if (submitAssignmentForm) {
        submitAssignmentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('submit_assignment', {
                assignment_id: formData.get('assignment_id'),
                content: formData.get('content')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Grade Submission Form
    const gradeSubmissionForm = document.getElementById('gradeSubmissionForm');
    if (gradeSubmissionForm) {
        gradeSubmissionForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            UI.showLoading(submitBtn);
            
            const formData = new FormData(this);
            const result = await API.request('grade_submission', {
                submission_id: formData.get('submission_id'),
                grade: formData.get('grade'),
                feedback: formData.get('feedback')
            });
            
            UI.hideLoading(submitBtn);
            
            if (result.success) {
                UI.showAlert(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                UI.showAlert(result.message, 'error');
            }
        });
    }

    // Logout Button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const result = await API.request('logout');
            
            if (result.success) {
                window.location.href = 'login.html';
            }
        });
    }

    // Load User Data
    loadUserData();
});

async function loadUserData() {
    const result = await API.request('get_user_data');
    
    if (result.success && result.data) {
        const userDisplay = document.getElementById('userDisplay');
        if (userDisplay) {
            userDisplay.textContent = `Привет, ${result.data.user.username} (${result.data.user.role})`;
        }
    }
}

async function loadCourses() {
    const result = await API.request('get_courses');
    
    if (result.success && result.data.courses) {
        const coursesContainer = document.getElementById('coursesList');
        if (coursesContainer) {
            coursesContainer.innerHTML = '';
            
            if (result.data.courses.length === 0) {
                coursesContainer.innerHTML = '<p>Курсы пока не созданы</p>';
                return;
            }
            
            result.data.courses.forEach(course => {
                const courseCard = document.createElement('div');
                courseCard.className = 'card course-item';
                courseCard.innerHTML = `
                    <h3>${course.title}</h3>
                    <p>${course.description}</p>
                    <a href="course.html?id=${course.id}" class="btn">Подробнее</a>
                `;
                coursesContainer.appendChild(courseCard);
            });
        }
    }
}

async function loadCourseDetails(courseId) {
    const result = await API.get('get_course_details', { course_id: courseId });
    
    if (result.success && result.data) {
        const courseTitle = document.getElementById('courseTitle');
        const courseDescription = document.getElementById('courseDescription');
        const lessonsList = document.getElementById('lessonsList');
        
        if (courseTitle) courseTitle.textContent = result.data.course.title;
        if (courseDescription) courseDescription.textContent = result.data.course.description;
        
        if (lessonsList && result.data.lessons) {
            lessonsList.innerHTML = '';
            
            if (result.data.lessons.length === 0) {
                lessonsList.innerHTML = '<p>Уроки пока не созданы</p>';
                return;
            }
            
            result.data.lessons.forEach(lesson => {
                const lessonItem = document.createElement('div');
                lessonItem.className = 'lesson-item';
                lessonItem.innerHTML = `
                    <h4>${lesson.title}</h4>
                    <p>${lesson.content}</p>
                    <p><strong>Тип:</strong> ${lesson.type}</p>
                    ${lesson.attachment ? `<p><strong>Файл:</strong> ${lesson.attachment}</p>` : ''}
                `;
                lessonsList.appendChild(lessonItem);
            });
        }
    }
}

async function loadCRMData(courseId) {
    const result = await API.get('get_crm_data', { course_id: courseId });
    
    if (result.success && result.data.crm) {
        const crmContainer = document.getElementById('crmData');
        if (crmContainer) {
            crmContainer.innerHTML = '';
            
            if (result.data.crm.length === 0) {
                crmContainer.innerHTML = '<p>Студентов пока нет</p>';
                return;
            }
            
            const table = document.createElement('table');
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Студент</th>
                        <th>Email</th>
                        <th>Оценки за задания</th>
                        <th>Результаты тестов</th>
                        <th>Средний балл</th>
                    </tr>
                </thead>
                <tbody>
                    ${result.data.crm.map(student => {
                        const avgGrade = student.grades.length > 0 
                            ? Math.round(student.grades.reduce((a, b) => a + b, 0) / student.grades.length)
                            : '-';
                        const avgTest = student.test_scores.length > 0
                            ? Math.round(student.test_scores.reduce((a, b) => a + b, 0) / student.test_scores.length)
                            : '-';
                        
                        return `
                            <tr>
                                <td>${student.username}</td>
                                <td>${student.email}</td>
                                <td>${student.grades.join(', ') || '-'}</td>
                                <td>${student.test_scores.join(', ') || '-'}</td>
                                <td>${avgGrade} / ${avgTest}</td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            `;
            
            crmContainer.appendChild(table);
        }
    }
}

// Initialize page-specific functions
if (window.location.pathname.includes('index.html')) {
    loadCourses();
}

if (window.location.pathname.includes('course.html')) {
    const urlParams = new URLSearchParams(window.location.search);
    const courseId = urlParams.get('id');
    if (courseId) {
        loadCourseDetails(courseId);
        loadCRMData(courseId);
    }
}
