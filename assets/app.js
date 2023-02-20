import './styles/app.scss';
import {startAuthentication, startRegistration} from '@simplewebauthn/browser';

const alert = document.querySelector('.alert');

document.querySelector('.form-register')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = new FormData(e.target);

    try {
        const body = { username: data.get('username'), displayName: data.get('displayName') };
        const creationOptionsJSON = await jsonFetch('/register/options', 'POST',body);
        const registrationResponse = await startRegistration(creationOptionsJSON);
        const registerResponseJSON = await jsonFetch('/register', 'POST', registrationResponse);
        (registerResponseJSON.status === 'error') ?  handleCommonError() : window.location.href = '/login';
    } catch (e) {
        if (e.name === 'NotAllowedError') {
            handleNotAllowedError();
        }
    }
});

document.querySelector('.form-login')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = new FormData(e.target);

    try {
        const requestOptionsJSON = await jsonFetch('/login/options', 'POST', { username: data.get('username') });
        const authResponse = await startAuthentication(requestOptionsJSON);
        const loginResponseJSON = await jsonFetch('/login', 'POST', authResponse);
        (loginResponseJSON.status === 'error') ?  handleCommonError() : window.location.href = '/login';
    } catch(e) {
        if (e.name === 'NotAllowedError') {
            handleNotAllowedError();
        }
    }
});

function handleCommonError() {
    alert.innerText = loginResponseJSON.errorMessage;
    alert.classList.remove('d-none');
}

function handleNotAllowedError() {
    alert.classList.remove('d-none');
    alert.innerText = 'Authentification annulée';
}

/**
 *
 * @param {string} url
 * @param {'GET'|'POST'} method
 * @param body
 * @return {Promise<any>}
 */
async function jsonFetch(url, method, body) {
    const response = await fetch(url, {
        body: JSON.stringify(body),
        headers: {
            'Content-Type': 'application/json'
        },
        method: method
    });

    return await response.json();
}