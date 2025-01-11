import { BASE_URL } from './config/config.js';

document.addEventListener('DOMContentLoaded', () => {
    // Проверяем авторизацию пользователя при загрузке страницы
    const user = localStorage.getItem('user');
    if (user) {
        try {
            const parsedUser = JSON.parse(user);
            if (parsedUser && parsedUser.login) {
                showProfileMenu(parsedUser);
            } else {
                console.error("Некорректные данные пользователя в localStorage:", parsedUser);
                showLoginButton();
            }
        } catch (error) {
            console.error("Ошибка при обработке данных из localStorage:", error);
            showLoginButton();
        }
    } else {
        showLoginButton();
    }
});

// Отображение кнопки "Войти"
function showLoginButton() {
    const authContainer = document.getElementById('auth-container');
    authContainer.innerHTML = `
        <button id="loginButton" class="btn btn-outline-light">
            <i class="fas fa-sign-in-alt"></i> Войти
        </button>
    `;
    document.getElementById('loginButton').addEventListener('click', showLoginModal);
}

// Отображение меню профиля
function showProfileMenu(user) {
    const authContainer = document.getElementById('auth-container');
    authContainer.innerHTML = `
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" id="profileMenuButton" data-bs-toggle="dropdown">
                <i class="fas fa-user"></i> ${user.login}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenuButton">
                <li><a class="dropdown-item" href="#" id="accountButton">Аккаунт</a></li>
                <li><a class="dropdown-item" id="logoutButton" href="#">Выход</a></li>
            </ul>
        </div>
    `;
    document.getElementById('logoutButton').addEventListener('click', logoutUser);
    document.getElementById('accountButton').addEventListener('click', showAccountModal);
}

// Функция открытия окна логина
function showLoginModal() {
    Swal.fire({
        title: 'Войти',
        html: `
            <input type="text" id="login" class="swal2-input" placeholder="Логин">
            <input type="password" id="password" class="swal2-input" placeholder="Пароль">
            <p class="mt-3">Нет аккаунта? <a id="registerLink" href="#" style="color: #6c7cdb;">Регистрация</a></p>
        `,
        confirmButtonText: 'Войти',
        showCancelButton: true,
        cancelButtonText: 'Отмена',
        focusConfirm: false,
        preConfirm: () => {
            const login = Swal.getPopup().querySelector('#login').value;
            const password = Swal.getPopup().querySelector('#password').value;
            if (!login || !password) {
                Swal.showValidationMessage(`Пожалуйста, заполните все поля`);
            }
            return { login, password };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            authenticateUser(result.value);
        }
    });

    document.getElementById('registerLink').addEventListener('click', showRegisterModal);
}

// Функция открытия окна регистрации
function showRegisterModal() {
    Swal.fire({
        title: 'Регистрация',
        html: `
            <input type="text" id="reg-login" class="swal2-input" placeholder="Логин">
            <input type="password" id="reg-password" class="swal2-input" placeholder="Пароль">
            <input type="password" id="reg-password-repeat" class="swal2-input" placeholder="Повторите пароль">
        `,
        confirmButtonText: 'Зарегистрироваться',
        showCancelButton: true,
        cancelButtonText: 'Отмена',
        focusConfirm: false,
        preConfirm: () => {
            const login = Swal.getPopup().querySelector('#reg-login').value;
            const password = Swal.getPopup().querySelector('#reg-password').value;
            const passwordRepeat = Swal.getPopup().querySelector('#reg-password-repeat').value;
            if (!login || !password || !passwordRepeat) {
                Swal.showValidationMessage(`Пожалуйста, заполните все поля`);
            }
            if (password !== passwordRepeat) {
                Swal.showValidationMessage(`Пароли не совпадают`);
            }
            return { login, password, password_repeat: passwordRepeat };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            registerUser(result.value);
        }
    });
}

// Функция открытия окна профиля
function showAccountModal() {
    const user = JSON.parse(localStorage.getItem('user'));
    if (!user) {
        Swal.fire('Ошибка', 'Пользователь не авторизован', 'error');
        return;
    }

    fetch(`${BASE_URL}auth`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'full_data', user_id: user.user_id })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                const userData = data.data;
                Swal.fire({
                    title: 'Мой профиль',
                    html: `
                    <input type="text" id="surname" class="swal2-input" placeholder="Фамилия" value="${userData.surname || ''}">
                    <input type="text" id="name" class="swal2-input" placeholder="Имя" value="${userData.name || ''}">
                    <input type="email" id="email" class="swal2-input" placeholder="Email" value="${userData.email || ''}">
                    <input type="text" id="address" class="swal2-input" placeholder="Адрес" value="${userData.address || ''}">
                    <input type="tel" id="phone" class="swal2-input" placeholder="Телефон" value="${userData.phone || ''}">
                `,
                    confirmButtonText: 'Сохранить',
                    showCancelButton: true,
                    cancelButtonText: 'Отмена',
                    preConfirm: () => {
                        const surname = Swal.getPopup().querySelector('#surname').value.trim();
                        const name = Swal.getPopup().querySelector('#name').value.trim();
                        const email = Swal.getPopup().querySelector('#email').value.trim();
                        const address = Swal.getPopup().querySelector('#address').value.trim();
                        const phone = Swal.getPopup().querySelector('#phone').value.trim();

                        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                            Swal.showValidationMessage('Некорректный email');
                            return false;
                        }

                        return { user_id: user.user_id, surname, name, email, address, phone };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        saveUserData(result.value);
                    }
                });
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось получить данные пользователя', 'error');
        });
}

// Функция сохранения данных пользователя
function saveUserData(userData) {
    fetch(`${BASE_URL}auth`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update', ...userData })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200 || data.status === 201) {
                Swal.fire('Успешно!', 'Данные обновлены', 'success');
                localStorage.setItem('user', JSON.stringify(data.data));
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось сохранить данные', 'error');
        });
}

// Функция авторизации пользователя
function authenticateUser({ login, password }) {
    fetch(`${BASE_URL}auth`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'login', login, password })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                Swal.fire('Успешно!', 'Вы вошли в систему', 'success');
                localStorage.setItem('user', JSON.stringify(data.data));
                showProfileMenu(data.data);
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось соединиться с сервером', 'error');
        });
}

// Функция регистрации пользователя
function registerUser({ login, password, password_repeat }) {
    fetch(`${BASE_URL}auth`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'register', login, password, password_repeat })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 201) {
                Swal.fire('Успешно!', 'Аккаунт создан. Теперь вы можете войти.', 'success');
            } else {
                Swal.fire('Ошибка', data.message, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Ошибка', 'Не удалось соединиться с сервером', 'error');
        });
}

// Функция выхода пользователя
function logoutUser() {
    localStorage.removeItem('user');
    Swal.fire('Вы вышли из системы', '', 'success');
    showLoginButton();
}