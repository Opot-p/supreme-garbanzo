// Глобальные переменные
let currentUser = null;

// Проверка авторизации при загрузке страницы
document.addEventListener('DOMContentLoaded', async () => {
    await checkAuth();
});

async function checkAuth() {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_auth'
        });
        
        const data = await response.json();
        
        if (data.authenticated) {
            currentUser = data.user;
            updateUserInterface();
        } else {
            // Если не авторизован и находится на защищенной странице
            const protectedPages = ['admin.html', 'teacher.html', 'student.html', 'profile.html'];
            const currentPage = window.location.pathname.split('/').pop();
            
            if (protectedPages.includes(currentPage)) {
                window.location.href = '/html/login.html';
            }
        }
    } catch (error) {
        console.error('Ошибка проверки авторизации:', error);
    }
}

function updateUserInterface() {
    if (!currentUser) return;
    
    // Обновляем информацию о пользователе в шапке
    const userElements = document.querySelectorAll('.user-name');
    userElements.forEach(el => {
        el.textContent = currentUser.name;
    });
    
    const roleElements = document.querySelectorAll('.user-role');
    roleElements.forEach(el => {
        const roleNames = {
            'admin': 'Администратор',
            'teacher': 'Преподаватель',
            'student': 'Студент'
        };
        el.textContent = roleNames[currentUser.role] || currentUser.role;
    });
    
    // Показываем элементы для авторизованных пользователей
    document.querySelectorAll('.auth-only').forEach(el => {
        el.style.display = 'block';
    });
    
    document.querySelectorAll('.guest-only').forEach(el => {
        el.style.display = 'none';
    });
}

// Функция входа
async function login(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const login = formData.get('login');
    const password = formData.get('password');
    
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=login&login=${encodeURIComponent(login)}&password=${encodeURIComponent(password)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.user;
            showAlert('Вход выполнен успешно!', 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
    }
}

// Функция регистрации
async function register(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const login = formData.get('login');
    const password = formData.get('password');
    const name = formData.get('name');
    const email = formData.get('email');
    const role = formData.get('role') || 'student';
    
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=register&login=${encodeURIComponent(login)}&password=${encodeURIComponent(password)}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&role=${role}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.user;
            showAlert('Регистрация успешна! Добро пожаловать!', 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
    }
}

// Функция выхода
async function logout() {
    try {
        await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout'
        });
        
        currentUser = null;
        showAlert('Вы вышли из системы', 'success');
        setTimeout(() => {
            window.location.href = '/html/index.html';
        }, 1000);
    } catch (error) {
        showAlert('Ошибка при выходе', 'error');
    }
}

// Создание курса
async function createCourse(title, description) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=create_course&title=${encodeURIComponent(title)}&description=${encodeURIComponent(description)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Курс создан успешно!', 'success');
            return data.course;
        } else {
            showAlert(data.error, 'error');
            return null;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return null;
    }
}

// Получение курсов
async function getCourses() {
    try {
        const response = await fetch('/php/api.php?action=get_courses');
        const data = await response.json();
        
        if (data.success) {
            return data.courses;
        } else {
            showAlert(data.error, 'error');
            return [];
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return [];
    }
}

// Создание урока
async function createLesson(courseId, title, type, content) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=create_lesson&courseId=${courseId}&title=${encodeURIComponent(title)}&type=${type}&content=${encodeURIComponent(content)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Урок создан успешно!', 'success');
            return data.lesson;
        } else {
            showAlert(data.error, 'error');
            return null;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return null;
    }
}

// Получение уроков курса
async function getLessons(courseId) {
    try {
        const response = await fetch(`/php/api.php?action=get_lessons&courseId=${courseId}`);
        const data = await response.json();
        
        if (data.success) {
            return data.lessons;
        } else {
            return [];
        }
    } catch (error) {
        return [];
    }
}

// Зачисление студента
async function enrollStudent(studentId, courseId) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=enroll_student&studentId=${studentId}&courseId=${courseId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Студент зачислен!', 'success');
            return true;
        } else {
            showAlert(data.error, 'error');
            return false;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return false;
    }
}

// Отправка практического задания
async function submitAssignment(lessonId, fileContent, fileName) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=submit_assignment&lessonId=${lessonId}&fileContent=${encodeURIComponent(fileContent)}&fileName=${encodeURIComponent(fileName)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Задание отправлено!', 'success');
            return data.submission;
        } else {
            showAlert(data.error, 'error');
            return null;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return null;
    }
}

// Получение работ студентов
async function getSubmissions(lessonId) {
    try {
        const response = await fetch(`/php/api.php?action=get_submissions&lessonId=${lessonId}`);
        const data = await response.json();
        
        if (data.success) {
            return data.submissions;
        } else {
            return [];
        }
    } catch (error) {
        return [];
    }
}

// Оценка работы
async function gradeSubmission(submissionId, grade, feedback) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=grade_submission&submissionId=${submissionId}&grade=${grade}&feedback=${encodeURIComponent(feedback)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Оценка выставлена!', 'success');
            return true;
        } else {
            showAlert(data.error, 'error');
            return false;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return false;
    }
}

// Создание теста
async function createTest(lessonId, questions) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=create_test&lessonId=${lessonId}&questions=${encodeURIComponent(JSON.stringify(questions))}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Тест создан!', 'success');
            return data.test;
        } else {
            showAlert(data.error, 'error');
            return null;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return null;
    }
}

// Получение теста
async function getTest(lessonId) {
    try {
        const response = await fetch(`/php/api.php?action=get_test&lessonId=${lessonId}`);
        const data = await response.json();
        
        if (data.success) {
            return data.test;
        } else {
            return null;
        }
    } catch (error) {
        return null;
    }
}

// Отправка ответов на тест
async function submitTest(testId, answers) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=submit_test&testId=${testId}&answers=${encodeURIComponent(JSON.stringify(answers))}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            return data.result;
        } else {
            showAlert(data.error, 'error');
            return null;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return null;
    }
}

// Получение всех пользователей
async function getAllUsers(role = '') {
    try {
        const url = role 
            ? `/php/api.php?action=get_all_users&role=${role}`
            : '/php/api.php?action=get_all_users';
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            return data.users;
        } else {
            return [];
        }
    } catch (error) {
        return [];
    }
}

// Создание преподавателя (только админ)
async function createTeacher(login, password, name, email) {
    try {
        const response = await fetch('/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=create_teacher&login=${encodeURIComponent(login)}&password=${encodeURIComponent(password)}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Преподаватель создан!', 'success');
            return data.user;
        } else {
            showAlert(data.error, 'error');
            return null;
        }
    } catch (error) {
        showAlert('Ошибка соединения с сервером', 'error');
        return null;
    }
}

// Утилита для показа уведомлений
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Модальные окна
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Закрытие модального окна по клику вне его
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Обработка форм
document.querySelectorAll('form[data-handler]').forEach(form => {
    form.addEventListener('submit', async (e) => {
        const handlerName = form.dataset.handler;
        if (typeof window[handlerName] === 'function') {
            await window[handlerName](e);
        }
    });
});
