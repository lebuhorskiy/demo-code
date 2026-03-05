import { defineStore } from 'pinia'
import { useRouter, useNuxtApp } from '#imports'
import type { User } from '~/types/user/user.type'
import type { RegisterType } from '~/types/auth/register.type'
import type { RegisterVerifyType } from '~/types/auth/register-verify.type'
import type { Config } from '~/types/config/config.type'
import { useModalsStore } from './modals'
import { useAuth } from '~/composables/useAuth'

export type AuthState = {
    user: User | null
    pending: boolean
    registerData: Config
}

const initial: AuthState = {
    user: null,
    pending: false,
    registerData: {
        email: null,
        password: null,
        promo: null,
        currency_id: null,
        user_id: null,
        verification_code: null,
        next_request_seconds: null,
    },
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({ ...initial }),
    getters: {
        isAuthenticated: (s) => !!s.user,
    },
    actions: {
        async bootstrap() {
            const svc = useAuth()
            this.pending = true
            try {
                this.user = await svc.fetchMe()
            } finally {
                this.pending = false
            }
        },

        async changeCurrency(currency_id: number) {
            const svc = useAuth()
            try {
                await svc.changeCurrency(currency_id);
            } catch (e) {} finally {
                this.user = await svc.fetchMe();
            }
        },

        async login(payload: import('~/types/auth/login.type').LoginType) {
            const svc = useAuth()
            const modals = useModalsStore()
            this.pending = true
            try {
                await svc.loginEmail(payload)
                this.user = await svc.fetchMe()
                modals.closeAll();
            } finally {
                this.pending = false
            }
        },

        async logout() {
            const router = useRouter()
            const svc = useAuth()
            await svc.logout()
            this.user = null
            await router.push('/')
        },

        async register(payload: RegisterType) {
            const svc = useAuth()
            const modals = useModalsStore()

            const refCookie = useCookie<string | null>('ref_user_id', {
                maxAge: 60 * 60 * 24,
                sameSite: 'lax',
                path: '/',
            })

            if (refCookie.value) {
                payload.referral_id = refCookie.value;
            }

            const { user_id, next_request_seconds } = await svc.registerEmail(payload)

            this.registerData = {
                ...payload,
                user_id,
                next_request_seconds
            }

            modals.open('verification')
            modals.close('auth')
        },

        async registerResend()
        {
            const svc = useAuth()

            const { next_request_seconds } = await svc.registerEmail(this.registerData)

            this.registerData.next_request_seconds = next_request_seconds;

            return next_request_seconds;
        },

        async registerConfirmation(payload: RegisterVerifyType) {
            const modals = useModalsStore()
            const svc = useAuth()
            this.pending = true
            try {
                await svc.verifyEmail(payload)
                this.user = await svc.fetchMe()
                await modals.closeAll()
            } finally {
                this.pending = false
            }
        },

        async finishWithSocial(provider: string, params: object) {
            const modals = useModalsStore()

            const svc = useAuth()
            this.pending = true
            try {
                await svc.completeSocial(provider, params)
                this.user = await svc.fetchMe()
                await modals.closeAll()
            } catch (e) {
                console.log(e);
            } finally {
                this.pending = false
            }
        },

        async authWithSocial(provider: string) {
            if (!import.meta.client) return
            const svc = useAuth()
            this.pending = true
            try {
                await svc.startSocial(provider)
            } catch (e) {
                console.log(e);
            } finally {
                this.pending = false
            }
        },
    },
})
