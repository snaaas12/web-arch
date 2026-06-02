import {
    generateVerifier,
    generateChallenge,
    generateState
} from './pkce.js'

// PUBLIC CLIENT ID
const CLIENT_ID = '019e6590-7e00-717f-9447-802c5c036085'

const REDIRECT_URI =
    window.location.origin + '/oauth/callback'

// =========================
// START LOGIN
// =========================

export async function startLogin() {
    const verifier = generateVerifier()
    const challenge = await generateChallenge(verifier)
    const state = generateState()

    sessionStorage.setItem('pkce_verifier', verifier)
    sessionStorage.setItem('oauth_state', state)

    const params = new URLSearchParams({
        client_id: CLIENT_ID,
        response_type: 'code',
        redirect_uri: REDIRECT_URI,
        code_challenge: challenge,
        code_challenge_method: 'S256',
        state: state,
        scope: '*',
    })

    window.location =
        '/oauth/authorize?' + params
}
// =========================
// CALLBACK
// =========================

export async function handleCallback() {

    const params =
        new URLSearchParams(window.location.search)

    const code = params.get('code')
    const state = params.get('state')

    if (!code) {
        return null
    }

    // CSRF protection

    const savedState =
        sessionStorage.getItem('oauth_state')

    if (state !== savedState) {
        throw new Error('Invalid OAuth state')
    }

    const verifier =
        sessionStorage.getItem('pkce_verifier')

    if (!verifier) {
        throw new Error('No PKCE verifier')
    }

    const body = new URLSearchParams({
    grant_type: 'authorization_code',
    client_id: CLIENT_ID,
    code: code,
    code_verifier: verifier,
    redirect_uri: REDIRECT_URI,
})

const res = await fetch('/oauth/token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    credentials: 'include',
    body: body
})
    const data = await res.json()

    console.log('TOKEN RESPONSE:', data)

    const postId =
        sessionStorage.getItem('return_post_id')

    if (postId) {
        window.location.href = `/posts/${postId}`
    }

    sessionStorage.removeItem('pkce_verifier')
    sessionStorage.removeItem('oauth_state')

    return data.access_token
}
// =========================
// REFRESH TOKEN
// =========================

export async function refreshToken() {

    const res = await fetch('/oauth/token', {

        method: 'POST',

        credentials: 'include',

        headers: {
            'Content-Type': 'application/json'
        },

        body: JSON.stringify({
            grant_type: 'refresh_token',
            client_id: CLIENT_ID,
        })
    })

    if (!res.ok) {

        console.error('Refresh failed')

        startLogin()

        return null
    }

    const data = await res.json()

    return data.access_token
}
