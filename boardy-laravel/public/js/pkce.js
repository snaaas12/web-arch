// Генерация случайной строки
export function generateVerifier() {
    const arr = new Uint8Array(32);
    if (crypto.getRandomValues) {
        crypto.getRandomValues(arr);
    } else {
        for (let i = 0; i < arr.length; i++) {
            arr[i] = Math.floor(Math.random() * 256);
        }
    }
    return base64UrlEncode(arr);
}

// SHA-256 хеш (альтернативная реализация без crypto.subtle)
export async function generateChallenge(verifier) {
    // Используем простой хеш для теста (в продакшене так не делать!)
    // Для реального PKCE нужен SHA-256, но для учебных целей используем fallback
    try {
        const data = new TextEncoder().encode(verifier);
        const hash = await crypto.subtle.digest('SHA-256', data);
        return base64UrlEncode(new Uint8Array(hash));
    } catch (e) {
        console.warn('crypto.subtle недоступен, использую упрощённый challenge');
        // Упрощённый вариант (только для теста!)
        return base64UrlEncode(new TextEncoder().encode(verifier));
    }
}

// Генерация state
export function generateState() {
    return generateVerifier();
}

function base64UrlEncode(bytes) {
    let base64;
    if (typeof bytes === 'string') {
        base64 = btoa(bytes);
    } else {
        const str = String.fromCharCode(...bytes);
        base64 = btoa(str);
    }
    return base64
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}
