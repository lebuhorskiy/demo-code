import { saveTokens, clearTokens, getTokens, updateTokens, isAccessExpired } from '~/utils/tokenStorage'
import type { LoginType } from '~/types/auth/login.type'
import type { LoginResponseType } from '~/types/auth/login.response.type'
import type { SocialLoginResponseType } from '~/types/auth/social-login.response.type'
import type { RegisterType } from '~/types/auth/register.type'
import type { RegisterResponseType } from '~/types/auth/register.response.type'
import type { RegisterVerifyType } from '~/types/auth/register-verify.type'
import type { User } from '~/types/user/user.type'
import {useRuntimeConfig, useNuxtApp, useEmitter} from '#imports'

let authInFlight = false

export function useAuth() {
    const { $api } = useNuxtApp()
    const config = useRuntimeConfig()

    async function refreshTokensIfNeeded() {
        const tokens = getTokens()
        if (!tokens?.access_token) return null

        if (!isAccessExpired(tokens)) return tokens

        try {
            const refreshed = await $api<LoginResponseType>('auth/refresh', {
                method: 'POST',
                headers: { Authorization: `Bearer ${tokens.access_token}` },
            })

            updateTokens({
                access_token: refreshed.access_token,
                expires_in: Date.now() + refreshed.expires_in * 1000,
            })

            return getTokens()
        } catch {
            clearTokens()
            return null
        }
    }

    async function fetchMe(): Promise<User | null> {
        const tokens = await refreshTokensIfNeeded()
        if (!tokens?.access_token) return null

        try {
            return await $api<User>('user/me')
        } catch {
            return null
        }
    }

    async function registerEmail(payload: RegisterType): Promise<{ user_id: number }> {
        return $api<RegisterResponseType>('auth/register/email', {
            method: 'POST',
            body: payload,
        })
    }

    async function verifyEmail(payload: RegisterVerifyType): Promise<void> {
        const res = await $api<LoginResponseType>('auth/register/verify-email', {
            method: 'POST',
            body: payload,
        })

        console.log('payload', payload)

        saveTokens({
            access_token: res.access_token,
            expires_in: res.expires_in ? Date.now() + res.expires_in * 1000 : undefined,
        })
    }

    async function loginEmail(payload: LoginType): Promise<void> {
        const res = await $api<LoginResponseType>('auth/login/email', {
            method: 'POST',
            body: payload,
        })

        saveTokens({
            access_token: res.access_token,
            expires_in: res.expires_in ? Date.now() + res.expires_in * 1000 : undefined,
        })
    }

    async function logout(): Promise<void> {
        clearTokens()
    }

    async function startSocial(provider: string): Promise<Window> {
        if (authInFlight) throw new Error('Auth is in flight')
        authInFlight = true
        try {
            const response = await $api<SocialLoginResponseType>(`auth/social/${provider}/start`)
            if (!import.meta.client) throw new Error('Must be called on client')

            window.location.href = response.redirect_url;
        } catch (e) {
            console.log('e', e);
            authInFlight = false
            throw e
        }
    }

    async function completeSocial(provider: string, params: object): Promise<void> {
        if (!import.meta.client) throw new Error('Must be called on client')

        try {
            const currency_id = localStorage.getItem('currency')
            if (currency_id) {
                params.currency_id = currency_id
            }

            const refCookie = useCookie<string | null>('ref_user_id', {
                maxAge: 60 * 60 * 24,
                sameSite: 'lax',
                path: '/',
            })

            if (refCookie.value) {
                params.referral_id = refCookie.value;
            }

            const fetchToken = await $api<LoginResponseType>(`auth/social/${provider}/callback`, {
                method: 'POST',
                body: params,
            })

            saveTokens({
                access_token: fetchToken.access_token,
                expires_in: fetchToken.expires_in ? Date.now() + fetchToken.expires_in * 1000 : undefined,
            })
        } catch (e) {
            console.log(e);
        } finally {
            authInFlight = false
        }
    }

    async function changeCurrency(currency_id: number) {
        try {
            await $api<any>('wallet/set-currency', {
                method: 'POST',
                body: {
                    currency_id
                },
            })

            return true;
        } catch {
            return false
        }
    }

    return {
        // queries
        fetchMe,
        // commands
        registerEmail,
        verifyEmail,
        loginEmail,
        logout,
        startSocial,
        completeSocial,

        // wallet
        changeCurrency
    }
}
